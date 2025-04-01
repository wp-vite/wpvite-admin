<?php

namespace App\Services\SiteSetup;

use App\Models\Template;
use App\Services\Ssh\SshService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class TemplatePublisherService
{
    public function __construct()
    {
        //
    }

    public function publish(Template $template): array
    {
        try {
            $siteOwnerUser = $template->site_owner_username;
            $backupPath = "/home/{$siteOwnerUser}/wpvite-backups";
            $dbDump = "{$backupPath}/db.sql.gz";
            $uploadsZip = "{$backupPath}/uploads.zip";

            $s3Path = "s3://". Config::get('filesystems.disks.s3_admin.bucket') ."/templates/{$template->template_uid}/v1.0";

            $authData = $template->auth_data;
            if(empty($authData['db_name'] ?? '') || empty($authData['db_username'] ?? '') || empty($authData['db_password'] ?? '')) {
                return ['status' => false, 'message' => 'DB Credential for the template is missing.'];
            }

            $dbName = $authData['db_name'];
            $dbUsername = Crypt::decrypt($authData['db_username']);
            $dbPassword = Crypt::decrypt($authData['db_password']);

            // File::makeDirectory($backupPath, 777, true, true);

            // Connect SSH
            $sshService = SshService::create($template->server->public_ip)
                ->usePrivateKey()
                ->asUser($siteOwnerUser);

            // 1. SSH and dump database
            // $sshService->execute([
            //     "mkdir -p {$backupPath}",
            //     "sudo chmod -R 777 {$backupPath}",
            // ]);
            $output = $sshService->execute([
                "mkdir -p {$backupPath}",
                "mysqldump -u {$dbUsername} -p'{$dbPassword}' {$dbName} | gzip > {$dbDump}",
                "cd /home/{$siteOwnerUser}/public_html/content && zip -r {$uploadsZip} uploads",
                // "chown -R www-data {$backupPath} && chmod -R 777 {$backupPath}",
                "aws s3 cp {$dbDump} {$s3Path}/db.sql.gz",
                "aws s3 cp {$dbDump} {$s3Path}/uploads.zip",
            ]);
            // $sshService->asUser('ubuntu')->execute([
            //     "sudo chown -R www-data {$backupPath} && sudo chmod -R 777 {$backupPath}",
            // ]);

            dd($output->getOutput());

            // 3. Update template model
            // $template->update([
            //     'is_published' => true,
            //     'published_version' => 'v1.0',
            //     'backup_path' => $s3Path,
            //     'published_at' => now(),
            // ]);

            return ['status' => true, 'message' => 'Template published successfully'];
        } catch (\Exception $e) {
            Log::error("Template publish failed: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
