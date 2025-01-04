<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TemplateCategory extends Model
{
    use CrudTrait;
    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category',
        'category_slug',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            // Generate a unique alphanumeric ID with a prefix
            $category->category_slug = Str::slug($category->category);
        });
    }
}
