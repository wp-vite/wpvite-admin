<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class TemplateVersion extends Model
{
    use HasUlids;

    protected $primaryKey = 'version_id';

    protected $fillable = [
        'template_id',
        'version',
    ];
}
