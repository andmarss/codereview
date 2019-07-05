<?php

namespace Tests\Reset;


use Tests\TestCase;

/**
 * Тестирование модели для клиента
 *
 * Class ResetTest
 */
class ResetTest extends TestCase
{
    private $database;

    public function setUp() {
        parent::setUp();

        $this->database = $this->app->make('db');
        // todo not env
        $connection = config('database.default');
        $this->truncateDb(config('database.connections.'.$connection.'.database'));
    }

    public function testResetDb() {
        echo  PHP_EOL . "--- накатили все миграции".PHP_EOL.PHP_EOL;
        $this->assertEquals(0, $this->artisan('migrate'));
    }

    /**
     * Очищает базу данных
     *
     * @param string $dbName
     *
     * @return int
     */
    private function truncateDb($dbName)
    {
        $colname = 'Tables_in_' . $dbName;

        $tables = $this->database->select('SHOW TABLES');
        $droplist = [];

        foreach($tables as $table) {
            $droplist[] = $table->$colname;
        }

        if ($droplist) {
            $this->database->statement('SET FOREIGN_KEY_CHECKS=0');
            echo PHP_EOL . PHP_EOL . "--- удалили ".count($droplist)." таблиц в " . $dbName;
            $droplist = implode(',', $droplist);
            $this->database->statement("DROP TABLE $droplist");
            $this->database->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return true;
    }
}