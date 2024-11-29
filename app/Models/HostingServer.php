<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostingServer extends Model
{
    use SoftDeletes
    ;
    protected $primaryKey = 'server_id';

    protected $fillable = [
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
    ];
}
