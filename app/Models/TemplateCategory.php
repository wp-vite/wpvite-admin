<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class TemplateCategory extends Model
{
    use CrudTrait;
    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category',
        'category_slug',
    ];
}
