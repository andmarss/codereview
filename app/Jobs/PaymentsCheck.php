<?php

namespace App\Jobs;

use Crabler\Common\Facades\PaymentGateway;
use Crabler\Common\PaymentGateways\GatewayPayment;
use Crabler\Common\PaymentGateways\GatewayPaymentStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Payment;
use Illuminate\Support\Facades\Artisan;

class PaymentsCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var Payment[] $payments
         */
        $payments = Payment::where(['status' => Payment::STATUS_CREATED])->get();

        foreach ($payments as $payment) {
            $payment->updateGatewayStatus(
                PaymentGateway::checkStatus(
                    $this->createGatewayPayment($payment)
                )
            );
        }
    }

    /**
     * @param Payment $payment
     * @return GatewayPayment
     */
    private function createGatewayPayment(Payment $payment)
    {
        $gp = new GatewayPayment();
        $gp->setSum($payment->sum);
        $gp->setDescription($payment->description);
        $gp->setId($payment->id);
        $gp->setGatewayId($payment->gateway_id);
        return $gp;
    }
}
