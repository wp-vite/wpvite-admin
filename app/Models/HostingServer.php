<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostingServer extends Model
{
    use CrudTrait;
    use SoftDeletes
    ;
    protected $primaryKey = 'server_id';

    protected $fillable = [
        'server_uid',
        'name',
        'provider',
        'instance_type',
        'public_ip',
        'private_ip',
        'instance_id',
        'virtualmin_url',
        'status',
        'max_sites',
        'cpu',
        'ram',
        'disk_size',

        /**
         * JSON
         * {auth_type: string, auth_source: string}
         */
        'authorization',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'authorization',
    ];

    protected static function booted()
    {
        static::creating(function ($hostingServer) {
            // Generate a unique alphanumeric ID with a prefix
            $hostingServer->server_uid = \App\Helpers\CustomHelper::generateHexId('H', false);
        });
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            1 => 'Active',
            0 => 'Inactive',
            2 => 'Maintenance',
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }
}
