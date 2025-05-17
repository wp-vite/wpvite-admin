<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use CrudTrait, HasUlids, SoftDeletes;

    protected $primaryKey = 'template_id';

    protected $fillable = [
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
        'published_at',
        'current_version',
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
    public static function setupProgress(?int $progress = null, string $default = null)
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

    public function setAuthDataAttribute($value)
    {
        // If value is a JSON string, decode it
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['auth_data'] = $decoded ? json_encode($decoded) : json_encode([]);
        } else {
            // If it's already an array or object, encode it as-is
            $this->attributes['auth_data'] = json_encode($value);
        }
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
