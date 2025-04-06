<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateVersion extends Model
{
    protected $primaryKey = 'version_id';

    protected $fillable = [
        'template_id',
        'version',
    ];
}
