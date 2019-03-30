<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 25.02.2019
 * Time: 18:08
 */

namespace Tests\Unit\DealHandlers\Typografies;

use App\DealHandlers\Helpers\BookLayouts;
use App\DealHandlers\Typografies\FF;
use App\Models\DealFile;
use Tests\TestCase;
use Tests\traits\TestDealTrait;
use Tests\traits\TestFFTrait;

class FFTest extends TestCase
{
    /**
     * @param $srcElements
     * @param $expectedElements
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @dataProvider formatFilesDataProvider
     */
    public function testFormatFiles($srcElements, $expectedElements)
    {
        $files = [];
        foreach ($srcElements as $name) {
            $files[] = DealFile::createEmptyLayout(1, explode('_', $name)[0], DealFile::getIndividualBook($name));
        }

        $layouts = new BookLayouts($files);

        $ff = new FF();

        $convertedFiles = $ff->convertFiles($layouts);

        foreach ($convertedFiles as $i => $converted) {
            $this->assertEquals($expectedElements[$i], $converted['new_name']);
        }

    }


    public function formatFilesDataProvider()
    {
        return [
            [
                [DealFile::COVER, 1],
                ['00-000', '01-000']
            ],
            [
                [DealFile::COVER, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                ['00-000', '01-000', '02-000', '03-000', '04-000', '05-000', '06-000', '07-000', '08-000', '09-000', '10-000', '11-000']
            ],
            [
                [DealFile::COVER, DealFile::FORZAC, 1, 2, DealFile::NAHZAC],
                ['00-000', '01-000', '02-000', '03-000', '04-000']
            ],
            [
                [DealFile::COVER, '1_иванов', '1_петров', '1_сидоров' , 2, 3, 4, DealFile::NAHZAC],
                ['00-000', '01-001', '01-002', '01-003', '02-000', '03-000', '04-000', '05-000'],
            ],
            [
                [DealFile::COVER . "_иванов", DealFile::COVER . "_петров", DealFile::COVER . "_сидоров", '1_иванов', '1_петров', '1_сидоров' , 2, 3, 4],
                ['00-001', '00-002','00-003',  '01-001', '01-002', '01-003', '02-000', '03-000', '04-000',],
            ],
            [
                [
                    DealFile::COVER . "_иванов", DealFile::COVER . "_петров", DealFile::COVER . "_сидоров",
                    DealFile::FORZAC,
                    '1_иванов', '1_петров', '1_сидоров' , 2, 3, 4,
                    DealFile::NAHZAC,
                ],
                ['00-001', '00-002','00-003', '01-000', '02-001', '02-002', '02-003', '03-000', '04-000', '05-000', '06-000'],
            ],
            [
                [
                    DealFile::COVER . "_иванов", DealFile::COVER . "_петров", DealFile::COVER . "_сидоров",
                    DealFile::FORZAC . "_иванов", DealFile::FORZAC . "_петров", DealFile::FORZAC . "_сидоров",
                    '1_иванов', '1_петров', '1_сидоров' , 2, 3, 4,
                    DealFile::NAHZAC,
                ],
                [
                    '00-001', '00-002','00-003',
                    '01-001', '01-002', '01-003',
                    '02-001', '02-002', '02-003',
                    '03-000', '04-000', '05-000', '06-000'
                ],
            ],
            [
                [
                    DealFile::COVER . "_иванов", DealFile::COVER . "_петров", DealFile::COVER . "_сидоров",
                    DealFile::FORZAC . "_иванов", DealFile::FORZAC . "_петров", DealFile::FORZAC . "_сидоров",
                    '1_иванов', '1_петров', '1_сидоров' , 2, 3, 4, 5, 6, 7, 8,
                    DealFile::NAHZAC . "_иванов", DealFile::NAHZAC . "_петров", DealFile::NAHZAC . "_сидоров",
                ],
                [
                    '00-001', '00-002', '00-003',
                    '01-001', '01-002', '01-003',
                    '02-001', '02-002', '02-003',
                    '03-000', '04-000', '05-000', '06-000', '07-000', '08-000', '09-000',
                    '10-001', '10-002', '10-003'
                ],
            ],
            [
                [DealFile::COVER, DealFile::FORZAC, '1_иванов', '1_петров', '1_сидоров', '1_иваненко', '1_петренко', '1_сидоренко', DealFile::NAHZAC],
                ['00-000', '01-000', '02-001', '02-002', '02-003', '02-004', '02-005', '02-006', '03-000']
            ],
            [
                [DealFile::COVER, DealFile::FORZAC, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, DealFile::NAHZAC],
                [
                    '00-000',
                    '01-000',
                    '02-000', '03-000', '04-000', '05-000', '06-000', '07-000', '08-000', '09-000', '10-000', '11-000', '12-000', '13-000',
                    '14-000'
                ]
            ]
        ];
    }

}
