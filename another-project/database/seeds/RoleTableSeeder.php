<?php

use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Начинаем загрузку ролей');

        collect(['Пользователь', 'Модератор'])->each(function (string $role_name){
            \Silvanite\Brandenburg\Role::create([
                'name' => $role_name,
                'slug' => str_slug($role_name)
            ]);
        });

        $this->command->info('Роли успешно загружены');
    }
}
