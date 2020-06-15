<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Transaction
 * @package App
 */
class Transaction extends Model
{
    use SoftDeletes;

    const MAX_REMAINING_SECONDS = 120;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations ()
    {
        return $this->hasMany('App\Reservation', 'transaction_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order ()
    {
        return $this->hasOne('App\Order', 'transaction_id', 'id');
    }

    /**
     * @return Transaction
     */
    public static function generate ()
    {
        $transaction = new self();
        $transaction->code = str_replace('/', '-', bcrypt(strtotime(date('Y-m-d H:i:s')) . microtime() . rand(1000, 1000000)));
        $transaction->save();

        return $transaction;
    }
}
