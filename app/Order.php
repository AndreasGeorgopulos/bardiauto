<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    public function reservations () {
        return $this->hasMany('App\Reservation', 'order_id', 'id');
    }
}
