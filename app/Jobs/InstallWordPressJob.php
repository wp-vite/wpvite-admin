<?php

namespace App\Jobs;

use App\Models\Template;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\Ssh\SshService;

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
        $output = SshService::create($sshUser, $serverIp)
            ->usePrivateKey($privateKeyPath)
            ->asUser($targetUser)
            ->execute([
                'cd /home/' . $targetUser . '/public_html',
                'ls',
            ]);

        if ($output->isSuccessful()) {
            echo $output->getOutput(); // Logs or outputs the command result
        } else {
            echo $output->getErrorOutput(); // Logs or outputs the error
        }
    }
}
