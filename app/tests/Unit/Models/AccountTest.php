<?php

namespace Tests\Models;

use App\Models\Account;
use App\Models\Deal;
use App\Models\DealType;
use App\Models\Payment;
use App\Models\User;
use App\Workflow\PrintPhotobook;
use Tests\TestCase;
use Tests\traits\TestDealTrait;
use WorkflowTestSeeder;

class AccountTest extends TestCase
{

    use TestDealTrait;

    public function setUp()
    {
        parent::setUp();

        $this->loadTestDealData();
    }

    public function testAccount()
    {
        // Выбираем пользователя
        $user = User::where('name', 'Тестовый пользователь')->first();
        // Счета нет
        $account = $user->account;

        $this->assertNull($account);
        // Создаем счет
        $this->assertInstanceOf(Account::class, $user->getAccount());
        // Проверяем, что счет сохранился в БД
        $account = $user->account;

        $this->assertNotNull($account);
        // Создаем неподтвержденный платеж
        $payment = $account->add(500, Payment::STATUS_CREATED);
        // Баланс должен быть равным нулю
        $this->assertEquals(0, $account->balance);
        // Подтверждаем платеж
        $payment->confirm();
        // Проверяем, что баланс теперь равен добавленной сумме
        $this->assertEquals(500, $payment->account->balance);
    }

    public function testDeal()
    {
        // Выбираем пользователя
        $user = User::where(['name' => 'Тестовый пользователь'])->first();

        $this->assertNotNull($user);

        $this->assertNotNull($user->account);

        // Создаем сделку, валидную для оплаты
        $deal = Deal::create([
            'user_id'      => $user->id,
            'deal_type_id' => DealType::TYPE_PRINT_PHOTOBOOK,
            'places'        => [
                PrintPhotobook::PLACE_PRINT_PHOTOBOOK_PAYING => 1
            ],
            'cost'         => 500,
            'params'       => [
                'test'  => 'Печать книг. Оплата',
                'calculator' => WorkflowTestSeeder::getValidParameters(),
            ]
        ]);

        $this->assertNotNull($deal);

        $this->assertTrue($user->account->canPay($deal->cost));

        // Проверяем, может ли пользователь оплатить
        if($user->account->canPay($deal->cost)) {
            // Если пользователь может оплатить - оплачиваем
            $user->account->payDeal($deal);
            // Проверяем, списались ли деньги со счета
            $this->assertEquals(0, $user->account->balance);
        }
    }


}
