<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03.04.2019
 * Time: 16:36
 */

namespace App\Traits;

/**
 * Trait PluralizeTrait
 * @package App\Traits
 */
trait PluralizeTrait
{
    /**
     * получить единственную или множественную форму по числу
     * @param int $number
     * @return int
     */
    protected function getPlural(int $number): int
    {
        if($number % 10 == 1 && $number % 100 != 11) return 0;

        return $number % 10 >= 2 && $number % 10 <= 4 && ( $number % 100 < 10 || $number % 100 >= 20 ) ? 1 : 2;
    }
    /**
     * Выбирает из массива, состоящего из трех строчных элементов
     * ту строчку, которая подходит под одиночную или множественную форму числа
     * @param int $number
     * @param array $words
     * @return string
     */
    protected function pluralize(int $number, array $words): string
    {
        return (string) $words[$this->getPlural($number)];
    }
}
