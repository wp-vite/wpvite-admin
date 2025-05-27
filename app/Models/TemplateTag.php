<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TemplateTag extends Model
{
    use CrudTrait;

    protected $primaryKey = 'tag_id';

    protected $fillable = [
        'tag',
        'slug',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->tag);
        });
    }

    /**
     * Set the tag attribute and capitalize each word.
     *
     * @param string $value
     * @return void
     */
    public function setTagAttribute($value)
    {
        $this->attributes['tag'] = ucwords(strtolower($value));
    }

    public function templates()
    {
        return $this->belongsToMany(Template::class, 'template_template_tag', 'tag_id', 'template_id')
            ->using(TemplateTemplateTag::class);
    }
}
