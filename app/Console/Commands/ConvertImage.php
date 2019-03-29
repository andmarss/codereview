<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \Illuminate\Support\Facades\File;
use Image;

class ConvertImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:convert {from=png} {to=jpg} {path=/img}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert image from one encoding to another';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Получаем список аргументов
        $arguments = array_slice($this->arguments(), 1);
        // Из какого формата будет происходить конвертация, п/у png
        $from = $arguments['from'];
        // В какой формат будет происходить конвертация, п/у jpg
        $to = $arguments['to'];
        // Откуда будут браться файлы, п/у /img
        $path = $arguments['path'];
        // Полный путь к папке
        $path = public_path($path);
        // Массив имен файлов, которые нужно переконвертировать
        $files = File::glob("${path}/*.${from}");

        foreach ($files as $file) {
            // Объект изображения
            $image = Image::make($file);
            // Оригинальное имя файла, без расширения
            $filename = pathinfo($file, PATHINFO_FILENAME);
            // Выоста изображения
            $height = $image->height();
            // Ширина изображения
            $width = $image->width();
            // если длина больше 300 px и при этом меньше высоты
            // значит длина - минимальная сторона изображения
            if($width > 300 && $width < $height) {
                $width = 300;

                // Устанавливаем размеры изображения с сохранением пропорций
                $image->resize($width, null, function ($constraint){
                    $constraint->aspectRatio();
                });
            } elseif ($height > 300 && $height < $width) { // иначе, высота - минимальная сторона изображения
                $height = 300;

                $image->resize(null, $height, function ($constraint){
                    $constraint->aspectRatio();
                });
            } elseif ($height > 300 && $height === $width) {
                // Если высота и ширина - одинакового размера
                // Ужимаем любую из сторон до 300px
                $height = 300;

                $image->resize(null, $height, function ($constraint){
                    $constraint->aspectRatio();
                });
            }
            // Удаляем старый файл
            File::delete($file);
            // Сохраняем новое изображение с форматом, переданным аргументом to
            $image->save("${path}/${filename}.${to}", 60);
        }

        echo "All files from storage \"$path\" successfully converted from \".$from\" to \".$to\", and files with extension \".$from\" have been deleted";
    }
}
