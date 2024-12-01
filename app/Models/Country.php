<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use CrudTrait;
    protected $primaryKey = 'country_id';

    protected $fillable = [
        'name',
        'iso_code',
        'isd_code',
        'currency_code',
        'currency_symbol',
    ];
}
