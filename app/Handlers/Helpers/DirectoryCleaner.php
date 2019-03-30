<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 18.02.2019
 * Time: 14:30
 */

namespace App\DealHandlers\Helpers;


class DirectoryCleaner
{
    /**
     * @var string $directory
     */
    protected $directory;

    /**
     * DirectoryCleaner constructor.
     * @param string $directory
     */
    public function __construct(string $directory = '')
    {
        $this->directory = $directory;
    }

    /**
     * Устанавливает рабочую директорию, из которой будут браться файлы и папки
     * И в которой будет происходить основная работа
     * @param string $directory
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function directory(string $directory)
    {   // если передана не папка
        // проверяем, возможно передано имя внутренней папки ->directory('innerDirectory')
        if (!is_dir($directory)) {
            // Если родительская папка установлена
            // пытаемся работать с ней
            if($this->directory){
                $oldname = $directory;
                $directory = $this->directory . '/' . $directory;
                // Если по прежнему ничего не получилось - выбрасываем исключение
                if(!is_dir($directory)) {
                    throw new \InvalidArgumentException("$oldname must be a directory");
                }
            } else { // если нет - выбрасываем исключение
                throw new \InvalidArgumentException("$directory must be a directory");
            }
        }

        if (substr($directory, strlen($directory) - 1, 1) !== '/') {
            $directory .= '/';
        }

        $this->directory = $directory;

        return $this;
    }

    /**
     * Функция удаляет все внутренние файлы и папки
     * Если передан параметр $directoriesOrFiles,
     * то внутри родительской папки ищет все совпадения с переданной маской (или просто строкой)
     * и удаляет их
     * Если параметр не передан - рекурсивно очищает родительскую папку от всех файлов и папок
     *
     * @param array $directoriesOrFiles
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function removeAll(array $directoriesOrFiles = [])
    {
       if(count($directoriesOrFiles) > 0) {
           foreach ($directoriesOrFiles as $directoryOrFile){
               $files = glob($this->directory . "/${directoryOrFile}");

               if($files) {
                   foreach ($files as $file) {
                       if(is_file($file)) {
                           $this->removeFile($file);
                       } elseif (is_dir($file)) {
                           $this->removeDirectory($file);
                       }
                   }
               }
           }
       } else {
           $files = glob($this->directory . '/*');

           if($files) {
               foreach ($files as $file) {
                   if(is_file($file)) {
                       $this->removeFile($file);
                   } elseif (is_dir($file)) {
                       $this->removeDirectory($file);
                   }
               }
           }
       }

       return $this;
    }

    /**
     * Работает как и removeAll, но удоляет только файлы, папки не трогает
     *
     * @param null $files
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function removeFiles($files = null)
    {
       if(!is_null($files) && is_string($files)) {
           $files = glob($this->directory . "/${files}");

           if($files) {
               foreach ($files as $file) {
                   if(is_file($file)) {
                       $this->removeFile($file);
                   } else {
                       continue;
                   }
               }
           }
       } elseif (!is_null($files) && is_array($files) && count($files) > 0) {
           foreach ($files as $file){
               $matchedFiles = glob($this->directory . "/${$file}");

               if($matchedFiles) {
                   foreach ($matchedFiles as $matchedFile) {
                       if(is_file($matchedFile)) {
                           $this->removeFile($matchedFile);
                       } else {
                           continue;
                       }
                   }
               }
           }
       } else {
           $files = glob($this->directory . "/*");

           if($files) {
               foreach ($files as $file) {
                   if(is_file($file)) {
                       $this->removeFile($file);
                   } else {
                       continue;
                   }
               }
           }
       }

       return $this;
    }

    /**
     * Функция удаляет файл, строковый путь к которому передан через параметр
     * Если путь ведет не к файлу - выбрасывает исключение
     *
     * @param string $file
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function removeFile(string $file)
    {
       if(is_file($file)) {
           unlink($file);
       } else {
           throw new \InvalidArgumentException("$file must be a file");
       }

       return $this;
    }

    /**
     * Рекурсивно очищает все папки внутри переданной директории, после чего удаляет саму директорию
     * Если параметр $directory не передан - работает с $this->directory
     * В противном случае - выбрасывает исключение
     *
     * @param string $directory
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function removeDirectory(string $directory = '')
    {
       if($directory) {
           $oldname = '';
           // может быть передано имя внутренней папки ->removeDirectory('inner')
           if(!is_dir($directory)) {
               $oldname = $directory;
               $directory = $this->directory . '/' . $directory;

               // Если даже после этого $directory - не папка, то выбрасываем исключение
               if(!is_dir($directory)) {
                   throw new \InvalidArgumentException("$oldname must be a directory");
               }
           }

           exec('rm -d -r ' . $directory);
       } else {

           if($this->directory) {
               exec('rm -d -r ' . $directory);
           } else {
               throw new \InvalidArgumentException("$directory must be a directory");
           }

       }

       return $this;
    }

    /**
     * Принимает необязательный параметр $directories, который может быть либо массивом строк, либо строкой
     * Если параметр - массив, то ищет в родительской все совпадения с переданными именами папок, и удаляет их
     * Если параметр - строка, то ищет в родительской папке все совпадения с переданным именем папки, и удаляет их
     * Если параметр не передан - очищает родительскую ридекторию от всех файлов и папок
     *
     * @param null $directories
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function removeDirectories($directories = null)
    {
        if(!is_null($directories) && is_array($directories) && count($directories) > 0) {

            foreach ($directories as $directory) {

                $directories = glob($this->directory . "/${directory}", GLOB_ONLYDIR);

                $this->remove_directories($directories);
            }

        } elseif (!is_null($directories) && is_string($directories) && $directories !== '') {

            $directories = glob($this->directory . "/${$directories}", GLOB_ONLYDIR);

            $this->remove_directories($directories);

        } else {

            $directories = glob($this->directory . "/*", GLOB_ONLYDIR);

            $this->remove_directories($directories);

        }

        return $this;
    }

    /**
     * Работает по принципу removeDirectory, но в конце не удаляет рабочую папку
     *
     * @param string $directory
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function clearDirectory(string $directory = '')
    {
        if($directory) {
            // может быть передано имя внутренней папки ->removeDirectory('inner')
            if (!is_dir($directory)) {

                $directory = $this->directory . '/' . $directory;
                // Если даже после этого $directory - не папка, то выбрасываем исключение
                if(!is_dir($directory)) {
                    throw new \InvalidArgumentException("$directory must be a directory");
                }
            }

            if (substr($directory, strlen($directory) - 1, 1) !== '/') {
                $directory .= '/';
            }

            $files = glob($directory . '/*');

            if($files) {
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        $this->removeDirectory($file);
                    } elseif(is_file($file)) {
                        unlink($file);
                    }
                }
            }
        } else {
            if (!is_dir($this->directory)) {
                throw new \InvalidArgumentException("$directory must be a directory");
            }

            $files = glob($this->directory . '/*');

            if($files) {
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        $this->removeDirectory($file);
                    } elseif(is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Работает по принципу removeDirectories, но не удаляет директории - а только очищает их от всех файлов
     *
     * @param null $directories
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function clearDirectories($directories = null)
    {
        if(!is_null($directories) && is_array($directories) && count($directories) > 0) {

            foreach ($directories as $directory) {

                $directories = glob($this->directory . "/${directory}", GLOB_ONLYDIR);

                $this->clear_directories($directories);
            }
        } elseif (!is_null($directories) && is_string($directories) && $directories !== '') {
            $directories = glob($this->directory . "/${$directories}", GLOB_ONLYDIR);

            $this->clear_directories($directories);
        } else {
            $directories = glob($this->directory . "/*", GLOB_ONLYDIR);

            $this->clear_directories($directories);
        }

        return $this;
    }

    /**
     * Получает массив папок
     * Перебирает каждую - и удаляет их
     *
     * @param array $directories
     * @throws \InvalidArgumentException
     */
    protected function remove_directories(array $directories)
    {
        if(count($directories) > 0) {
            foreach ($directories as $directory) {

                if (is_dir($directory)) {
                    $this->removeDirectory($directory);
                }

            }
        }
    }

    /**
     * Получает массив папок
     * Перебирает каждую - и очищает их от файлов
     *
     * @param array $directories
     * @throws \InvalidArgumentException
     */
    protected function clear_directories(array $directories)
    {
        if(count($directories) > 0) {
            foreach ($directories as $directory) {

                if (is_dir($directory)) {
                    $this->clearDirectory($directory);
                }

            }
        }
    }
}
