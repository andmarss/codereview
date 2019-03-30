<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 22.02.2019
 * Time: 14:12
 */

namespace App\DealHandlers\Typografies;


use App\DealHandlers\Helpers\BookLayouts;
use App\DealHandlers\Helpers\DirectoryCleaner;
use App\Models\DealFile;
use Illuminate\Support\Facades\File;

class FF
{
    /**
     * @param BookLayouts|null $layouts
     * @param null $archive
     * @return null
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function createLayoutsArchive(BookLayouts $layouts, $archive)
    {
        if (is_file($archive)) {
            File::delete($archive);
        }

        $files = $this->convertFiles($layouts);

        return $this->createArchive($files, $archive);
    }

    /**
     * @param BookLayouts $layouts
     * @return array
     */
    public function convertFiles(BookLayouts $layouts): array
    {
        /**
         * формат наименования файлов: XX-YYY, где XX - номер разворота (страницы), YYY - номер книги
         * обложка_иванов, обложка_петров, обложка_сидоров => 00-001, 00-002, 00-003 (00 - всегда обложка)
         * форзац_иванов, форзац_петров, форзац_сидоров => 01-001, 01-002, 01-003
         * 1_иванов, 1_петров,1_сидоров => 02-001,02-002,02-003
         * 2,3,4,5 => 03-000,04-000,05-000,06-000
         * нахзац => 07-000
         **/

        // наименование книг
        $books = $layouts->getBookNames();
        // обложки
        $covers = $layouts->getCovers();
        // форзацы
        $forzacs = $layouts->getForzacs();
        // страницы
        $pages = $layouts->getPages();
        // нахзацы
        $nahzacses = $layouts->getNahzacs();

        $convertedFiles = [];
        /**
         * Переменная используется для того, что бы не было дублей
         * у обычных, не индивидуальных страниц
         * @var bool $pagesLoaded
         */
        $pagesLoaded = false;
        $coversLoaded = false;
        $forzacsLoaded = false;
        $nahzacsesLoaded = false;
        $individualPagesLoaded = false;
        // Если количество книг больше нуля, и имя первой книги - не пустая строка
        $condition = count($books) > 0 && $books[0] !== '';

        $format = $condition ? '00-%s' : '00-000';

        $individualPages = array_filter($pages, function (DealFile $page){
            return $page->isIndividual();
        });

        $new_name_format = '';

        if($condition) {
            /**
             * обложка
             */
            foreach ($books as $index => $book) {

                $bookNumber = (string)($index + 1) > 99
                    ? ($index + 1)
                    : ($index + 1) > 9
                        ? '0' . ($index + 1)
                        : '00' . ($index + 1);

                $new_name_format = sprintf($format, $bookNumber);
                // Если есть не индивидуальная обложка
                if (count($covers) > 0 && !$coversLoaded) {
                    foreach ($covers as $i => $cover) {

                        if (count($covers) === ($i + 1)) {
                            $coversLoaded = true;
                        }

                        if ($cover->isIndividual()) continue;

                        $new_name_format = '00-000';

                        $convertedFiles[] = $this->convertPage($cover, $new_name_format, null);
                    }
                }

                $cover = $layouts->getCover($book);
                // Если есть индивидуальная обложка
                if ($cover && $cover->isIndividual()) {
                    if ($this->getBookNumber($new_name_format) === '000') {
                        $new_name_format = '00-' . $bookNumber;
                    }

                    $convertedFiles[] = $this->convertPage($cover, $new_name_format, $book);
                }
            }
            // форзац
            foreach ($books as $index => $book) {

                $bookNumber = (string)($index + 1) > 99
                    ? ($index + 1)
                    : ($index + 1) > 9
                        ? '0' . ($index + 1)
                        : '00' . ($index + 1);
                // Если есть не индивидуальный форзац
                if (count($forzacs) > 0 && !$forzacsLoaded) {
                    foreach ($forzacs as $i => $forzac) {

                        if (count($forzacs) === ($i + 1)) {
                            $forzacsLoaded = true;
                        }

                        if ($this->isIndividual($forzac)) continue;

                        $new_name_format = '01-000';

                        $convertedFiles[] = $this->convertPage($forzac, $new_name_format, null);
                    }
                }

                $forzac = $layouts->getForzac($book);
                // Если есть индивидуальный форзац
                if ($forzac && $this->isIndividual($forzac)) {
                    // если номер страницы - 00, то инкрементируем страницу
                    // иначе - оставляем старый номер страницы
                    $new_name_format = $this->getPageNumber($new_name_format) === '00'
                        ? $this->getNextPageNumber($new_name_format) . '-' . $bookNumber
                        : $this->getPageNumber($new_name_format) . '-' . $bookNumber;

                    $convertedFiles[] = $this->convertPage($forzac, $new_name_format, $book);
                }

            }
            // Индивидуальные страницы
            if (count($individualPages) > 0 && !$individualPagesLoaded) {
                // Инкрементируется номер страницы
                $nextPageNumber = $this->getNextPageNumber($new_name_format);

                foreach ($books as $index => $book) {

                    $bookNumber = (string)($index + 1) > 99
                        ? ($index + 1)
                        : ($index + 1) > 9
                            ? '0' . ($index + 1)
                            : '00' . ($index + 1);

                    foreach ($individualPages as $i => $page) {

                        if ($page->book === $book) {
                            $new_name_format = $nextPageNumber . '-' . $bookNumber;

                            $convertedFiles[] = $this->convertPage($page, $new_name_format, $book);

                            break;
                        }
                    }
                }

            }
            // Страницы
            foreach ($books as $index => $book) {

                if (count($pages) > 0 && !$pagesLoaded) {

                    foreach ($pages as $i => $page) {
                        // После первого итерирования страниц
                        // Запрещаем повторное итерирование во избежание дублей
                        if (count($pages) === ($i + 1)) {
                            $pagesLoaded = true;
                        }

                        if($page->isIndividual()) continue;

                        $pageNumber = $this->getNextPageNumber($new_name_format);

                        $new_name_format = $pageNumber . '-000';

                        $convertedFiles[] = $this->convertPage($page, $new_name_format, null);
                    }
                } else {
                    break;
                }
            }
            // Нахзацы
            foreach ($books as $index => $book) {
                // Если есть не индивидуальный форзац
                if (count($nahzacses) > 0 && !$nahzacsesLoaded) {
                    foreach ($nahzacses as $i => $nahzac) {
                        if (count($nahzacses) === ($i + 1)) {
                            $nahzacsesLoaded = true;
                        }

                        if ($this->isIndividual($nahzac)) continue;

                        $pageNumber = $this->getNextPageNumber($new_name_format);

                        $new_name_format = $pageNumber . '-000';

                        $convertedFiles[] = $this->convertPage($nahzac, $new_name_format, null);
                    }
                } else {
                    break;
                }
            }
            // Инкрементируем номер страницы
            $pageNumber = $this->getNextPageNumber($new_name_format);

            foreach ($books as $index => $book) {

                $bookNumber = (string)($index + 1) > 99
                    ? ($index + 1)
                    : ($index + 1) > 9
                        ? '0' . ($index + 1)
                        : '00' . ($index + 1);

                $nahzac = $layouts->getNahzac($book);
                // Если есть индивидуальный нахзац
                if($nahzac && $this->isIndividual($nahzac)) {

                    $new_name_format = $pageNumber . '-' . $bookNumber;

                    $convertedFiles[] = $this->convertPage($nahzac, $new_name_format, $book);
                }

                continue;
            }
        } else {
            $new_name_format = $format;
            // обложка
            if(count($covers) > 0) {
                foreach ($covers as $cover) {
                    if($cover->isIndividual()) continue;

                    $new_name_format = '00-000';

                    $convertedFiles[] = $this->convertPage($cover, $new_name_format, null);
                }
            }
            // форзац
            if(count($forzacs) > 0) {
                foreach ($forzacs as $forzac) {
                    if($this->isIndividual($forzac)) continue;

                    $new_name_format = '01-000';

                    $convertedFiles[] = $this->convertPage($forzac, $new_name_format, null);
                }
            }
            // страницы
            if(count($pages) > 0) {
                foreach ($pages as $i => $page) {
                    if($page->isPage() && !$page->isIndividual()) {
                        $pageNumber = $this->getNextPageNumber($new_name_format);

                        $new_name_format = $pageNumber . '-000';

                        $convertedFiles[] = $this->convertPage($page, $new_name_format, null);
                    }
                }
            }
            // нахзац
            if(count($nahzacses) > 0) {
                foreach ($nahzacses as $nahzac) {
                    $pageNumber = $this->getNextPageNumber($new_name_format);

                    $new_name_format = $pageNumber . '-000';

                    $convertedFiles[] = $this->convertPage($nahzac, $new_name_format, null);
                }
            }
        }

        return $convertedFiles;
    }

    /**
     * @param array $files
     * @param $archive
     * @return null
     * @throws \InvalidArgumentException
     */
    protected function createArchive(array $files, $archive)
    {
        if(count($files) > 0) {
            foreach ($files as $file) {
                $dir = dirname($file['new_path']);

                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                if(!copy($file['path'], $file['new_path'])) {
                    throw new \Exception("Cannot be copy file ${file['path']} into folder " . dirname($file['new_path']));
                }
            }

            $dir = dirname($files[0]['new_path']);

            exec('cd '. $dir .' && zip -r ' . $archive . ' . ');
            // удаляем временную директорию
            (new DirectoryCleaner($dir))->removeDirectory();

            return is_file($archive) ? $archive : null;
        }

        return null;
    }

    /**
     * @param string $format
     * @return string
     */
    protected function getNextNumberFormat(string $format): string
    {
        return $this->getNextPageNumber($format) . '-' . $this->getBookNumber($format);
    }

    /**
     * @param string $format
     * @return string
     */
    protected function getNextPageNumber(string $format): string
    {
        $regexp = '/(\d{2})/';

        preg_match($regexp, $format, $match);

        $pageNumber = (int) $match[1];
        // Инкрементируем номер книги
        $pageNumber += 1;

        return (string) (($pageNumber) > 9 ? ($pageNumber) : '0' . ($pageNumber));
    }

    /**
     * @param string $format
     * @return string
     */
    protected function getBookNumber(string $format): string
    {
        $regexp = '/(\d{3})/';

        preg_match($regexp, $format, $match);

        return $match[1];
    }

    /**
     * @param string $format
     * @return string
     */
    protected function getPageNumber(string $format): string
    {
        $regexp = '/(\d{2})/';

        preg_match($regexp, $format, $match);

        return $match[1];
    }

    /**
     * @param DealFile $page
     * @param string $format
     * @param $book
     * @return array
     */
    protected function convertPage(DealFile $page, string $format, $book): array
    {
        $file = [];

        if($page) {
            $file = [
                'old_name' => $page->name,
                'new_name' => $format,
                'path' => $page->getPath(),
                'new_path' => str_replace("/files/$page->name", '/tmp/' . $format, $page->getPath()),
                'book' => $book
            ];
        }

        return $file;
    }

    /**
     * Функция используется для проверки индивидуальности страницы
     * В частности, для проверки индивидуальности нахзацев и форзацев
     * @param DealFile $page
     * @return bool
     */
    protected function isIndividual(DealFile $page): bool
    {
        return count(explode('_', $page->name)) > 1;
    }
}
