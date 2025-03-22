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
use Illuminate\Support\Facades\Config;
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
        try {
            if($this->siteStatus !== 10) {
                return $this->errorResponse('Site not available for setup, status might have changed.');
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
                    'admin_email'   => Config::get('wpvite.admin_email'),
                    'template'   => 'template-wp-01',
                ];

                $response   = $this->installWordPress('template', $params);
                if(($response['status'] ?? false) !== true) {
                    $this->statusUpdate(12);    // Setup Error
                    return $response;
                }
            }

            // Setup completed
            $this->setupCompleted();
            return ['status' => true, 'message'=> 'Site setup completed.'];
        } catch (Exception $e) {
            $this->statusUpdate(12); // Setup Error
            return $this->errorResponse('Unexpected error: ' . $e->getMessage());
        }
    }

    /**
     * Summary of errorResponse
     * @param string $message
     * @return array{message: string, status: bool}
     */
    private function errorResponse(string $message): array
    {
        return ['status' => false, 'message' => $message];
    }
}
