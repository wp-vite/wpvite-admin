<?php

namespace App\Jobs;

use App\Helpers\CustomHelper;
use App\Models\Template;
use App\Repositories\TemplateRepository;
use App\Services\Virtualmin\VirtualminSiteManager;
use Exception;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateTemplateSiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $template;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Template $template
     */
    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
     * Handle the job
     * @param VirtualminSiteManager $virtualminManager
     * @return void
     * @throws Exception
     */
    public function handle(VirtualminSiteManager $virtualminManager): void
    {
        // Step 3: Call the Virtualmin API to create the domain
        try {
            $server = $virtualminManager->server($this->template->server);

            $siteOwnerUsername   = strtolower(CustomHelper::generateRandomUsername(12, 'templ'));

            $create = $server->createDomain($this->template->domain, [
                // 'plan' => 'default',
                'user' => $siteOwnerUsername,
            ]);

            if($create['status'] && $create['data']['status'] == 'success') {
                // $domainDetails = $server->domainDetails($this->template->domain);

                // if($domainDetails['status'] && isset($domainDetails['data']['data'][0]['values'])) {
                //     $domainData = $domainDetails['data']['data'][0]['values'];
                //     $root_directory = $domainData['html_directory'][0] ?? null;
                // }

                $root_directory = "/home/{$siteOwnerUsername}/public_html";

                // Update template details
                $this->template->update([
                    'root_directory' => $root_directory,
                ]);
            }
        } catch (Exception $e) {
            Log::error("Failed to create domain for template ID {$this->template->template_uid}: " . $e->getMessage());
            throw $e;
        }
    }
}
