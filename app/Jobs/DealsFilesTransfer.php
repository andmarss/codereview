<?php

namespace App\Jobs;

use App\DealHandlers\Helpers\DirectoryCleaner;
use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DealsFilesTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Deal[] $deals
     */
    protected $deals;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->deals = Deal::paramsNotContains('s3.status')->get();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if($this->deals->count() > 0) {
            /**
             * @var Deal[] $deals
             */
            $deals = $this->deals;

            $s3_disk = Storage::disk('s3');

            foreach ($deals as &$deal) {
                $status = $deal->getParam('s3.status');

                if($status === 'loaded') continue;

                try {
                    // устанавливаем статус loading
                    $deal->setParam('s3.status', 'loading');
                    $deal->save();
                    // директория, в которую будут загружаться файлы и архивы на s3
                    $directory = env('APP_ENV') . '/printphotobook/' . $deal->id;
                    // Если директория есть - очищаем её
                    if($s3_disk->exists($directory)) {
                        $s3_disk->deleteDirectory($directory);
                    }

                    $s3_disk->makeDirectory($directory);

                    // локальная папка сделки
                    $local_directory = storage_path() . "/photobooks/" . env('APP_ENV') . "/" . $deal->id;

                    $directory_cleaner = new DirectoryCleaner($local_directory);

                    if(is_dir($local_directory . '/files')) {
                        // файлы
                        $files = glob($local_directory . '/files/*.jpg');
                        // директория, куда будут загружаться макеты на s3
                        $s3_files_directory = $directory . '/layouts';
                        // создаём её
                        $s3_disk->makeDirectory($s3_files_directory);

                        if(count($files) > 0) {
                            foreach ($files as $file) {
                                // получаем имя файла
                                $file_name = basename($file);
                                // передаём в s3
                                $s3_disk->put($s3_files_directory . '/' . $file_name, file_get_contents($file));
                            }
                        }
                    }

                    // директория, куда будет перенесен архив (s3)
                    $s3_archives_directory = $directory . '/archives';
                    // создаём директорию для архивов
                    $s3_disk->makeDirectory($s3_archives_directory);
                    // и для FF формата
                    $s3_disk->makeDirectory($s3_archives_directory . '/FF');
                    // Получаем архив
                    $archive = $deal->handler->createLayoutsArchive();
                    // Если архив вернулся - передаем его на s3
                    if(is_file($archive)) {
                        $s3_disk->put($s3_archives_directory . '/FF/FF.zip', file_get_contents($archive));
                    }
                    // устанавливает статус, как loaded
                    $deal->setParam('s3.status', 'loaded');
                    // устанавливаем дату загрузки файлов
                    $deal->setParam('s3.loaded_at', date('d.m.Y'));
                    $deal->save();
                    // если папка архива есть - удаляем её
                    if(is_dir($local_directory . '/archive')) {
                        $directory_cleaner->removeDirectory('archive');
                    }
                    // если папка с файлами есть - удаляем её
                    if(is_dir($local_directory . '/files')) {
                        $directory_cleaner->removeDirectory('files');
                    }
                    // если папка tmp есть - так же её удаляем
                    if(is_dir($local_directory . '/tmp')) {
                        $directory_cleaner->removeDirectory('tmp');
                    }
                    // если есть папка finally - удаляем её
                    if(is_dir($local_directory . '/finally')) {
                        $directory_cleaner->removeDirectory('finally');
                    }

                } catch (\Exception $e) {
                    // в случае любой ошибки - удаляем статус
                    $deal->forgetParam('s3.status');
                    $deal->save();

                    Log::error('При переносе файлов сделки №' . $deal->id . ' на s3 произошла ошибка: ' . $e->getMessage() . ' ' .date('d.m.Y H:i'));
                }
            }
        }

        return;
    }
}
