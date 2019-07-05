<?php

namespace Tests\Unit;

use App\User;
use App\Workflow\AuthWorkflow;
use Tests\TestCase;
use Tests\traits\TestTrait;
use Workflow;

class AuthWorkflowTest extends TestCase
{
    use TestTrait;

    public function setUp()
    {
        parent::setUp();

        $this->loadData();
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testWorkflow()
    {
        /**
         * @var User $user
         */
        $user = $this->getUser('test');
        /**
         * @var Workflow $workflow
         */
        $workflow = Workflow::get($user, 'auth');
        /**
         * @var string $workflow_name
         */
        $workflow_name = $workflow->getName();

        $this->assertNotNull($user);
        // пользователь уже зарегистрирован, поэтому п/у у него стоит transition -> registered
        // проверяем, что пользователю доступно подтверждение регистрации
        $this->assertTrue(
            $user->workflow_can(
                AuthWorkflow::TRANSITION_AUTH_CONFIRM,
                $workflow_name
            )
        );
        // подтверждаем регистрацию
        $user->workflow_apply(AuthWorkflow::TRANSITION_AUTH_CONFIRM, $workflow_name);
        // пользователь может быть возвращен обратно к подтверждению регистрации?
        $this->assertTrue(
            $user->authWorkflowCan(
                AuthWorkflow::TRANSITION_AUTH_BACK_TO_REGISTER
            )
        );
        // возвращаем пользователя обратно к подтверждению регистрации
        $user->authWorkflowApply(AuthWorkflow::TRANSITION_AUTH_BACK_TO_REGISTER);
        // может пользователь еще раз подтвердить регистрацию?
        $this->assertTrue(
            $user->authWorkflowCan(
                AuthWorkflow::TRANSITION_AUTH_CONFIRM
            )
        );
        // подтверждаем регистрацию
        $user->authWorkflowApply(AuthWorkflow::TRANSITION_AUTH_CONFIRM);

        // проверяем, что пользователю доступно подтверждение модератора
        $this->assertTrue(
            $user->workflow_can(
                AuthWorkflow::TRANSITION_AUTH_MODERATOR_CONFIRM,
                $workflow_name
            )
        );

        // регистрацию подтверждает модератор
        $user->workflow_apply(AuthWorkflow::TRANSITION_AUTH_MODERATOR_CONFIRM, $workflow_name);

        // проверяем, что пользователю доступны изменения профиля
        $this->assertTrue(
            $user->workflow_can(
                AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE,
                $workflow_name
            )
        );
        // пользователь внес изменения в профиле, и ждет одобрения модератора
        $user->workflow_apply(AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE, $workflow_name);
        // при этом пользователь больше не может вносить изменения прежде
        // чем предыдущие изменения не будут приняты модератором
        $this->assertNotTrue(
            $user->workflow_can(AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE,
                $workflow_name
            )
        );
        // пользователь может быть возвращен к подтверждению данных?
        $this->assertTrue(
            $user->authWorkflowCan(
                AuthWorkflow::TRANSITION_AUTH_BACK_TO_CHANGE
            )
        );
        // возвращаем пользователя обратно к подтверждению данных
        $user->authWorkflowApply(AuthWorkflow::TRANSITION_AUTH_BACK_TO_CHANGE);

        // проверяем, что пользователю доступны изменения профиля
        $this->assertTrue(
            $user->workflow_can(
                AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE,
                $workflow_name
            )
        );
        // пользователь внес изменения в профиле, и ждет одобрения модератора
        $user->workflow_apply(AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE, $workflow_name);

        // проверяем, что пользователю доступно подтверждения изменения профиля модератором
        $this->assertTrue(
            $user->workflow_can(
                AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE_CONFIRM,
                $workflow_name
            )
        );
        // модератор подтвердил изменения пользователя
        $user->workflow_apply(AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE_CONFIRM, $workflow_name);
        // ему снова доступно изменение профиля
        $this->assertTrue(
            $user->workflow_can(
                AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE,
                $workflow_name
            )
        );

        // еще три раза повторим последовательное изминение в профиле пользователя
        $this->again(3, function () use ($user, $workflow_name){
            // изменил данные
            $user->workflow_apply(
                AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE,
                $workflow_name
            );
            // новые изменения пока что не доступны
            $this->assertNotTrue(
                $user->workflow_can(
                    AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE,
                    $workflow_name
                )
            );
            // пользователя можно вернуть к подтверждению данных
            $this->assertTrue(
                $user->authWorkflowCan(
                    AuthWorkflow::TRANSITION_AUTH_BACK_TO_CHANGE
                )
            );
            // возвращаем пользователя обратно к подтверждению данных
            $user->authWorkflowApply(AuthWorkflow::TRANSITION_AUTH_BACK_TO_CHANGE);
            // пользователю доступно изменение профиля
            $this->assertTrue(
                $user->workflow_can(
                    AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE,
                    $workflow_name
                )
            );
            // изменил данные
            $user->workflow_apply(
                AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE,
                $workflow_name
            );
            // доступно подтверждение модератора
            $this->assertTrue(
                $user->workflow_can(
                    AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE_CONFIRM,
                    $workflow_name
                )
            );
            // модератор подтвердил изменения
            $user->workflow_apply(
                AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE_CONFIRM,
                $workflow_name
            );
            // пользователю снова доступно изменение профиля
            $this->assertTrue(
                $user->workflow_can(
                    AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE,
                    $workflow_name
                )
            );
        });
    }
    /**
     * Повторно вызывает переданную функцию указанное в $number количество раз
     * @param int $number
     * @param \Closure $callback
     */
    protected function again(int $number = 1, \Closure $callback)
    {
        // привязываем контекст функции
        $callback->bindTo($this);

        for($i = 0; $i < $number; $i++){
            $callback();
        }
    }
}
