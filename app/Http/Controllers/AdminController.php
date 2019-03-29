<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Payment;
use App\Models\User;
use App\Workflow\PrintPhotobook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\FilesController;

class AdminController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('nova::admin.index');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers(Request $request)
    {
        if(!auth()->check()) {
            return response()->json([
                'access' => false
            ]);
        }
        /**
         * @var int $page
         */
        $page = ((int) $request->page) - 1;
        /**
         * @var int $perPage
         */
        $perPage = (int) $request->per_page;
        /**
         * @var int $from
         */
        $from = $perPage * max($page, 0);
        if(!is_null($request->email)) {
            /**
             * @var User[] $collectUsers
             */
            $collectUsers = User::where('email', 'like', '%' . $request->email . '%')
                ->orderBy('id', 'desc')
                ->get();
        } else {
            /**
             * @var User[] $collectUsers
             */
            $collectUsers = User::orderBy('id', 'desc')->get();
        }
        /**
         * @var int $total
         */
        $total = $collectUsers->count();
        /**
         * @var User[] $collectUsers
         */
        $collectUsers = $collectUsers->slice($from, $perPage)->values();
        /**
         * @var array $users
         */
        $users = [];

        foreach ($collectUsers as $user) {
            $users[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'bd' => $user->getBdUrl(),
            ];
        }

        return response()->json([
            'access' => true,
            'users' => $users,
            'total' => $total
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userDeals(Request $request)
    {
        /**
         * @var User $user
         */
        $user = User::find($request->route('id'));
        /**
         * @var Deal[] $collectDeals
         */
        $collectDeals = Deal::where(['user_id' => $user->id])->orderBy('id', 'desc')->get();
        /**
         * @var int $total
         */
        $total = $collectDeals->count();
        /**
         * @var int $page
         */
        $page = ((int) $request->page) - 1;
        /**
         * @var int $perPage
         */
        $perPage = (int) $request->per_page;
        /**
         * @var int $from
         */
        $from = $perPage * max($page, 0);
        /**
         * @var Deal[] $collectDeals
         */
        $collectDeals = $collectDeals->slice($from, $perPage)->values();
        /**
         * @var array $deals
         */
        $deals = [];

        foreach ($collectDeals as $deal) {
            /**
             * @var bool $filesLoaded
             */
            $fileUrl = $deal->handler->getArchiveUrl();

            $deals[] = [
                'id' => $deal->id,
                'status' => PrintPhotobook::getPlaceTitle($deal->handler->getClientPlace()),
                'filesLoaded' => (bool)$fileUrl,
                'link' => $fileUrl,
                'type' => $deal->getParam('calculator.type'),
                'params' => $deal->params
            ];
        }

        return response()->json([
            'access' => true,
            'total' => $total,
            'deals' => $deals
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userPayments(Request $request)
    {
        /**
         * @var User $user
         */
        $user = User::find($request->route('id'));
        /**
         * @var Payment[] $collectDeals
         */
        $collectPayments = Payment::where(['account_id' => $user->getAccount()->id])->orderBy('id', 'desc')->get();
        /**
         * @var int $total
         */
        $total = $collectPayments->count();
        /**
         * @var int $page
         */
        $page = ((int) $request->page) - 1;
        /**
         * @var int $perPage
         */
        $perPage = (int) $request->per_page;
        /**
         * @var int $from
         */
        $from = $perPage * max($page, 0);
        /**
         * @var Payment[] $collectPayments
         */
        $collectPayments = $collectPayments->slice($from, $perPage)->values();

        foreach ($collectPayments as $payment) {
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
            'total' => $total,
            'payments' => $collectPayments
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request)
    {
        /**
         * @var User $user
         */
         $user = User::find($request->route('id'));

         if($user) {
             return response()->json([
                 'access' => true,
                 'balance' => $user->getAccount()->balance,
                 'name' => $user->name,
                 'email' => $user->email,
                 'bd' => $user->getBdUrl(),
             ]);
         } else {
             return response()->json([
                 'access' => false
             ]);
         }
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function getArchive(Request $request)
    {
        /**
         * @var Deal $deal
         */
        $deal = Deal::find($request->route('id'));

        if($deal->getParam('s3.status') === 'loaded') {
            $filePath = env('APP_ENV') . '/printphotobook/' . $deal->id . '/archives/FF/FF.zip';
            $s3 = Storage::disk('s3');

            if ($s3->exists($filePath)) {

                $adapter = $s3->getAdapter();
                $client = $adapter->getClient();
                $client->registerStreamWrapper();
                $object = $client->headObject([
                    'Bucket' => $adapter->getBucket(),
                    'Key' => $filePath,
                ]);

                header('Last-Modified: '.$object['LastModified']);
                header('Accept-Ranges: '.$object['AcceptRanges']);
                header('Content-Length: '.$object['ContentLength']);
                header('Content-Type: '.$object['ContentType']);
                header('Content-Disposition: attachment; filename=' . $deal->id . '_FF.zip');

                if (!($stream = fopen("s3://{$adapter->getBucket()}/{$filePath}", 'r'))) {
                    throw new \Exception('Невозможно открыть поток для чтения файла: ['.$filePath.']');
                }

                while (!feof($stream)) {
                    echo fread($stream, 409600);
                }

                fclose($stream);

                exit;

            } else {
                abort(500);
            }
        }

        $archive = $deal->handler->createLayoutsArchive();

        if (!$archive) {
            abort(500);
        }

        header('Content-type: application/zip');
        header('Content-Length: ' . filesize($archive));
        header('Content-Disposition: attachment; filename=' . $deal->id . '_FF.zip');

        $file = new \SplFileObject($archive, 'r');
        while (!$file->eof()) {
            echo $file->fread(409600);
        }

        File::delete($archive);
        exit;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pay(Request $request)
    {
        /**
         * @var User $user
         */
       $user = User::find($request->user_id);

       if($user) {
           try {
               if($request->minus) {
                   $amount = -((float) ($request->amount));
               } else {
                   $amount = (float) $request->amount;
               }
               /**
                * @var Payment $payment
                */
               // Создаем не подтвержденный платеж
               $payment = $user->getAccount()
                   ->add($amount, Payment::STATUS_CREATED, $request->comment);
               $payment->gateway_id = null;
               $payment->gateway_status = null;
               $payment->is_su = true;

               // подтверждаем платеж
               $payment->confirm();
           } catch (\Exception $e) {
               return response()->json([
                   'access' => true,
                   'error' => [
                       'confirmation' => true
                   ]
               ]);
           }

           if($payment && $payment->isConfirmed()) {
               return response()->json([
                   'access' => true,
                   'error' => false,
                   'balance' => $payment->account->balance
               ]);
           }
       } else {
           return response()->json([
               'access' => true,
               'error' => [
                   'user' => true
               ]
           ]);
       }
    }
}
