<?php

namespace App\Services\SiteSetup;

use App\Helpers\CustomHelper;
use App\Models\Template;
use App\Models\UserSite;
use App\Services\Cloudflare\CloudflareDnsManager;
use App\Services\SiteSetup\SiteSetupService;
use App\Services\Ssh\SshService;
use App\Services\Virtualmin\VirtualminSiteManager;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class TemplateSiteSetupService extends SiteSetupService
{
    /**
     * Summary of runSetup
     * @return array|array{message: string, status: bool}
     */
    public function runSetup()
    {
        if($this->siteStatus !== 10) {
            return [
                'status'    => false,
                'message'   => 'Site not available for setup, status might have changed.',
            ];
        }

        // Initialize setup
        if(empty($this->setupProgress) || $this->setupProgress == 1) {
            $this->setupProgressUpdate(2);  // DNS Setup Pending
        }

        // DNS setup
        if($this->setupProgress == 2) {
            $response   = $this->createDnsRecord();
            if(($response['status'] ?? false) !== true) {
                $this->statusUpdate(12);    // Setup Error
                return $response;
            }

            $this->setupProgressUpdate(3); // Virtual Site Setup Pending
        }

        // Virtual Site Setup
        if($this->setupProgress == 3) {
            $response   = $this->createTemplateSite();
            if(($response['status'] ?? false) !== true) {
                $this->statusUpdate(12);    // Setup Error
                return $response;
            }

            $this->setupProgressUpdate(4); // Wordpress Setup Pending
        }

        // Wordpress Setup
        if($this->setupProgress == 4) {
            $params = [
                'admin_email'   => '',
                'template'   => '',
            ];

            $response   = $this->installWordPress('template', $params);
            if(($response['status'] ?? false) !== true) {
                $this->statusUpdate(12);    // Setup Error
                return $response;
            }

            $this->setupCompleted();
        }
    }
}
