<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 25.02.2019
 * Time: 18:17
 */

namespace Tests\traits;

use App\Models\Deal;
use App\Models\DealFile;

trait TestFFTrait
{
    /**
     * @param Deal $deal
     * @param string $bookName
     * @return DealFile
     */
    public function generateCover(Deal $deal, string $bookName = ''): DealFile
    {
        $file = DealFile::create([
            'name' => $bookName ? DealFile::COVER . "_" . $bookName : DealFile::COVER,
            'deal_id' => $deal->id,
            'book' => $bookName ?? null
        ]);

        return $file;
    }

    /**
     * @param Deal $deal
     * @param string $bookName
     * @return DealFile
     */
    public function generateForzac(Deal $deal, string $bookName = ''): DealFile
    {
        $file = DealFile::create([
            'name' => $bookName ? DealFile::FORZAC . "_" . $bookName : DealFile::FORZAC,
            'deal_id' => $deal->id,
            'book' => $bookName ?? null
        ]);

        return $file;
    }

    /**
     * @param Deal $deal
     * @param int $countPages
     * @return array
     */
    public function generatePages(Deal $deal, int $countPages = 1): array
    {
        $files = [];

        for($i = 1; $i <= $countPages; $i++) {
            $file = DealFile::create([
                'name' => $i,
                'deal_id' => $deal->id
            ]);

            $files[] = $file;
        }

        return $files;
    }

    /**
     * @param Deal $deal
     * @param string $bookName
     * @return DealFile
     */
    public function generateNahzac(Deal $deal, string $bookName = ''): DealFile
    {
        $file = DealFile::create([
            'name' => $bookName ? DealFile::NAHZAC . "_" . $bookName : DealFile::NAHZAC,
            'deal_id' => $deal->id,
            'book' => $bookName ?? null
        ]);

        return $file;
    }

    /**
     * @param Deal $deal
     * @param string $bookName
     * @param int $countPages
     * @return array
     */
    public function generateIndividualPages(Deal $deal, string $bookName, $countPages = 1): array
    {
       $files = [];

        for($i = 1; $i <= $countPages; $i++) {
            $file = DealFile::create([
                'name' => "${i}_${bookName}",
                'deal_id' => $deal->id,
                'book' => $bookName
            ]);

            $files[] = $file;
        }

        return $files;
    }

    /**
     * @param Deal $deal
     * @return string
     */
    public function getDealTestPath(Deal $deal)
    {
       $dir = storage_path() . '/photobooks/testing/' . $deal->id;

       if(!is_dir($dir)) {
           mkdir($dir, 0755);
       }

       return $dir;
    }

    /**
     * @param array $arr1
     * @param array $arr2
     * @return bool
     */
    public function assertArrays(array $arr1, array $arr2): bool
    {
        try {
            if(count($arr1) !== count($arr2)) return false;

            foreach($arr1 as $key => $value) {

                if(is_array($value)) {
                    $result = $this->assertArrays($value, $arr2[$key]);

                    if($result) {
                        continue;
                    } else {
                        return false;
                    }
                }

                if ($value !== $arr2[$key]) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            var_dump($e->getTraceAsString()); die;
        }
    }
}
