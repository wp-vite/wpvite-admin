<?php

namespace App\Services\Template;

use App\Models\Template;
use App\Services\Ssh\SshService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class TemplatePublisher
{
    /**
     * Summary of publish
     * @param \App\Models\Template $template
     * @return array{message: string, status: bool}
     */
    public function publish(Template $template): array
    {
        try {
            $siteOwnerUser = $template->site_owner_username;
            $localBackupPath = "/home/{$siteOwnerUser}/wpvite-backups";
            $dbDump = "{$localBackupPath}/db.sql.gz";
            $uploadsZip = "{$localBackupPath}/uploads.zip";

            $s3BackupPath = TemplateService::getS3BackupPath($template, 'v1.0');

            $authData = $template->auth_data;
            if(empty($authData['db_name'] ?? '') || empty($authData['db_username'] ?? '') || empty($authData['db_password'] ?? '')) {
                return ['status' => false, 'message' => 'DB Credential for the template is missing.'];
            }

            $dbName = $authData['db_name'];
            $dbUsername = Crypt::decrypt($authData['db_username']);
            $dbPassword = Crypt::decrypt($authData['db_password']);

            // Connect SSH
            $sshService = SshService::create($template->server->public_ip)
                ->usePrivateKey()
                ->asUser($siteOwnerUser);

            $output = $sshService->execute([
                "mkdir -p {$localBackupPath}",
                "mysqldump -u {$dbUsername} -p'{$dbPassword}' {$dbName} | gzip > {$dbDump}",
                "cd /home/{$siteOwnerUser}/public_html/content && zip -r {$uploadsZip} uploads",
                "aws s3 cp {$dbDump} {$s3BackupPath}/db.sql.gz",
                "aws s3 cp {$dbDump} {$s3BackupPath}/uploads.zip",
            ]);

            // dd($output->getOutput());

            // 3. Update template model
            // $template->update([
            //     'is_published' => true,
            //     'published_version' => 'v1.0',
            //     'backup_path' => $s3BackupPath,
            //     'published_at' => now(),
            // ]);

            return ['status' => true, 'message' => 'Template published successfully'];
        } catch (\Exception $e) {
            Log::error("Template publish failed: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
