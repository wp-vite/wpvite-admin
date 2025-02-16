<?php

namespace App\Jobs;

use App\Helpers\CustomHelper;
use App\Models\Template;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\Ssh\SshService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        try {

            $authData   = $this->template->auth_data;

            $authData['admin_user']     = CustomHelper::generateRandomUsername(12, 'site');
            $authData['admin_password'] = CustomHelper::generateRandomPassword();

            // Update template details
            $this->template->update([
                'auth_data' => $authData,
            ]);

            // Connect SSH
            $sshService = SshService::create($this->template->server->public_ip)
                ->usePrivateKey()
                ->asUser($this->template->site_owner_username);

            $output = $sshService->execute([
                'cd '. $this->template->root_directory,
                sprintf(
                    'setup-wordpress --template %s --root-dir %s --domain %s --title %s --admin-user %s --admin-pass %s --admin-email %s --db-name %s --db-user %s --db-pass %s',
                    "template-wp-01",
                    escapeshellarg($this->template->root_directory),
                    escapeshellarg($this->template->domain),
                    escapeshellarg($this->template->title .' - Template Site'),
                    escapeshellarg($authData['admin_user']),
                    escapeshellarg($authData['admin_password']),
                    escapeshellarg('support@wpvite.com'),
                    escapeshellarg($authData['db_name']),
                    escapeshellarg(Crypt::decrypt($authData['db_username'])),
                    escapeshellarg(Crypt::decrypt($authData['db_password']))
                ),
            ]);

            if ($output->isSuccessful()) {
                Log::channel('site_setup')->info("WordPress installation successful for template ID {$this->template->template_uid}: " . $output->getOutput());
            } else {
                Log::channel('site_setup')->error("Failed to install WordPress for template ID {$this->template->template_uid}: " . $output->getOutput());
                throw new Exception($output->getOutput());
            }
        } catch (Exception $e) {
            Log::channel('site_setup')->error("Failed to install WordPress for template ID {$this->template->template_uid}: " . $e->getMessage());
            throw $e;
        }
    }
}
