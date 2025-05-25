<?php

namespace App\Services\Template;

use App\Helpers\CustomHelper;
use App\Models\Template;
use App\Models\UserSite;
use App\Services\Cloudflare\CloudflareDnsManager;
use App\Services\SiteSetup\SiteSetupService;
use App\Services\Ssh\SshService;
use App\Services\Virtualmin\VirtualminSiteManager;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class TemplateService
{
    /**
     * Summary of setup
     * @param \App\Models\Template $template
     * @return TemplateSiteSetup
     */
    public static function setup(Template $template): TemplateSiteSetup
    {
        return resolve(TemplateSiteSetup::class)->site($template);
    }

    /**
     * Summary of publish
     * @param \App\Models\Template $template
     * @return array{message: string, status: bool}
     */
    public static function publish(Template $template, array $inputs): array
    {
        return resolve(TemplatePublisher::class)->publish($template, $inputs);
    }

    /**
     * Get template backup path on S3
     * @param \App\Models\Template $template
     * @param string $backupVersion
     * @return string
     */
    public static function getS3BackupPath(Template $template, string $backupVersion): string
    {
        return "s3://". Config::get('filesystems.disks.s3_admin.bucket') ."/templates/{$template->template_id}/{$backupVersion}";
    }

    /**
     * Summary of calculateNextTemplateVersion
     * @param \App\Models\Template $template
     * @param bool $major
     * @return float
     */
    public static function calculateNextTemplateVersion(Template $template, bool $major = false): float
    {
        $latestVersion  = $template->versions()->orderBy('version', 'desc')->value('version');
        if($latestVersion) {
            return (float)($latestVersion + ($major ? 1 : 0.1));
        }
        return 1.0;
    }



}
