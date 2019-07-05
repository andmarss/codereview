<?php

use App\User;
use App\Workflow\AuthWorkflow;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('APP_ENV') != 'testing') {
            exit('TestSeeder can run only in test environment');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        User::truncate();

        $user = User::create([
            'name' => 'test',
            'email' => 'test@mail.ru',
            'password' => bcrypt('secret')
        ]);

        $user->workflow_apply(AuthWorkflow::TRANSITION_AUTH_REGISTER, 'auth');

        $user->save();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
