<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use CaT\Plugins\ParticipationsImport\Data\SpoutXLSXExtractor;
use CaT\Plugins\ParticipationsImport\Data\DataExtractor;

class SpoutXLSXExtractorTest extends TestCase
{
    public function test_init()
    {
        $ex = new SpoutXLSXExtractor();
        $this->assertInstanceOf(DataExtractor::class, $ex);
        return $ex;
    }

    /**
     * @depends test_init
     */
    public function test_extract_sheet_1($ex)
    {
        $data = $ex->extractContent(
            __DIR__ . '/../Filesystem/Fixtures/xlsx_test_file.xlsx',
            ['A1' => 'AA1','B1' => 'BB1','C1' => 'CC1']
        );
        $row_cnt = 1;
        while ($row = array_shift($data)) {
            $this->assertEquals(
                $row,
                ['AA1' => 'a1' . $row_cnt,'BB1' => 'b1' . $row_cnt,'CC1' => 'c1' . $row_cnt]
            );
            $row_cnt++;
        }
        $this->assertEquals($row_cnt, 3);
    }

    /**
     * @depends test_init
     */
    public function test_extract_sheet_2($ex)
    {
        $data = $ex->withSheet(2)->extractContent(
            __DIR__ . '/../Filesystem/Fixtures/xlsx_test_file.xlsx',
            ['A2' => 'AA2','B2' => 'BB2']
        );
        $row_cnt = 1;
        while ($row = array_shift($data)) {
            $this->assertEquals(
                $row,
                ['AA2' => 'a2' . $row_cnt,'BB2' => 'b2' . $row_cnt]
            );
            $row_cnt++;
        }
        $this->assertEquals($row_cnt, 4);
    }

    /**
     * @depends test_init
     */
    public function test_extract_no_header($ex)
    {
        $data = $ex->withSheet(2)->extractContent(
            __DIR__ . '/../Filesystem/Fixtures/xlsx_test_file.xlsx',
            ['AA2','BB2'],
            true
        );
        $row = array_shift($data);
        $this->assertEquals(
            $row,
            ['AA2' => 'A2','BB2' => 'B2']
        );
        $row_cnt = 1;
        while ($row = array_shift($data)) {
            $this->assertEquals(
                $row,
                ['AA2' => 'a2' . $row_cnt,'BB2' => 'b2' . $row_cnt]
            );
            $row_cnt++;
        }
        $this->assertEquals($row_cnt, 4);
    }

    /**
     * @depends test_init
     * @expectException \InvalidArgumentException
     */
    public function test_invalid_mapping($ex)
    {
        try {
            $ex->extractContent(
                __DIR__ . '/../Filesystem/Fixtures/xlsx_test_file.xlsx',
                ['Ax2' => 'AA2','Bx2' => 'BB2']
            );
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }
}
