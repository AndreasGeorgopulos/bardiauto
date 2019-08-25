<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    public function seat () {
        return $this->hasOne('App\Seat', 'id', 'seat_id');
    }

    public function order () {
        return $this->hasOne('App\Order', 'id', 'order_id');
    }

    public function transaction () {
        return $this->hasOne('App\Transaction', 'id', 'transaction_id');
    }
}
