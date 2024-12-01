<?php

use App\Helpers\CustomHelper;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('hello', function () {
    // $this->info(CustomHelper::generateHexId('T'));
    $this->info(bcrypt('Masta626@'));
})->purpose('Testing code');
