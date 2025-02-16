<?php

namespace App\Jobs;

use App\Models\Template;
use App\Services\Cloudflare\CloudflareDnsManager;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateDnsRecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $template;

    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
     * Handle the job
     * @param CloudflareDnsManager $dnsManager
     * @return void
     * @throws Exception
     */
    public function handle(CloudflareDnsManager $dnsManager)
    {
        try {
            $response = $dnsManager->addARecord(
                $this->template->domain,
                $this->template->server->public_ip
            );

            if($response['status'] && isset($response['response_data']['result']['id'])) {
                $this->template->update([
                    'dns_provider' => 'cloudflare',
                    'dns_record_id' => $response['response_data']['result']['id'],
                ]);
            }
        } catch (Exception $e) {
            Log::channel('site_setup')->error("CloudflareDnsManager: Failed to add DNS records for {$this->template->domain}: " . $e->getMessage());
            throw $e;
        }
    }
}
