<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateTemplateTag extends Model
{
    protected $table = 'template_template_tag';
    protected $primaryKey = 'template_tag_id';
    public $timestamps = false;

    protected $fillable = [
        'template_id',
        'tag_id',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'template_id');
    }

    public function tag()
    {
        return $this->belongsTo(TemplateTag::class, 'tag_id', 'tag_id');
    }
}
