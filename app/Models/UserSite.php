<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSite extends Model
{
    use CrudTrait;
    use SoftDeletes;

    protected $primaryKey = 'site_id';

    protected $fillable = [
        'site_uid',
        'user_id',
        'status',
        'template_id',
        'server_id',
        'domain',
        'root_directory',
        'dns_provider', // cloudflare
        'dns_record_id',
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
