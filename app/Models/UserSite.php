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
        'template_id',
        'server_id',

        /**
         * status
         *  0 => Inactive
         *  1 => Active
         *  2 => Maintenance
         *  3 => Suspended
         *  10 => Setup Pending
         *  11 => Setup In Progress
         *  12 => Setup Error
         */
        'status',

        /**
         * setup_progress
         *  1 => Setup Initialized
         *  2 => DNS Setup Pending
         *  3 => Virtual Site Setup Pending
         *  4 => Wordpress Setup Pending
         *  100 => Setup Completed
         */
        'setup_progress',

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
            $site->site_uid = \App\Services\Common\UidService::generate('S');
        });
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'template_id');
    }

    public function server()
    {
        return $this->belongsTo(HostingServer::class, 'server_id', 'server_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
