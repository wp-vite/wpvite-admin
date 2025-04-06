<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateMeta extends Model
{
    protected $primaryKey = 'meta_id';

    protected $fillable = [
        'template_id',
        'meta_key',
        'meta_value',
    ];
}
