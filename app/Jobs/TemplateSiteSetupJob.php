<?php

namespace App\Jobs;

use App\Helpers\CustomHelper;
use App\Models\Template;
use App\Repositories\TemplateRepository;
use App\Services\SiteSetup\TemplateSiteSetupService;
use App\Services\Virtualmin\VirtualminSiteManager;
use Exception;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class TemplateSiteSetupJob implements ShouldQueue
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
     * @return array
     */
    public function handle(TemplateSiteSetupService $siteSetupService): array
    {
        $siteSetupService->site($this->template);

        return $siteSetupService->runSetup();
    }
}
