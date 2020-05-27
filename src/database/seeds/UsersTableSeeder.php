<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'id'        => config('const.virus_user_id'),
            'name'      => 'ウィルス',
            'email'     => 'sugoroku@example.com',
            'password'  => 'testtest'
        ]);
    }
}
