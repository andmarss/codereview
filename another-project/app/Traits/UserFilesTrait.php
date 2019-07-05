<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08.04.2019
 * Time: 17:02
 */

namespace App\Traits;

use App\User;
use App\UserFile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;


trait UserFilesTrait
{
    /**
     * @param UploadedFile $file
     * @param User $user
     * @param bool $is_avatar
     * @return UserFile|null
     */
    public function addUserFile(UploadedFile $file, User $user, bool $is_avatar = false)
    {
        /**
         * @var string $file_new_name
         */
        $file_new_name = time() . $file->getClientOriginalName();
        /**
         * @var string $path
         */
        $path = UserFile::getPath($user->id);
        /**
         * @var string $type
         */
        $type = $file->getClientOriginalExtension();

        try {
            $file->move(public_path() . $path, $file_new_name);

            return UserFile::create([
                'name' => $file_new_name,
                'old_name' => $file->getClientOriginalName(),
                'path' => $path . '/',
                'user_id' => $user->id,
                'type' => $type,
                'status' => UserFile::STATUS_UPLOADED,
                'is_avatar' => $is_avatar
            ]);
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param Request $request
     * @param array $rules
     * @param array $config
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function validateRequestFile(Request $request, array $rules, array $config = [
        'upload_max_filesize' => '5M',
        'max_execution_time' => 1000,
        'post_max_size' => '5M'
    ])
    {
        // устанавливаем настройки для загружаемых файлов
        if(count($config) > 0) {
            collect($config)->each(function ($value, string $key){
                ini_set($key, $value);
            });
        }

        /**
         * @var Validator $validator
         */
        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json([
                'access' => true,
                'success' => false,
                'error' => [
                    'message' => 'Произошла ошибка при загрузке файла'
                ],
                'errors' => $validator->errors()
            ]);
        }
    }

    /**
     * @param UserFile $file
     * @param User $user
     * @return bool
     */
    public function removeUserFile(UserFile $file, User $user): bool
    {
        if($user && $file && $file->user->id === $user->id) {
            // удаляем файл локально
            if(is_file(public_path() . $file->path . $file->name)) {
                unlink(public_path() . $file->path . $file->name);
            }

            try {
                $file->delete();
            } catch (\Exception $exception) {
                return false;
            }

            return true;
        }

        return false;
    }
}
