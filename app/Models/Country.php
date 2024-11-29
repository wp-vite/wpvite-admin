<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $primaryKey = 'country_id';

    protected $fillable = [
        'name',
        'iso_code',
        'isd_code',
        'currency_code',
        'currency_symbol',
    ];
}
