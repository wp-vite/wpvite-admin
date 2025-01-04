<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use CrudTrait;
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = 'template_id';

    protected $fillable = [
        'template_uid',
        'title',
        'description',
        'category_id',
        'server_id',
        'status',
        'domain',
        'root_directory',
        'dns_provider', // cloudflare
        'dns_record_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            // Generate a unique alphanumeric ID with a prefix
            $template->template_uid = \App\Helpers\CustomHelper::generateHexId('T');
        });
    }

    public function server()
    {
        return $this->belongsTo(HostingServer::class, 'server_id', 'server_id');
    }
}
