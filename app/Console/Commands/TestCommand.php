<?php

namespace App\Console\Commands;

use App\Helpers\CustomHelper;
use App\Jobs\CreateDnsRecordJob;
use App\Jobs\CreateTemplateSiteJob;
use App\Jobs\InstallWordPressJob;
use App\Models\Template;
use App\Repositories\TemplateRepository;
use App\Services\AWS\ParameterStore;
use App\Services\Cloudflare\CloudflareDnsManager;
use App\Services\Common\TextSimilarityService;
use App\Services\SiteSetup\TemplatePublisherService;
use App\Services\SiteSetup\TemplateSiteSetupService;
use App\Services\Virtualmin\VirtualminSiteManager;
use Illuminate\Console\Command;
use Faker\Factory as Faker;
use App\Services\Ssh\SshService;
use App\Services\Template\TemplateService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-command {arg1=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $arg1   = $this->argument('arg1');

        $template   = Template::where('template_id', '01JTZZQXBRF0C7YCV4C975NYKV')->first();

        $templateSetup  = TemplateService::setup($template);
        dd($templateSetup->run());

        $templatePublisher   = resolve(TemplatePublisherService::class);
        dd($templatePublisher->publish($template));

        $siteSetupService   = resolve(TemplateSiteSetupService::class);
        $siteSetupService->site($template);

        dd($siteSetupService->generateSsl());


        // CreateDnsRecordJob::withChain([
        //     new CreateTemplateSiteJob($template),
        //     new InstallWordPressJob($template),
        // ])->dispatch($template);
        // dd("Done");

        $authData   = $template->auth_data;

        $authData['admin_user']     = CustomHelper::generateRandomUsername(12, 'site');
        $authData['admin_password'] = CustomHelper::generateRandomPassword();
        // Update template details
        $template->update([
            'auth_data' => $authData,
        ]);


        $sshService = SshService::create($template->server->public_ip)
            ->usePrivateKey()
            ->asUser($template->site_owner_username);

        $this->info("Starting wordpress setup..");
        $output = $sshService->execute([
            'cd '. $template->root_directory,
            sprintf(
                'setup-wordpress --template %s --root-dir %s --domain %s --title %s --admin-user %s --admin-pass %s --admin-email %s --db-name %s --db-user %s --db-pass %s',
                "template-wp-01",
                escapeshellarg($template->root_directory),
                escapeshellarg($template->domain),
                escapeshellarg($template->title .' - Template Site'),
                escapeshellarg($authData['admin_user']),
                escapeshellarg($authData['admin_password']),
                escapeshellarg('support@wpvite.com'),
                escapeshellarg($authData['db_name']),
                escapeshellarg(Crypt::decrypt($authData['db_username'])),
                escapeshellarg(Crypt::decrypt($authData['db_password']))
            ),
        ]);

        if ($output->isSuccessful()) {
            echo "Command succeeded: " . $output->getOutput();
        } else {
            echo "Command failed with error: " . $output->getOutput();
        }

        dd("Done.");

        // $zoneId = resolve(CloudflareDnsManager::class)->getRecord('template101.wpvite.com');dd($zoneId);
    }
}
