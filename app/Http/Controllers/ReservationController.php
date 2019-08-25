<?php

namespace App\Http\Controllers;

use App\Order;
use App\Reservation;
use App\Seat;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    public function index () {
        return view('index');
    }

    public function getSettings ($code = null) {
        $is_new_transaction_code = 0;
        if (!$transaction = $this->getTransaction($code)) {
            $transaction = Transaction::generate();
            $is_new_transaction_code = 1;
        }

        return response()->json([
            'transaction' => [
                'code' => $transaction->code,
                'is_new' => $is_new_transaction_code,
            ],
            'seat_status' => [
                'free' => Seat::STATUS_FREE,
                'own_reservation' => Seat::STATUS_OWN_RESERVATION,
                'reserved' => Seat::STATUS_RESERVED,
                'ordered' => Seat::STATUS_ORDERED,
            ],
            'remaining_time' => [
                'seconds' => $this->getRemainingSeconds($transaction),
                'max_seconds' => Transaction::MAX_REMAINING_SECONDS,
            ],
        ]);
    }

    public function getSeats ($transaction_code) {
        $transaction = $this->getTransaction($transaction_code, true);
        $seats = Seat::all();
        foreach ($seats as $s) {
            $s->status = $s->getStatus($transaction);
        }

        return response()->json($seats);
    }

    public function reserve (Request $request) {
        $transaction = $this->getTransaction($request->get('transaction_code'), true);
        $seat = Seat::findOrFail($request->get('id'));

        if ($seat->getStatus($transaction) != Seat::STATUS_FREE) {
            return response()->json(['success' => 0, 'status' => $seat->getStatus($transaction)]);
        }

        $reservation = new Reservation();
        $reservation->transaction_id = $transaction->id;
        $reservation->seat_id = $seat->id;
        $reservation->save();

        $transaction->updated_at = Carbon::now()->format('Y-m-d H:i:s');
        $transaction->save();

        return response()->json(['success' => 1, 'status' => $seat->getStatus($transaction)]);
    }


    public function revoke (Request $request) {
        $transaction = $this->getTransaction($request->get('transaction_code'), true);
        $seat = Seat::findOrFail($request->get('id'));

        if ($seat->getStatus($transaction) != Seat::STATUS_OWN_RESERVATION) {
            return response()->json(['success' => 0, 'status' => $seat->getStatus($transaction)]);
        }

        $seat->reservations()->delete();

        $transaction->updated_at = Carbon::now()->format('Y-m-d H:i:s');
        $transaction->save();

        return response()->json(['success' => 1, 'status' => $seat->getStatus($transaction)]);
    }

    public function order (Request $request) {
        $transaction = $this->getTransaction($request->get('transaction_code'), true);
        $order = new Order();

        DB::beginTransaction();
        DB::transaction(function () use ($request, $transaction, $order) {
            $order->transaction_id = $transaction->id;
            $order->firstname = $request->get('order', [])['firstName'];
            $order->lastname = $request->get('order', [])['lastName'];
            $order->email = $request->get('order', [])['email'];
            $order->save();

            foreach ($request->get('reservations', []) as $res) {
                $seat = Seat::findOrFail($res['id']);
                if ($seat->getStatus($transaction) != Seat::STATUS_OWN_RESERVATION) {
                    continue;
                }

                $reservation = Reservation::where('seat_id', $seat->id)->where('transaction_id', $transaction->id)->first();
                $reservation->order_id = $order->id;
                $reservation->save();
            }

            if (!$order->reservations->count()) {
                $order->delete();
            }

            $transaction->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $transaction->save();
        });
        DB::commit();

        if (!$order) {
            return response()->json(['success' => 0]);
        }

        Mail::send('order_email', ['order' => $order], function ($m) use ($order) {
            $m->from('no-reply@bardiauto.hu', 'Bardiauto.hu');
            $m->bcc('andreas.georgopulos@gmail.com');
            $m->subject('Rendelés visszaigazolás');
            $m->to($order->email);
        });

        return response()->json(['success' => 1]);
    }

    private function getTransaction ($code, bool $fail = false) : ?Transaction {
        $transaction = Transaction::where('code', $code)->doesnthave('order');
        return $fail ? $transaction->firstOrFail() : $transaction->first();
    }

    private function getRemainingSeconds (Transaction $transaction) : int {
        if (!$transaction->reservations->count()) {
            return 0;
        }

        $timestamp_last_update = Carbon::createFromFormat('Y-m-d H:i:s', $transaction->updated_at)->timestamp;
        $timestamp_now = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'))->timestamp;
        $seconds = Transaction::MAX_REMAINING_SECONDS - ($timestamp_now - $timestamp_last_update);
        $seconds = (int) $seconds > 0 ? $seconds : 0;

        if (!$seconds) $transaction->reservations()->delete();

        return $seconds;
    }
}
