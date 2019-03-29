<?php

namespace App\Http\Controllers;

use Crabler\Common\Facades\PaymentGateway;
use App\Models\Payment;
use App\Models\User;
use Crabler\Common\PaymentGateways\GatewayPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Laravel\Nova\Nova;

/**
 * Class YandexKassaController
 * @package App\Http\Controllers
 */

class PaymentsController extends Controller
{
    /**
     * Создает платеж, перенаправляет пользователя на страницу яндекс.кассы
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */

    protected function pay(Request $request)
    {
        // Получаем пользователя
        /**
         * @var User $user
         */
        $user = Auth::user();

        if($user && intval($request->amount) > 0) {

            // Создаем не подтвержденный платеж
            $payment = $user->getAccount()
                ->add($request->amount, Payment::STATUS_CREATED);

            $response = PaymentGateway::create($this->createGatewayPayment($payment));

            if (!$response) {
                Session::flash('error', 'Произошла ошибка при выполнении запроса.');
                return redirect(Nova::path());
            }

            $payment->gateway_id = $response->getId();
            $payment->save();

            // Отправляем пользователя на страничку подтверждения на Яндекс.Кассе
            return redirect()->away($response->getConfirmationUrl());
        }
    }

    /**
     * Пользователь возвращается сюда со страницы яндекс.кассы после подтверждения платежа
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function confirm(Request $request)
    {
        /**
         * @var Payment $payment
         */
        $payment = $request->attributes->get('payment');

        if($payment->isConfirmed()) {
            Session::flash('success', 'Баланс успешно пополнен');
            return redirect(Nova::path());
        }

        $payment->updateGatewayStatus(
            PaymentGateway::checkStatus($this->createGatewayPayment($payment))
        );

        if ($payment->isConfirmed()) {
            Session::flash('success', 'Баланс успешно пополнен');
        } elseif ($payment->isWaitingComfirmation()) {
            Session::flash('success', 'Баланс будет пополнен в течение нескольких минут');
        }

        return redirect(Nova::path());

    }

    /**
     * Заглушка для уведомлений платежного шлюза
     *
     * @param Request $request
     */
    public function confirmGateway(Request $request)
    {
        exit;
    }

    /**
     * Возвращает список оплат пользователей
     * А так же общее количество совершенных операций оплаты (см. Http/Controllers/PaymentsController.php:105)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayments(Request $request)
    {
       if(!Auth::check()) {
           return response()->json([
               'access' => false
           ]);
       }
        // Номер страницы
        // отнимаем -1, что бы работать с массивом
       $page = ((int) $request->page) - 1;
       // Количество элементов на каждой странице
       $perPage = (int) $request->perPage;

       $from = $perPage * max($page, 0);
        // Получаем польователя
       $user = Auth::user();
        // Если пользователь - супер-юзер
        // То получаем все оплаты - и подтвержденные, и не подтвержденные
       if(Session::has('super_user') && Session::get('super_user')) {
           $payments = Payment::where(
               [
                   'account_id' => $user->getAccount()->id
               ]
           )->orderBy('created_at', 'desc')->get();
       } else {
           // Иначе - получаем все подтвержденные оплаты пользователя
           $payments = Payment::where(
               [
                   'account_id' => $user->getAccount()->id,
                   'status' => Payment::STATUS_PAID
               ]
           )->orderBy('created_at', 'desc')->get();
       }
        // Считаем общее количество оплат (нужно для корректной работы пагинации)
       $total = $payments->count();
        // Возвращаем только те оплаты, которые должны отображаться в соответствии
        // с номером страницы
       $payments = $payments->slice($from, $perPage)->values();

       foreach ($payments as &$payment) {
           // Пока что заказов у нас нет
           // поэтому это просто заглушка
           if($payment->type === Payment::TYPE_DEBET) {
               $payment->order = '-';
           } else {
               /**
                * @var Deal $deal
                */
               $deal = $payment->deal;

               if ($deal) {
                   $payment->order = [
                       'id' => $deal->id,
                       'url' => $deal->getDealUrl()
                   ];
               } else {
                   $payment->order = '-';
               }
           }

           $payment->date = date('d.m.Y', strtotime($payment->created_at));
       }

       return response()->json([
           'access' => true,
           'balance' => $user->getAccount()->balance,
           'payments' => $payments,
           'total' => $total,
           'super' => Session::has('super_user') ? Session::get('super_user') :  false
       ]);
    }

    /**
     * Возвращает экземпляр отдельной оплаты
     * Для вывода на отдельной странице
     *
     * @param Request $request
     * @return mixed
     */
    public function singlePayment(Request $request)
    {
       return $request->attributes->get('payment');
    }

    /**
     * Функция для супер-пользователя
     * Подтвержает платеж
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suconfirm(Request $request)
    {
        /**
         * @var Payment $payment
         */
        $payment = $request->attributes->get('payment');
        // Если пользователь - супер-пользователь
        // И пароль подходит
        if(Session::get('super_user') && $request->password === env('SU_PASSWORD')) {
            // очищаем gateway_id и gateway_status
            $payment->gateway_id = null;
            $payment->gateway_status = null;
            $payment->is_su = true;
            // подтверждаем платеж
            $payment->confirm();
            // Возвращаем пользователю ответ об успешном выполнении
            return response()->json([
                'access' => true,
                'sum' => $payment->sum
            ]);

        } else {
            // Если хотя бы одно условие не совпадает
            // Возвращает false
            // Для того, что бы на фронте отобразилось уведомление об ошибке
            return response()->json([
                'access' => false
            ]);
        }
    }

    /**
     * Возвращает баланс пользователя
     * Для компоненты баланса
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function balance(Request $request)
    {
       return response()->json(
           ['balance' => Auth::user()->getAccount()->balance]
       );
    }

    /**
     * @param Payment $payment
     * @return GatewayPayment
     */
    private function createGatewayPayment(Payment $payment)
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        $gp = new GatewayPayment();
        $gp->setSum($payment->sum);
        $gp->setDescription($payment->description);
        $gp->setId($payment->id);
        $gp->setGatewayId($payment->gateway_id);

        $receipt = [
            'email' => $user->email,
            'items' => [
                [
                    "description" => "Изготовление фотокниги",
                    "quantity" => 1,
                    "amount" => [
                        "value" => $payment->sum,
                        "currency" => "RUB"
                    ],
                    "vat_code" => "2",
                    "payment_mode" => "full_prepayment",
                    "payment_subject" => "service"

                ]
            ]
        ];

        $gp->setReceipt($receipt);
        return $gp;
    }
}
