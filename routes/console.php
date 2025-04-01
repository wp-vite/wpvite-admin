<?php

use App\Helpers\CustomHelper;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('hello', function () {
    $uid    = \App\Services\Common\UidService::generate('T1');
    $timestamp = \App\Services\Common\UidService::timestamp($uid);
    $prefix = \App\Services\Common\UidService::prefix($uid);

    dd([$uid, $timestamp->format('c'), $prefix]);
    $this->info(\App\Services\Common\UidService::generate('T'));
    // $this->info(bcrypt('Masta626@'));
})->purpose('Testing code');
