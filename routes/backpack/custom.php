<?php

use App\Http\Controllers\Admin\TemplatePublishController;
use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('user', 'UserCrudController');
    Route::crud('template', 'TemplateCrudController');
    Route::crud('country', 'CountryCrudController');
    Route::crud('hosting-server', 'HostingServerCrudController');
    Route::crud('template-category', 'TemplateCategoryCrudController');
    Route::crud('template-tag', 'TemplateTagCrudController');
    Route::crud('user-site', 'UserSiteCrudController');

    // Template
    Route::get('template/{template}/publish', [TemplatePublishController::class, 'getPublish'])->name('template.getPublish');
    Route::post('template/{template}/publish', [TemplatePublishController::class, 'publish'])->name('template.publish');

}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
