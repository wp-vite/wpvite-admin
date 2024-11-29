<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateCategory extends Model
{
    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category',
        'category_slug',
    ];
}
