<?php

use Illuminate\Database\Seeder;

class BoardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('boards')->insert([
            'id'            => 1,
            'goal_position' => 30,
            'goal_status'   => config('const.piece_status_health')
        ]);
    }
}
