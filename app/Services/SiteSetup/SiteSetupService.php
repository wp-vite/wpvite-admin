<?php

namespace App\Services\SiteSetup;

use App\Helpers\CustomHelper;
use App\Models\Template;
use App\Models\UserSite;
use App\Services\Cloudflare\CloudflareDnsManager;
use App\Services\Ssh\SshService;
use App\Services\Virtualmin\VirtualminSiteManager;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

abstract class SiteSetupService
{
    protected $errors = [];
    protected $siteModel;
    protected $siteStatus;
    protected $setupProgress;
    protected $dnsManager;
    protected $virtualminSiteManager;

    public function __construct(
        CloudflareDnsManager $dnsManager,
        VirtualminSiteManager $virtualminSiteManager
    )
    {
        $this->dnsManager  = $dnsManager;
        $this->virtualminSiteManager  = $virtualminSiteManager;
    }

    /**
     * Summary of site
     * @param object $model
     * @throws \Exception
     * @return static
     */
    public function site(object $model)
    {
        if (!($model instanceof Template || $model instanceof UserSite)) {
            throw new Exception("Invalid site instance.");
        }
        $this->siteModel    = $model;
        $this->siteStatus   = $model->status;
        $this->setupProgress    = $model->setup_progress;

        return $this;
    }

    /**
     * Add an A record to Cloudflare DNS.
     *
     * @return array
     */
    public function createDnsRecord(): array
    {
        $publicIp   = $this->siteModel->server->public_ip;

        try {
            // Check if record exists
            $getRecord = $this->dnsManager->getRecord($this->siteModel->domain);
            if($getRecord['status'] && $getRecord['data']['content'] == $publicIp) {
                $this->siteModel->update([
                    'dns_provider' => 'cloudflare',
                    'dns_record_id' => $getRecord['data']['id'],
                ]);

                return $getRecord;
            }

            // Add record if not exists
            $response = $this->dnsManager->addARecord(
                $this->siteModel->domain,
                $this->siteModel->server->public_ip
            );

            if($response['status'] && isset($response['data']['id'])) {
                $this->siteModel->update([
                    'dns_provider' => 'cloudflare',
                    'dns_record_id' => $response['data']['id'],
                ]);
            }

            return $response;
        } catch (Exception $e) {
            Log::channel('site_setup')->error("CloudflareDnsManager: Failed to add DNS records for {$this->siteModel->domain}: " . $e->getMessage());
            return [
                'status'    => false,
                'message'   => $e->getMessage()
            ];
        }
    }

    /**
     * Add an A record to Cloudflare DNS.
     *
     * @return array
     */
    public function createTemplateSite(): array
    {
        try {
            $server = $this->virtualminSiteManager->server($this->siteModel->server);

            $siteOwnerUsername   = strtolower(CustomHelper::generateRandomUsername(12, 'templ'));

            $create = $server->createDomain($this->siteModel->domain, [
                // 'plan' => 'default',
                'user' => $siteOwnerUsername,
            ]);

            if($create['status'] || $server->isDomainExistsError($create['response_data']['error'] ?? '')) {
                $domainDetails = $server->domainDetails($this->siteModel->domain);

                $authData   = $this->siteModel->auth_data;
                if($domainDetails['status'] && isset($domainDetails['data'])) {
                    $domainData = $domainDetails['data'];

                    $siteOwnerUsername = $domainData['username'][0] ?? null;
                    // $root_directory = $domainData['html_directory'][0] ?? null;
                    $authData['db_username'] = Crypt::encrypt($domainData['username_for_mysql'][0] ?? null);
                    $authData['db_password'] = Crypt::encrypt($domainData['password_for_mysql'][0] ?? null);
                    $authData['db_name'] = $siteOwnerUsername;
                }

                $root_directory = "/home/{$siteOwnerUsername}/public_html";

                // Update template details
                $this->siteModel->update([
                    'root_directory' => $root_directory,
                    'site_owner_username' => $siteOwnerUsername,
                    'auth_data' => $authData,
                ]);

                return $domainDetails;
            }

            return $create;
        } catch (Exception $e) {
            Log::channel('site_setup')->error("Failed to create domain for template ID {$this->siteModel->template_id}: " . $e->getMessage());
            return [
                'status'    => false,
                'message'   => $e->getMessage()
            ];
        }
    }

    /**
     * Add an A record to Cloudflare DNS.
     *
     * @param string $siteType template|usersite
     * @param array $params
     * @return array
     */
    public function installWordPress(string $siteType, array $params): array
    {
        $result = ['status' => false];

        $siteTile   = $this->siteModel->title . (($siteType == 'template') ? ' - Template Site' : '');
        $adminEmail = $params['admin_email'];
        $template   = $params['template'];

        try {

            $authData   = $this->siteModel->auth_data;

            if(!($authData['admin_user'] ?? '')) {
                $authData['admin_user']     = CustomHelper::generateRandomUsername(12, 'site');
            }
            if(!($authData['admin_password'] ?? '')) {
                $authData['admin_password'] = CustomHelper::generateRandomPassword();
            }

            // Update template details
            $this->siteModel->update([
                'auth_data' => $authData,
            ]);

            // Connect SSH
            $sshService = SshService::create($this->siteModel->server->public_ip)
                ->usePrivateKey()
                ->asUser($this->siteModel->site_owner_username);

            $output = $sshService->execute([
                'cd '. $this->siteModel->root_directory,
                sprintf(
                    'setup-wordpress --template %s --root-dir %s --domain %s --title %s --admin-user %s --admin-pass %s --admin-email %s --db-name %s --db-user %s --db-pass %s',
                    $template,
                    escapeshellarg($this->siteModel->root_directory),
                    escapeshellarg($this->siteModel->domain),
                    escapeshellarg($this->siteModel->title .' - Template Site'),
                    escapeshellarg($authData['admin_user']),
                    escapeshellarg($authData['admin_password']),
                    escapeshellarg($adminEmail),
                    escapeshellarg($authData['db_name']),
                    escapeshellarg(Crypt::decrypt($authData['db_username'])),
                    escapeshellarg(Crypt::decrypt($authData['db_password']))
                ),
            ]);

            if ($output->isSuccessful()) {
                $message    = "WordPress installation successful for template ID {$this->siteModel->template_id}: " . $output->getOutput();
                Log::channel('site_setup')->info($message);

                return [
                    'status'    => true,
                    'message'   => $message,
                ];
            } else {
                $result['message']    = "Failed to install WordPress for template ID {$this->siteModel->template_id}: " . $output->getOutput();
                Log::channel('site_setup')->error($result['message']);
            }
        } catch (Exception $e) {
            $result['message']    = "Failed to install WordPress for template ID {$this->siteModel->template_id}: " . $e->getMessage();
            Log::channel('site_setup')->error($result['message']);
        }

        return $result;
    }

    /**
     * Summary of generateSsl
     * @return array|array{message: string, status: bool}
     */
    public function generateSsl()
    {
        try {
            $server = $this->virtualminSiteManager->server($this->siteModel->server);
            $response = $server->generateSsl($this->siteModel->domain);

           return $response;
        } catch (Exception $e) {
            Log::channel('site_setup')->error("Failed to generate SSL for the domain {$this->siteModel->domain} (template ID {$this->siteModel->template_id}): " . $e->getMessage());
            return [
                'status'    => false,
                'message'   => $e->getMessage()
            ];
        }
    }


    /**
     * setupCompleted is run after the setup is completed
     * @return void
     */
    public function setupCompleted()
    {
        $this->siteModel->update([
            'status'    => 1,
            'setup_progress'    => 100,
        ]);

        // Fire any Event here
    }

    /**
     * statusUpdate
     * @return bool
     */
    public function statusUpdate(int $status)
    {
        $this->siteStatus  = $status;
        return $this->siteModel->update([
            'status'    => $status,
        ]);
    }

    /**
     * setupProgressUpdate
     * @return bool
     */
    public function setupProgressUpdate(int $progress)
    {
        $this->setupProgress  = $progress;
        return $this->siteModel->update([
            'setup_progress'    => $progress,
        ]);
    }
}
