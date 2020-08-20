<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BestchangeRates extends Model
{
    //
    protected $fillable = ['curr1', 'curr2', 'rate', 'diff', 'rub_rate'];
}
