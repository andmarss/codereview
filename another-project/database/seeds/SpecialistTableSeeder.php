<?php

use App\User;
use Illuminate\Database\Seeder;
use App\Traits\PluralizeTrait;

class SpecialistTableSeeder extends Seeder
{
    use PluralizeTrait;

    const DEFAULT_MAX_RANGE = 10;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Получаем простых пользователей
         * НЕ специалистов
         * @var User[] $users
         */
        $users = \App\User::notDoctors()->get();
        /**
         * @var int $count
         */
        $count = (int) $users->count();

        // Если простых пользователей, НЕ специалистов, нет - ничего не делаем
        if($count === 0) {
            $this->command->error('Нет доступных пользователей для создания специалистов.');
            return;
        }
        /**
         * @var int $count
         */
        $count = (int) $this->command->ask(sprintf("Сколько специалистов нужно добавить? %s {$count} %s", $this->pluralize($count, ['Доступен', 'Доступно', 'Доступно']), $this->pluralize($count, ['пользователь', 'пользователя', 'пользователей'])), $count);
        // если был передан ноль - ничего не делать
        if($count === 0) return;
        // если передано число больше, чем доступно пользователей
        // обрываем выполнение
        if($count > $users->count()) {
            /**
             * @var int $usersCount
             */
            $usersCount = $users->count();

            $this->command->error(sprintf("Невозможно создать {$count} %s, т.к. доступно только {$usersCount} %s.", $this->pluralize((int)$count, ['специалиста', 'специалистов', 'специалистов']), $this->pluralize((int)$usersCount, ['пользователь', 'пользователя', 'пользователей'])));
            return;
        }
        /**
         * @var array $specialists
         */
        $specialists = [];

        $this->command->info(sprintf("Создание {$count} %s.", $this->pluralize($count, ['специалиста', 'специалистов', 'специалистов'])));

        $users
            ->splice(0, $count)
            ->each(function (User $user) use (&$specialists) {
                $specialists[] = factory(\App\Specialist::class)->create(['user_id' => $user->id]);
            });

        if ($this->command->confirm(sprintf('Нужно ли добавить отзывы %s?', $this->pluralize($count, ['специалисту', 'специалистам', 'специалистам'])))) {
            /**
             * @var string $range
             */
            $range = '1-10';
            /**
             * @var string $range
             */
            $range = (string) $this->command->ask('Сколько отзывов нужно добавить специалистам?', $range);
            // если было по ошибке передано одно строковое число, не диапазон
            // превращаем его в диапазон
            if(!$this->is_range($range)) $range = $this->create_range($range);

            collect($specialists)
                ->each(function (\App\Specialist $specialist) use ($range) {
                    factory(\App\SpecialistReview::class, $this->count_range($range))
                        ->create([
                            'specialist_id' => $specialist->id
                        ]);
                });

            $this->command->info('Специалисты и отзывы к ним успешно добавлены');
        } else {
            $this->command->info('Специалисты успешно добавлены');
        }
    }

    /**
     * @param string $range
     * @return int
     */
    protected function count_range(string $range): int
    {
        return rand(...explode('-', $range));
    }
    /**
     * Проверяет, передан ли диапазон
     * @param string $range
     * @return bool
     */
    protected function is_range(string $range): bool
    {
        return count(explode('-', $range)) > 1;
    }

    /**
     * Превращает переданное строковое число в диапазон
     * @param string $number
     * @return string
     */
    protected function create_range(string $number): string
    {
        $number = (int) $number;

        return min($number, static::DEFAULT_MAX_RANGE) . '-' . max($number, static::DEFAULT_MAX_RANGE);
    }
}
