<?php

namespace App\Http\Controllers;

use App\DataMapper\UserMapper;
use App\Mail\DataChangeCancel;
use App\Mail\DataChangeConfirm;
use App\Mail\RegisterCancel;
use App\Mail\RegisterConfirm;
use App\Specialist;
use App\Traits\PaginateTrait;
use App\Traits\UserFilesTrait;
use App\User;
use App\Workflow\AuthWorkflow;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ModeratorController extends Controller
{
    use PaginateTrait, UserFilesTrait;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function users(Request $request)
    {
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
            $collectUsers = User::whereNotNull('places->confirmed')
                ->where('email', 'like', '%' . $request->email . '%')
                ->orderBy('id', 'desc')
                ->get();
        } else {
            /**
             * @var User[] $collectUsers
             */
            $collectUsers = User::whereNotNull('places->confirmed')
                ->orderBy('id', 'desc')
                ->get();
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

        if($collectUsers->count() > 0) {
            foreach ($collectUsers as $user) {
                $users[] = UserMapper::map($user)->all();
            }
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
    public function user(Request $request)
    {
        /**
         * @var User $user
         */
       $user = User::find($request->route('id'));

       if($user) {
            return response()->json([
                'access' => true,
                'user' => UserMapper::map($user)->all()
            ]);
       } else {
           return response()->json([
               'access' => false
           ]);
       }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transition(Request $request)
    {
        /**
         * @var User $user
         */
        $user = User::find($request->id);

        if($user) {
            /**
             * @var string $transition
             */
            $transition = $request->transition;

            if($user->workflow_can($transition, $request->workflow)) {
                $user->workflow_apply($transition, $request->workflow);

                $user->save();

                $user->setCause('');

                if($transition === AuthWorkflow::TRANSITION_AUTH_MODERATOR_CONFIRM) {
                    Mail::to($user->email)->send(new RegisterConfirm((int) $user->id));
                }

                return response()->json(
                    [
                        'access' => true,
                        'user' => UserMapper::map($user)->all(),
                        'success' => true
                    ]
                );
            }

            return response()->json(
                [
                    'access' => true,
                    'user' => UserMapper::map($user)->all(),
                    'success' => false
                ]
            );
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpecialists(Request $request)
    {
        if(!is_null($request->email)) {
            /**
             * @var Collection $collectUsers
             */
            $collectUsers = User::changedOrDeletedDoctors()
                ->where('email', 'like', '%' . $request->email . '%')
                ->orderBy('id', 'desc')
                ->get();
        } else {
            /**
             * @var Collection $collectUsers
             */
            $collectUsers = User::changedOrDeletedDoctors()
                ->orderBy('id', 'desc')
                ->get();
        }
        /**
         * @var int $total
         */
        $total = $collectUsers->count();
        /**
         * @var Collection $collectUsers
         */
        $collectUsers = $this->paginate($collectUsers, (int) $request->per_page, (int) $request->page);
        /**
         * @var array $users
         */
        $users = [];

        if($collectUsers->count() > 0) {
            foreach ($collectUsers as $user) {
                $users[] = UserMapper::map($user)->all();
            }
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
    public function moderate(Request $request)
    {
        /**
         * @var User $user
         */
        $user = User::find($request->id);

        if($user && $user->isDoctor()) {
            /**
             * @var Specialist $specialist
             */
            $specialist = $user->specialist;

            // если пользователь подал заявку на удаление профиля
            if($user->isWantsToBeDeleted()) {
                if($user->authWorkflowCan(AuthWorkflow::TRANSITION_DELETE)) {
                    $user->authWorkflowApply(AuthWorkflow::TRANSITION_DELETE);
                }

                $user->save();

                /**
                 * @var Collection $reviews
                 */
                $reviews = $specialist->reviews;

                /**
                 * @var Collection $reviews
                 * @var int $total
                 */
                [$reviews, $total] = $this->paginateReviews($reviews, $request);

                /**
                 * @var array $mappedUser
                 */
                $mappedUser = UserMapper::map($user)->all();

                $mappedUser['specialist']['reviews'] = $reviews;

                $user->setCause('');

                Mail::to($user->email)->send(new DataChangeConfirm((int) $user->id));

                return response()->json(
                    [
                        'access' => true,
                        'user' => $mappedUser,
                        'total' => $total,
                        'success' => true
                    ]
                );
            } elseif ($user->wantsToBeRestore()) { // если пользователь подал заявку на восстановление профиля
                $user->restore(false);
            }

            /**
             * @var string $transition
             */
            $transition = $request->transition;

            if($user->workflow_can($transition, $request->workflow)) {

                $user->workflow_apply($transition, $request->workflow);

                $user->save();

                /**
                 * @var Collection $reviews
                 */
                $reviews = $user->specialist->reviews;

                /**
                 * @var Collection $reviews
                 * @var int $total
                 */
                [$reviews, $total] = $this->paginateReviews($reviews, $request);

                /**
                 * @var array $mappedUser
                 */
                $mappedUser = UserMapper::map($user)->all();

                $mappedUser['specialist']['reviews'] = $reviews;

                $user->setCause('');

                Mail::to($user->email)->send(new DataChangeConfirm((int) $user->id));

                return response()->json(
                    [
                        'access' => true,
                        'user' => $mappedUser,
                        'total' => $total,
                        'success' => true
                    ]
                );
            }

            /**
             * @var Collection $reviews
             */
            $reviews = $user->specialist->reviews;

            /**
             * @var Collection $reviews
             * @var int $total
             */
            [$reviews, $total] = $this->paginateReviews($reviews, $request);

            /**
             * @var array $mappedUser
             */
            $mappedUser = UserMapper::map($user)->all();

            $mappedUser['specialist']['reviews'] = $reviews;

            return response()->json(
                [
                    'access' => true,
                    'user' => $mappedUser,
                    'total' => $total,
                    'success' => false,
                    'error' => [
                        'message' => sprintf("Пользователь не может перейти к состоянию \"%s\"", $request->transition)
                    ]
                ]
            );
        } else {
            return response()->json(
                [
                    'access' => true,
                    'success' => false,
                    'error' => [
                        'message' => 'Пользователь не найден, либо пользователь - не доктор'
                    ]
                ]
            );
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request)
    {
        /**
         * @var User $user
         */
        $user = User::find($request->route('id'));
        /**
         * @var string $transition
         */
        $transition = $request->transition;

        if($user && $user->authWorkflowCan($transition)) {
            $user->authWorkflowApply($transition);

            $user->setCause($request->cause);

            $user->save();

            switch ($transition) {
                case AuthWorkflow::TRANSITION_AUTH_BACK_TO_REGISTER:

                    Mail::to($user->email)->send(new RegisterCancel((int) $user->id, (string) $request->cause));

                    break;
                case AuthWorkflow::TRANSITION_AUTH_BACK_TO_CHANGE:

                    Mail::to($user->email)->send(new DataChangeCancel((int) $user->id, (string) $request->cause));

                    break;
            }

            return response()->json([
                'access' => true,
                'success' => true,
                'user' => UserMapper::map($user)
            ]);
        } else {
            return response()->json([
                'access' => true,
                'success' => false,
                'error' => [
                    'message' => 'Пользователь не найден, либо он не может быть возвращен к восстановлению. Операция не удалась'
                ]
            ]);
        }
    }
}
