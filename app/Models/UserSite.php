<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSite extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'site_id';

    protected $fillable = [
        'site_uid',
        'user_id',
        'template_id',
        'server_id',
        'domain',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($site) {
            // Generate a unique alphanumeric ID with a prefix
            $site->site_uid = \App\Helpers\CustomHelper::generateHexId('S');
        });
    }
}
