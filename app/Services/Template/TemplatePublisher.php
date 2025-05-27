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
            // Validate template status
            if ($template->status !== 1) {
                return ['status' => false, 'message' => 'Template must be active before publishing.'];
            }

            $newVersion = $inputs['new_version'] ?? TemplateService::calculateNextTemplateVersion($template);
            
            // Validate version format
            if (!preg_match('/^\d+(\.\d{1,2})?$/', $newVersion)) {
                return ['status' => false, 'message' => 'Invalid version format. Must be X.Y or X.YY (e.g. 1.0 or 1.01)'];
            }

            // Check if version already exists
            $versionExists = $template->versions()->where('version', $newVersion)->exists();
            if ($versionExists && !($inputs['override_version'] ?? false)) {
                return ['status' => false, 'message' => "Version {$newVersion} already exists. Please check the 'Override existing version' checkbox to proceed."];
            }

            $s3BackupPath = TemplateService::getS3BackupPath($template, $newVersion);

            // Save previews first
            $previewResult = $this->savePreviews($template, $inputs, $newVersion);
            if (!$previewResult['status']) {
                return $previewResult;
            }

            $siteOwnerUser = $template->site_owner_username;
            $localBackupPath = "/home/{$siteOwnerUser}/wpvite-backups";
            $dbDump = "{$localBackupPath}/db.sql.gz";
            $uploadsZip = "{$localBackupPath}/uploads.zip";

            $authData = $template->auth_data;
            if (empty($authData['db_name'] ?? '') || empty($authData['db_username'] ?? '') || empty($authData['db_password'] ?? '')) {
                return ['status' => false, 'message' => 'Database credentials for the template are missing.'];
            }

            $dbName = $authData['db_name'];
            $dbUsername = Crypt::decrypt($authData['db_username']);
            $dbPassword = Crypt::decrypt($authData['db_password']);

            // Connect SSH
            $sshService = SshService::create($template->server->public_ip)
                ->usePrivateKey()
                ->asUser($siteOwnerUser);

            // Create backup directory if it doesn't exist
            $sshService->execute([
                "mkdir -p {$localBackupPath}"
            ]);

            // Create backups
            $backupResult = $sshService->execute([
                "mysqldump -u {$dbUsername} -p'{$dbPassword}' {$dbName} | gzip > {$dbDump}",
                "cd /home/{$siteOwnerUser}/public_html/content && zip -r {$uploadsZip} uploads"
            ]);

            if (!$backupResult->isSuccessful()) {
                return ['status' => false, 'message' => 'Failed to create backups: ' . $backupResult->getErrorOutput()];
            }

            // Upload to S3
            $uploadResult = $sshService->execute([
                "aws s3 cp {$dbDump} {$s3BackupPath}/db.sql.gz",
                "aws s3 cp {$uploadsZip} {$s3BackupPath}/uploads.zip"
            ]);

            if (!$uploadResult->isSuccessful()) {
                return ['status' => false, 'message' => 'Failed to upload backups to S3: ' . $uploadResult->getErrorOutput()];
            }

            // Clean up local backups
            $sshService->execute([
                "rm -f {$dbDump} {$uploadsZip}"
            ]);

            // Update template model
            $template->update([
                'published_at' => now(),
                'current_version' => $newVersion,
            ]);

            // If version exists and override is checked, update the existing version record
            if ($versionExists) {
                $template->versions()->where('version', $newVersion)->update([
                    'updated_at' => now()
                ]);
            } else {
                // Create new version record
                TemplateVersion::create([
                    'template_id' => $template->template_id,
                    'version' => $newVersion,
                ]);
            }

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
    private function savePreviews(Template $template, array $inputs, string $version): array
    {
        try {
            // Remove old previews
            $template->previews()->delete();

            $i = 1;
            foreach ($inputs['previews'] as $preview) {
                $file = $preview['screenshot'];

                // Validate file
                if (!$file->isValid()) {
                    return ['status' => false, 'message' => "Invalid file uploaded for preview {$i}."];
                }

                // Generate path like: templates/{template_id}/previews/filename.png
                $fileName = $i . '-' . Str::slug($preview['title']) . '.' . $file->getClientOriginalExtension();
                $filePath = "templates/{$template->template_id}/{$version}/previews/{$fileName}";

                // Store to S3
                if (!$file->storeAs('', $filePath, 's3_admin_public')) {
                    return ['status' => false, 'message' => "Failed to upload preview {$i} to S3."];
                }

                // Save preview record
                $template->previews()->create([
                    'title' => $preview['title'],
                    'image_filename' => $fileName,
                ]);

                $i++;
            }

            return ['status' => true];
        } catch (\Exception $e) {
            Log::error("Failed to save previews: " . $e->getMessage());
            return ['status' => false, 'message' => 'Failed to save previews: ' . $e->getMessage()];
        }
    }
}
