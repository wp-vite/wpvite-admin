<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplatePreview extends Model
{
    protected $primaryKey = 'preview_id';

    protected $fillable = [
        'template_id',
        'title',
        'device',
        'image_filename',
        'description',
        'position',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'template_id');
    }
}
