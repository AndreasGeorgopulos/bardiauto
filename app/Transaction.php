<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    const MAX_REMAINING_SECONDS = 120;

    public function reservations () {
        return $this->hasMany('App\Reservation', 'transaction_id', 'id');
    }

    public function order () {
        return $this->hasOne('App\Order', 'transaction_id', 'id');
    }

    public static function generate () {
        $transaction = new self();
        $transaction->code = str_replace('/', '-', bcrypt(strtotime(date('Y-m-d H:i:s')) . microtime() . rand(1000, 1000000)));
        $transaction->save();

        return $transaction;
    }
}
