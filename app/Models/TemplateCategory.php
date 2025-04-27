<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TemplateCategory extends Model
{
    use CrudTrait, HasUlids;

    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category',
        'category_slug',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->category_slug = Str::slug($model->category);
        });
    }
}
