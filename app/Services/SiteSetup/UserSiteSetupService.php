<?php

namespace App\Services\SiteSetup;

use App\Models\UserSite;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use App\Helpers\CustomHelper;

class UserSiteSetupService extends SiteSetupService
{
    public function runSetup(): array
    {
        try {
            $this->setupProgressUpdate(2); // DNS Setup
            $dnsResult = $this->createDnsRecord();
            if (!$dnsResult['status']) return $dnsResult;

            $this->setupProgressUpdate(3); // Virtual Site Setup
            $siteResult = $this->createTemplateSite();
            if (!$siteResult['status']) return $siteResult;

            $this->setupProgressUpdate(4); // Clone Template
            $restoreResult = $this->restoreTemplateBackup();
            if (!$restoreResult['status']) return $restoreResult;

            if (!CustomHelper::isSiteHttpsWorking($this->siteModel->domain)) {
                $this->generateSsl();
            }

            $this->setupCompleted();
            return ['status' => true, 'message' => 'User site setup completed'];
        } catch (\Exception $e) {
            $this->statusUpdate(12);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function restoreTemplateBackup(): array
    {
        try {
            $auth = $this->siteModel->auth_data;
            $backupPath = $this->siteModel->template->backup_path;
            $dbFile = "{$backupPath}/db.sql.gz";
            $uploadsZip = "{$backupPath}/uploads.zip";
            $user = $this->siteModel->site_owner_username;
            $domain = $this->siteModel->domain;

            $ssh = $this->sshService->connect($this->siteModel->server->public_ip, $user);
            $ssh->execute([
                "aws s3 cp s3://wpvite-templates/{$dbFile} /tmp/db.sql.gz",
                "gunzip -f /tmp/db.sql.gz",
                "mysql -u {$auth['db_user']} -p'{$auth['db_pass']}' {$auth['db_name']} < /tmp/db.sql",
                "aws s3 cp s3://wpvite-templates/{$uploadsZip} /tmp/uploads.zip",
                "unzip -o /tmp/uploads.zip -d {$this->siteModel->root_directory}/wp-content",
                "wp search-replace 'TEMPLATE_DOMAIN' '{$domain}' --allow-root"
            ]);

            return ['status' => true, 'message' => 'Template backup restored'];
        } catch (\Exception $e) {
            Log::error("Restore failed: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
