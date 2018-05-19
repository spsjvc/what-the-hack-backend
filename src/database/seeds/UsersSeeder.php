<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Milan Bačkonja',
            'email' => 'milan@backonja.com',
            'password' => bcrypt('student')
        ]);
        User::create([
            'name' => 'Aleksandar Babić',
            'email' => 'aleksandar@babic.com',
            'password' => bcrypt('student')
        ]);
        User::create([
            'name' => 'Milos Miljanic',
            'email' => 'milos@miljanic.com',
            'password' => bcrypt('student')
        ]);
        User::create([
            'name' => 'Dragisa Spasojević',
            'email' => 'dragisa@spasojevic.com',
            'password' => bcrypt('student')
        ]);
    }
}
