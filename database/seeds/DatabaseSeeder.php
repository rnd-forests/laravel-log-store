<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        factory(App\User::class)->create([
            'name' => 'Vinh Nguyen',
            'email' => 'vinhnguyen@nowhere.com',
        ]);

        factory(App\Lesson::class)->create();
    }
}
