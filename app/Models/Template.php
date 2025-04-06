<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
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

        /**
         * status
         *  0 => Inactive
         *  1 => Active
         *  2 => Maintenance
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
        'current_version'
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

        static::creating(function ($template) {
            // Generate a unique alphanumeric ID with a prefix
            $template->template_uid = \App\Services\Common\UidService::generate('T');
        });
    }

    /**
     * status
     * @param int|null $status
     * @return string|string[]
     */
    public static function status(?int $status = null)
    {
        $statusList = [
            0 => 'Inactive',
            1 => 'Active',
            2 => 'Maintenance',
            10 => 'Setup Pending',
            11 => 'Setup In Progress',
            12 => 'Setup Error',
        ];

        if($status !== null) {
            if(isset($statusList[$status])) {
                return $statusList[$status];
            }

            return $status;
        }

        return $statusList;
    }

    /**
     * setupProgress
     * @param int|null $progress
     * @return string|string[]
     */
    public static function setupProgress(?int $progress = null)
    {
        $progressList = [
            1 => 'Setup Initialized',
            2 => 'DNS Setup Pending',
            3 => 'Virtual Site Setup Pending',
            4 => 'Wordpress Setup Pending',
            100 => 'Setup Completed',
        ];

        if($progress !== null) {
            if(isset($progressList[$progress])) {
                return $progressList[$progress];
            }

            return $progress;
        }

        return $progressList;
    }

    /**
     * Summary of server
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<HostingServer, Template>
     */
    public function server()
    {
        return $this->belongsTo(HostingServer::class, 'server_id', 'server_id');
    }

    /**
     * Summary of versions
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TemplateVersion, Template>
     */
    public function versions()
    {
        return $this->hasMany(TemplateVersion::class, 'template_id', 'template_id');
    }
}
