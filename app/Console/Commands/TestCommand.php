<?php

namespace App\Console\Commands;

use App\Helpers\CustomHelper;
use Illuminate\Console\Command;
use Faker\Factory as Faker;

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

        $username  = CustomHelper::generateUsername(15, 'Wpvite');
        dd($username);
    }
}
