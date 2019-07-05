<?php

use App\Traits\PluralizeTrait;
use App\User;
use App\VerifyUser;
use Illuminate\Database\Seeder;
use App\Workflow\AuthWorkflow;
use Silvanite\Brandenburg\Role;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    use PluralizeTrait;

    /**
     * @throws RuntimeException
     */
    public function run()
    {
        // Если пользователей еще нет
        if(User::count() === 0 && $this->command->confirm('Создать пользователя с правами модератора?')) {
            /**
             * @var string $name
             */
            $name = (string) $this->command->ask('Введите имя пользователя');
            /**
             * @var string $email
             */
            $email = (string) $this->command->ask('Введите Email пользователя');
            /**
             * @var string $password
             */
            $password = (string) $this->command->secret('Пароль');
            /**
             * @var User $user
             */
            $user = tap((new User())->forceFill([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]))->save();

            /**
             * Ищем роль "модератор"
             * @var Role $role
             */
            $role = Role::where(['id' => User::ROLE_MODERATOR])->first();

            if($user->workflow_can(AuthWorkflow::TRANSITION_AUTH_REGISTER)) {
                // пользователь зарегестрирован
                $user->workflow_apply(
                    AuthWorkflow::TRANSITION_AUTH_REGISTER,
                    AuthWorkflow::WORKFLOW_NAME
                );
            }
            // добавляем в базу проверку пользователя
            VerifyUser::create([
                'user_id' => $user->id,
                'token' => str_random(40)
            ]);
            // пользователь подтвердил регистрацию
            $user->verified = true;
            // проставляем пользователю роль простого пользователя
            $user->roles()->attach($role);

            if($user->workflow_can(AuthWorkflow::TRANSITION_AUTH_CONFIRM)) {
                // подтверждаем регистрацию пользователя
                $user->workflow_apply(
                    AuthWorkflow::TRANSITION_AUTH_CONFIRM,
                    AuthWorkflow::WORKFLOW_NAME
                );
            }

            if($user->workflow_can(AuthWorkflow::TRANSITION_AUTH_MODERATOR_CONFIRM)) {
                // модератор подтвердил регистрацию
                $user->workflow_apply(
                    AuthWorkflow::TRANSITION_AUTH_MODERATOR_CONFIRM,
                    AuthWorkflow::WORKFLOW_NAME
                );
            }

            $user->save();
        }
        /**
         * @var int $count
         */
        $count = (int) $this->command->ask('Сколько пользователей нужно создать?', 5);
        // если передан ноль - пользователей создавать не нужно
        if($count === 0) return;

        $this->command->info(
            sprintf("Создание {$count} %s", $this->pluralize($count, ['пользователя', 'пользователей', 'пользователей']))
        );
        /**
         * @var User[] $users
         */
        $users = factory(User::class, $count)->create();

        if($users && $users->count() > 0) {
            /**
             * Ищем роль "пользователь НЕ модератор"
             * @var Role $role
             */
            $role = Role::where(['id' => User::ROLE_USER])->first();

            $users->each(function (User $user) use ($role) {
                if($user->workflow_can(AuthWorkflow::TRANSITION_AUTH_REGISTER)) {
                    // пользователь зарегестрирован
                    $user->workflow_apply(
                        AuthWorkflow::TRANSITION_AUTH_REGISTER,
                        AuthWorkflow::WORKFLOW_NAME
                    );
                }
                // добавляем в базу проверку пользователя
                VerifyUser::create([
                    'user_id' => $user->id,
                    'token' => str_random(40)
                ]);
                // пользователь подтвердил регистрацию
                $user->verified = true;
                // проставляем пользователю роль простого пользователя
                $user->roles()->attach($role);

                $user->save();
            });
            /**
             * @var boolean $confirmed
             */
            $confirmed = $this->command->confirm('Пользователи должны быть уже подтвержденными?');

            if ($confirmed) {
                $users->each(function (User $user) {
                    if($user->workflow_can(AuthWorkflow::TRANSITION_AUTH_CONFIRM)) {
                        // подтверждаем регистрацию пользователя
                        $user->workflow_apply(
                            AuthWorkflow::TRANSITION_AUTH_CONFIRM,
                            AuthWorkflow::WORKFLOW_NAME
                        );

                        $user->save();
                    }
                });
            } else {
                $this->command->info('Пользователи успешно созданы'); return;
            }
            /**
             * @var boolean $moderatorConfirmed
             */
            $moderatorConfirmed = $this->command->confirm('Пользователи должны быть уже подтвержденными модератором?');

            if($moderatorConfirmed) {
                $users->each(function (User $user) {
                    if($user->workflow_can(AuthWorkflow::TRANSITION_AUTH_MODERATOR_CONFIRM)) {
                        // модератор подтвердил регистрацию
                        $user->workflow_apply(
                            AuthWorkflow::TRANSITION_AUTH_MODERATOR_CONFIRM,
                            AuthWorkflow::WORKFLOW_NAME
                        );

                        $user->save();
                    }
                });
            } else {
                $this->command->info('Пользователи с подтвержденной регистрацией успешно созданы'); return;
            }

            $this->command->info('Пользователи с подтвержденной модератором регистрацией успешно созданы');
        }
    }
}
