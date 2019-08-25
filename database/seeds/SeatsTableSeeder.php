<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Seat;

class SeatsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = 1;
        $serials = 3;
        $price = 1500;

        DB::beginTransaction();
        DB::transaction(function () use ($rows, $serials, $price) {
            for ($row = 1; $row <= $rows; $row++) {
                for ($serial = 1; $serial <= $serials; $serial++) {
                    $seat = new Seat();
                    $seat->row = $row;
                    $seat->serial = $serial;
                    $seat->price = $price + ($serial * 100);
                    $seat->save();
                }
            }
        });
        DB::commit();
    }
}
