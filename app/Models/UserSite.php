<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
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
        'dns_provider', // cloudflare
        'dns_record_id',
        'root_directory',
        'site_owner_username',

        /**
         * JSON
         * {
         *     db_name: string,
         *     db_username: string,
         *     db_password: string,
         *     admin_user: string,
         *     admin_password: string,
         * }
         */
        'auth_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'auth_data' => AsArrayObject::class,
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($site) {
            // Generate a unique alphanumeric ID with a prefix
            $site->site_uid = \App\Helpers\CustomHelper::generateHexId('S');
        });
    }
}
