<?php

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Room::create([
            'name' => 'Room 1',
            'faculty' => 'FTN'
        ]);

        Room::create([
            'name' => 'Room 2',
            'faculty' => 'FTN'
        ]);

        Room::create([
            'name' => 'Room 3',
            'faculty' => 'FTN'
        ]);
    }
}
