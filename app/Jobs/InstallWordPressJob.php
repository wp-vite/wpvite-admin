<?php

namespace App\Jobs;

use App\Models\Template;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class InstallWordPressJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $template;

    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
