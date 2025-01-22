<?php

namespace App\Console\Commands;

use App\Helpers\CustomHelper;
use App\Jobs\CreateDnsRecordJob;
use App\Jobs\CreateTemplateSiteJob;
use App\Models\Template;
use App\Repositories\TemplateRepository;
use App\Services\AWS\ParameterStore;
use App\Services\Cloudflare\CloudflareDnsManager;
use App\Services\Virtualmin\VirtualminSiteManager;
use Illuminate\Console\Command;
use Faker\Factory as Faker;
use App\Services\Ssh\SshService;

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

        $serverIp   = '13.234.132.6';
        $targetUser = 'templmarlen4'; // Target user to run the command as

        $output = SshService::create($serverIp)
            ->usePrivateKey()
            ->asUser($targetUser)
            ->execute([
                'cd /home/' . $targetUser . '/public_html',
                'ls',
                'php -v',
            ]);

        if ($output->isSuccessful()) {
            echo "Command succeeded: " . $output->getOutput();
        } else {
            echo "Command failed with error: " . $output->getErrorOutput();
        }

        dd("Done.");

        $command = sprintf(
            "sudo -u %s bash -c 'cd /home/%s/public_html && ls'",
            $targetUser,
            $targetUser
        );

        // Validate private key path
        if ($privateKeyPath === false) {
            die('Private key file not found. Please check the path.');
        }

        // Properly construct the SSH command
        $sshCommand = sprintf(
            'ssh -i "%s" %s -o StrictHostKeyChecking=no "%s"',
            $privateKeyPath,
            $connection,
            $command
        );

        $process = proc_open($sshCommand, [
            1 => ['pipe', 'w'], // STDOUT
            2 => ['pipe', 'w'], // STDERR
        ], $pipes);

        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]); // Get STDOUT
            $errorOutput = stream_get_contents($pipes[2]); // Get STDERR

            fclose($pipes[1]);
            fclose($pipes[2]);

            $returnCode = proc_close($process);

            if ($returnCode === 0) {
                dd("Command succeeded: $output");
            } else {
                dd("Command failed with error: $errorOutput");
            }
        }


        // $zoneId = resolve(CloudflareDnsManager::class)->getZoneId('template5.wpvite.com');dd($zoneId);

        $template   = Template::where('template_uid', 'T1943321419DDQ8J')->first();

        CreateDnsRecordJob::withChain([
            new CreateTemplateSiteJob($template),
            // new InstallWordPressJob($template),
        ])->dispatch($template);

        dd($template->domain);

        $virtualminManager  = resolve(VirtualminSiteManager::class);

        // Step 3: Call the Virtualmin API to create the domain
        try {
            $server   = $virtualminManager->server($template->server);
            // $create = $server->createDomain($domain);

            // if($create['status'] && $create['data']['status'] == 'success') {
            //     $domainDetails = $server->domainDetails($domain);
            //     dd($domainDetails);
            // }
            $domainDetails = $server->domainDetails($domain);
            if($domainDetails['status'] && isset($domainDetails['data']['data'][0]['values'])) {
                $domainData = $domainDetails['data']['data'][0]['values'];
                $this->info("HTML Directory: ". $domainData['html_directory'][0]);
            }
            dd($domainDetails);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
        dd($create);
    }
}
