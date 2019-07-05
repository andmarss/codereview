<?php

namespace App\Http\Controllers;

use App\DataMapper\UserMapper;
use App\Specialist;
use App\SpecialistReview;
use App\Traits\UserFilesTrait;
use App\Traits\PaginateTrait;
use App\User;
use App\UserFile;
use App\Workflow\AuthWorkflow;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class PersonalAccountController extends Controller
{
    use PaginateTrait, UserFilesTrait;

    /**
     * @return JsonResponse
     */
    public function getData(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = auth()->user();

        return response()->json(
            ['user' => UserMapper::map($user)->all()]
        );
    }

    public function transition(Request $request)
    {
        /**
         * @var string $transition
         */
        $transition = $request->transition;
        /**
         * @var User $user
         */
        $user = auth()->user();

        if($user->workflow_can($transition, $request->workflow)) {
            $user->workflow_apply($transition, $request->workflow);

            $user->save();

            return response()->json(
                [
                    'user' => UserMapper::map($user)->all(),
                    'success' => true
                ]
            );
        }

        return response()->json(
            [
                'user' => UserMapper::map($user)->all(),
                'success' => false
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function specialist(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $request->attributes->get('user');
        /**
         * @var User $authorized
         */
        $authorized = auth()->user();
        /**
         * @var Collection $reviews
         * @var int $total
         */
        [$reviews, $total] = $this->paginateReviews($user->specialist->reviews, $request);

        /**
         * @var array $mappedUser
         */
        $mappedUser = UserMapper::map($user)->all();

        $mappedUser['specialist']['reviews'] = $reviews;

        return response()->json(
            array_merge([
                'access' => true,
                'user' => $mappedUser,
                'total' => $total
            ], $authorized->isModerator() ? ['is_m' => true] : [])
        );
    }

    /**
     * Получить список отзывов специалиста
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reviews(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $request->attributes->get('user');
        /**
         * @var Specialist $specialist
         */
        $specialist = $user->specialist;
        /**
         * @var Collection $reviews
         * @var int $total
         */
        [$reviews, $total] = $this->paginateReviews($specialist->reviews, $request);

        return response()->json(
            [
                'access' => true,
                'total' => $total,
                'reviews' => $reviews->all()
            ]
        );
    }

    /**
     * ответ на отзыв
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function review(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $request->attributes->get('user');
        /**
         * @var Specialist $specialist
         */
        $specialist = $user->specialist;
        /**
         * @var HasMany $reviews
         */
        $reviews = $specialist->reviews;
        /**
         * @var SpecialistReview $review
         */
        $review = $reviews->where('id', $request->route('review_id'))->first();

        if($review && $review->specialist_id === $specialist->id) {
            $review->answer_text = $request->answer;
            $review->answered_at = (new \DateTime())->format('Y-m-d H:i:s');
            $review->save();
            /**
             * @var Collection $reviews
             * @var int $total
             */
            [$reviews, $total] = $this->paginateReviews($specialist->reviews, $request);

            return response()->json(
                [
                    'access' => true,
                    'success' => true,
                    'error' => false,
                    'total' => $total,
                    'reviews' => $reviews->all()
                ]
            );
        } else {
            return response()->json(
                [
                    'access' => true,
                    'success' => false,
                    'error' => [
                        'message' => 'Отзыв не найден. Ответ не был добавлен'
                    ]
                ]
            );
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function moderation(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $request->attributes->get('user');

        if($user->authWorkflowCan(AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE)) {
            /**
             * @var Specialist $specialist
             */
            $specialist = $user->specialist;

            // если пользователь внес все изменения - он допускается к модерации
            if($user->allChanged() && $user->authWorkflowCan(AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE)) {
                $user->authWorkflowApply(AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE);
            } else {
                return response()->json([
                    'access' => true,
                    'success' => false,
                    'error' => [
                        'message' => "Необходимо внести изменения для отправки на модерацию.\n 
                        Заполните имя, фамилию, отчество, описание, добавьте фото.\n
                        Отправка на модерацию не выполнена"
                    ]
                ]);
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

            return response()->json([
                'access' => true,
                'success' => true,
                'error' => false,
                'user' => $mappedUser,
                'total' => $total
            ]);

        } else {
            return response()->json([
                'access' => true,
                'success' => false,
                'error' => [
                    'message' => "Ваши предыдущие изменения ждут подтверждения модератора.\n
                    Дождитесь подтверждения модератором предыдущих изменений прежде, чем добавлять новые"
                ]
            ]);
        }
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadSpecialistPhoto(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $request->attributes->get('user');

        if($user && $user->isDoctor()) {
            /**
             * @var Specialist $specialist
             */
            $specialist = $user->specialist;

            if(!is_null($specialist->image) && $request->file('image')) {
                /**
                 * @var UserFile $image
                 */
                $image = $specialist->image();

                if($image) {
                    /**
                     * @var bool $success
                     */
                    $success = $this->removeUserFile($image, $user);

                    if(!$success) {
                        return response()->json([
                            'access' => true,
                            'success' => false,
                            'error' => [
                                'message' => 'Произошла ошибка при загрузке файла. Файл не был загружен'
                            ]
                        ]);
                    }
                }

                $specialist->image = null;
            }

            if(is_null($specialist->image) && $request->file('image')) {
                // проверяем переданный файл
                $this->validateRequestFile($request, [
                    'image' => 'image|mimes:jpeg|max:5120'
                ]);
                /**
                 * загружаем файл
                 * @var UserFile $file
                 */
                $file = $this->addUserFile($request->file('image'), $user, true);

                $user->changed('image', true);

                if($file) {
                    $specialist->image = (string) $file->path . $file->name;

                    $specialist->save();

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
                            'message' => 'Произошла ошибка при загрузке файла. Файл не был загружен'
                        ]
                    ]);
                }
            }
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function action(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $request->attributes->get('user');

        if(!is_null($request->get('delete'))
            && $request->get('delete')
        ) {
            $user->specialist->is_deletion_requested = 1;

            $user->specialist->save();

            $user->restore(false);

            $user->save();

            return response()->json([
                'access' => true,
                'success' => true,
                'user' => UserMapper::map($user),
                'restore' => $user->getParam('restore')
            ]);
        } elseif (!is_null($request->get('delete'))
            && !$request->get('delete')) {

            $user->restore(true);

            $user->specialist->is_deletion_requested = 0;

            $user->specialist->save();

            $user->save();

            return response()->json([
                'access' => true,
                'success' => true,
                'user' => UserMapper::map($user)
            ]);

        }

        return response()->json([
            'access' => true,
            'success' => false,
            'error' => [
                'message' => 'Действия с профилем недоступны'
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $request->attributes->get('user');
        /**
         * @var array $changed
         */
        $changed = $user->changed();
        /**
         * @var Specialist $specialist
         */
        $specialist = $user->specialist;

        if(trim($specialist->firstname) !== trim($request->firstname)) {
            $specialist->firstname = trim($request->firstname);

            $changed['firstname'] = true;
        }

        if(trim($specialist->secondname) !== trim($request->secondname)) {
            $specialist->secondname = trim($request->secondname);

            $changed['secondname'] = true;
        }

        if(trim($specialist->lastname) !== trim($request->lastname)) {
            $specialist->lastname = trim($request->lastname);

            $changed['lastname'] = true;
        }

        if(trim($specialist->description) !== trim($request->description)) {
            $specialist->description = trim($request->description);

            $changed['description'] = true;
        }

        try {
            $specialist->save();

            $user->changed($changed);

            return response()->json([
                'access' => true,
                'success' => true,
                'user' => UserMapper::map($user)
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'access' => true,
                'success' => false,
                'error' => [
                    'message' => 'Произошла ошибка при сохранении изменений'
                ]
            ]);
        }
    }
}
