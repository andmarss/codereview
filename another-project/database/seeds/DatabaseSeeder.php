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
        if (env('APP_ENV') !== 'local') {
            exit('DatabaseSeeder может быть вызвана только в локальном окружении.');
        }

        // Нужно ли обновить БД, по умолчанию нет
        if ($this->command->confirm('Нужно ли обновить миграции перед заполнением? Это очистит все старые данные.')) {

            // Вызываем php artisan migrate:fresh
            $this->command->call('migrate:fresh');

            $this->command->line("База данных очищена.");
        }

        \Illuminate\Database\Eloquent\Model::unguard();

        if($this->command->confirm('Нужно ли добавлять роли? (Пользователь и Модератор)')) {
            $this->call(RoleTableSeeder::class);
        }

        if($this->command->confirm('Нужно ли добавлять пользователей?')) {
            $this->call(UsersTableSeeder::class);
        }

        if($this->command->confirm('Нужно ли добавлять специалистов?')) {
            $this->call(SpecialistTableSeeder::class);
        }

        $this->command->info("Данные загружены.");

        \Illuminate\Database\Eloquent\Model::reguard();
    }
}
