<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    $users  = \App\Models\User::all();
    dd($users->toArray());
});
Route::get('/logo/{sl}', function ($sl) {
    return view('wpvite-logo.wpvite-logo', compact('sl'));
});
