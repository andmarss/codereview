<?php

namespace App\Http\Controllers;

use App\DataMapper\UserMapper;
use App\Traits\UserFilesTrait;
use App\User;
use App\UserFile;
use App\Workflow\AuthWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilesController extends Controller
{
    use UserFilesTrait;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function uploadUserFile(Request $request): JsonResponse
    {
        /**
         * @var array $rules
         */
        $rules = [
            'file' => 'image|mimes:jpeg|max:5120'
        ];
        // проверяем переданный файл согласно условиям из $rules
        $this->validateRequestFile($request, $rules);
        /**
         * @var User $user
         */
        $user = User::find($request->route('id'));

        if($user) {
            // добавляем в user_files загруженный файл
            $this->addUserFile($request->file('file'), $user);

            return response()->json(
                [
                    'access' => true,
                    'success' => true,
                    'user' => UserMapper::map($user)->all()
                ]
            );
        } else {
            return response()->json([
                'access' => true,
                'success' => false,
                'message' => 'Пользователь не найден'
            ]);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function file(Request $request)
    {
        /**
         * @var User $user
         */
        $user = User::find($request->route('user_id'));
        /**
         * @var UserFile $file
         */
       $file = UserFile::find($request->route('id'));

       if($user && $file && $file->user->id === $user->id) {
           return response()->file($file->path . $file->name);
       }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function removeFile(Request $request)
    {
        /**
         * @var User $user
         */
        $user = User::find($request->route('user_id'));
        /**
         * @var UserFile $file
         */
        $file = UserFile::find($request->route('id'));
        /**
         * @var bool $success
         */
        $success = $this->removeUserFile($file, $user);

        if($success) {
            // если документов пользователя больше нет
            // возвращаем его к состоянию регистрации
            if($user->files()->where(['is_avatar' => 0])->count() === 0
                && $user->workflow_can(
                AuthWorkflow::TRANSITION_AUTH_REGISTER,
                AuthWorkflow::WORKFLOW_NAME)) {

                $user->workflow_apply(
                    AuthWorkflow::TRANSITION_AUTH_REGISTER,
                    AuthWorkflow::WORKFLOW_NAME);

                $user->save();
            }

            return response()->json(
                [
                    'access' => true,
                    'success' => true,
                    'user' => UserMapper::map($user)->all()
                ]
            );
        } else {
            return response()->json([
                'success' => false
            ]);
        }
    }
}
