<?php

namespace App\Services\Template;

use App\Models\Template;
use App\Models\TemplateVersion;
use App\Services\Ssh\SshService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TemplatePublisher
{
    protected array $data = [];

    /**
     * Summary of publish
     * @param \App\Models\Template $template
     * @return array{message: string, status: bool}
     */
    public function publish(Template $template, array $inputs): array
    {
        try {
            $newVersion   = $inputs['new_version'] ?? TemplateService::calculateNextTemplateVersion($template);
            $s3BackupPath = TemplateService::getS3BackupPath($template, $newVersion);

            // Save previews
            $this->savePreviews($template, $inputs, $newVersion);

            $siteOwnerUser = $template->site_owner_username;
            $localBackupPath = "/home/{$siteOwnerUser}/wpvite-backups";
            $dbDump = "{$localBackupPath}/db.sql.gz";
            $uploadsZip = "{$localBackupPath}/uploads.zip";

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
            $template->update([
                'published_at' => now(),
                'current_version'   => $newVersion,
            ]);
            TemplateVersion::firstOrCreate([
                'template_id'   => $template->template_id,
                'version'   => $newVersion,
            ]);

            return ['status' => true, 'message' => 'Template published successfully'];
        } catch (\Exception $e) {
            Log::error("Template publish failed: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Summary of savePreviews
     * @param \App\Models\Template $template
     * @param array $inputs
     * @param string $version
     * @return void
     */
    private function savePreviews(Template $template, array $inputs, string $version)
    {
        // Remove old previews (optional)
        $template->previews()->delete();

        $i  = 1;
        foreach ($inputs['previews'] as $preview) {
            $file = $preview['screenshot'];

            // Generate path like: templates/{template_id}/previews/filename.png
            $fileName = $i .'-'. Str::slug($preview['title']) .'.'. $file->getClientOriginalExtension();
            $filePath = "templates/{$template->template_id}/{$version}/previews/{$fileName}";

            // Store to S3 (without returning full path)
            $file->storeAs('', $filePath, 's3_admin');

            // Save just the filename (not full URL or path)
            $template->previews()->create([
                'title' => $preview['title'],
                'image_filename' => $fileName, // only filename
            ]);

            $i++;
        }
    }
}
