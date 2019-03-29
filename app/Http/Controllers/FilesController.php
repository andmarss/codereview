<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    /**
     * Получение файла с макетом
     *
     */
    public function file(Request $request)
    {
        /**
         * @var Deal $deal
         * Http/Middleware/CheckDealUserMiddleware.php:25
         */
        $deal = $request->attributes->get('deal');
        /**
         * @var DealFile $file
         */
        $file = DealFile::query()
            ->where('deal_id', $deal->id)
            ->where('id', $request->file_id)
            ->first();
        // если файла нет - возвращаем заглушку
        if (!$file) {
            return response()->file(DealFile::getNotFoundThumbPath());
        }
        // иначе - возвращаем файл
        return response()->file($file->getThumb());
    }

}
