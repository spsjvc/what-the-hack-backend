<?php

use Illuminate\Database\Seeder;
use App\Models\Seat;

class SeatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <=3; $i++) {
            for ($j = 1; $j <= 30; $j++) {
                Seat::create([
                    'number' => $j,
                    'room_id' => $i,
                    'is_taken' => 0
                ]);
            }
        }
    }
}
