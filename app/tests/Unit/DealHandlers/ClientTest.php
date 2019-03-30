<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08.02.2019
 * Time: 21:22
 */

namespace Tests\Unit\DealHandlers;

use App\DealHandlers\Calculators\PhotobookCalculator;
use App\DealHandlers\Exceptions\PrintPhotobookException;
use App\DealHandlers\Helpers\BookLayouts;
use App\Models\ClientType;
use App\Workflow\PrintPhotobook;
use App\Models\Deal;
use App\Models\DealType;
use App\Models\Payment;
use App\Models\User;
use Tests\TestCase;
use Tests\traits\TestDealTrait;
use WorkflowTestSeeder;

class ClientTest extends TestCase
{
    use TestDealTrait;

    public function setUp()
    {
        parent::setUp();

        $this->loadTestDealData();
    }

    public function testClient()
    {
        // Выбираем пользователя
        /**
         * @var User $user
         */
        $user = User::where('name', 'Тестовый пользователь')->first();

        $this->assertNotNull($user);

        // Получаем аккаунт пользователя
        $account = $user->getAccount();

        $this->assertNotNull($account);
        // пополняем счет
        $payment = $account->add(2000, Payment::STATUS_CREATED);
        // подтверждаем платеж, теперь на счете 2000
        $payment->confirm();

        // берем заявку в статусе ожидание оплаты
        $deal = $this->getDeal('Печать книг. Отправка в типографию');
        // Проверяем скидку - должна быть равна нулю
        $this->assertEquals(0, $user->client->getDiscount());
        // стоимость с учетом существующей скидки (0%)
        $this->assertEquals(1267, $deal->handler->calculate());
        //сколько бы мы раз не рассчитывали одну и ту же сделку ее стоимость не должна меняться
        $this->assertEquals(1267, $deal->handler->calculate());
        $this->assertEquals(1267, $deal->handler->calculate());

        // проверяем тип клиента
        $this->assertEquals(ClientType::CLIENT_SIMPLE, $user->client->getType());
        // количество оплаченных платежей
        $this->assertEquals(0, $user->getDealCount());
        // оплачиваем заказ
        $account->payDeal($deal);
        // получаем пользователя
        $user = $deal->user;
        // теперь количество оплаченных заказов должно быть равно 1
        $this->assertEquals(1, $user->getDealCount());
        // и скидка равна 5%
        $this->assertEquals(5, $user->client->getDiscount());

        //стоимость оплаченной сделки никогда не должна меняться
        $this->assertEquals(1267, $deal->handler->calculate());
        $this->assertEquals(1267, $deal->handler->calculate());


        // Создаем сделку, валидную для оплаты
        $deal = Deal::create([
            'user_id'      => $user->id,
            'deal_type_id' => DealType::TYPE_PRINT_PHOTOBOOK,
            'places'        => [
                PrintPhotobook::PLACE_PRINT_PHOTOBOOK_TYPOGRAPHY_SENDING => 1
            ],
            'cost'         => 1267,
            'params'       => [
                'test'  => 'Печать книг. Отправка в типографию',
                'calculator' => WorkflowTestSeeder::getValidParameters(),
            ]
        ]);
        // проверяем заказ
        $this->assertNotNull($deal);
        // проверяем стоимость без учета скидки
        $this->assertEquals(1267, $deal->cost);

        $deal->handler->calculate();

        // проверяем стоимость с учетом скидки (5%)
        $this->assertEquals(round(1267/1.05), $deal->handler->calculate());

        //сколько бы мы раз не рассчитывали одну и ту же сделку ее стоимость не должна меняться
        $this->assertEquals(round(1267/1.05), $deal->handler->calculate());
        $this->assertEquals(round(1267/1.05), $deal->handler->calculate());

        //меняем на выпускные с тиражом менее 10шт
        $deal->handler->setCalculatorParameter('type', PhotobookCalculator::TYPE_VIPUSK_STANDARD);
        $deal->handler->calculate();
        $this->assertEquals(
            [
                "src" => 515,
                "base" => 515,
                "discount" => 5,
                "discountPrice" => 490,
                "markup" => 1.1,
                "price" => 539,
                "total" => 539,
            ],
            $deal->handler->getPriceDetails()
        );

        $deal->handler->setCalculatorParameter('bookNum', 10);
        $deal->handler->calculate();

        $this->assertEquals(
            [
                "src" => 515,
                "base" => 515,
                "discount" => 5,
                "discountPrice" => 490,
                "markup" => 1,
                "price" => 490,
                "total" => 4900,
            ],
            $deal->handler->getPriceDetails()
        );



        $this->loadTestDealData(true);

        $deal = $this->getDeal('Печать книг. Отправка в типографию');
        $deal->user->client_type_id = ClientType::CLIENT_VIP;
        $deal->user->save();

        $this->assertEquals(10, $deal->user->client->getDiscount());
        $deal->handler->calculate();

        $this->assertEquals(ceil(1267/1.05 * 0.9), $deal->handler->calculate());
        $this->assertEquals(ceil(1267/1.05 * 0.9), $deal->handler->calculate());
        $this->assertEquals(ceil(1267/1.05 * 0.9), $deal->handler->calculate());

        //меняем на выпускные с тиражом менее 10шт
        //наценки для випов не действуют
        $deal->handler->setCalculatorParameter('type', PhotobookCalculator::TYPE_VIPUSK_STANDARD);
        $deal->handler->calculate();
        $this->assertEquals(
            [
                "src" => 515,
                "base" => 491,
                "discount" => 10,
                "discountPrice" => 442,
                "markup" => 1,
                "price" => 442,
                "total" => 442,
            ],
            $deal->handler->getPriceDetails()
        );

        $deal->handler->setCalculatorParameter('bookNum', 10);
        $deal->handler->calculate();

        $this->assertEquals(
            [
                "src" => 515,
                "base" => 491,
                "discount" => 10,
                "discountPrice" => 442,
                "markup" => 1,
                "price" => 442,
                "total" => 4420,
            ],
            $deal->handler->getPriceDetails()
        );


    }
}
