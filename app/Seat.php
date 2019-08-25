<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seat extends Model
{
    use SoftDeletes;

    const STATUS_FREE = 0;
    const STATUS_OWN_RESERVATION = 1;
    const STATUS_RESERVED = 2;
    const STATUS_ORDERED = 3;

    public function reservations () {
        return $this->hasMany('App\Reservation');
    }

    public function getStatus (Transaction $transaction): int {
        if (!$reservation = $this->reservations()->first()) {
            $status = self::STATUS_FREE;
        }
        else if ($reservation->transaction->id == $transaction->id && !$reservation->order) {
            $status = self::STATUS_OWN_RESERVATION;
        }
        else if (!$reservation->order) {
            $status = self::STATUS_RESERVED;
        }
        else {
            $status = self::STATUS_ORDERED;
        }

        return $status;
    }
}
