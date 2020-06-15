<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Reservation
 * @package App
 */
class Reservation extends Model
{
    use SoftDeletes;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seat ()
    {
        return $this->hasOne('App\Seat', 'id', 'seat_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order ()
    {
        return $this->hasOne('App\Order', 'id', 'order_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function transaction ()
    {
        return $this->hasOne('App\Transaction', 'id', 'transaction_id');
    }
}
