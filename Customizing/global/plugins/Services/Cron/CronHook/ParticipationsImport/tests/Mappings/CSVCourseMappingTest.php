<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase as TestCase;
use CaT\Plugins\ParticipationsImport\Mappings\CSVCourseMapping;
use CaT\Plugins\ParticipationsImport\Mappings\CourseMapping;
use CaT\Plugins\ParticipationsImport\Mappings\Mapping;
use CaT\Plugins\ParticipationsImport\Filesystem\ConfigStorage;
use CaT\Plugins\ParticipationsImport\Filesystem\Config;
use CaT\Plugins\ParticipationsImport\IliasUtils\CourseUtils;

class CSVCourseMappingTest extends TestCase
{
    protected static $idc;
    protected static $fs_mock;

    public function setUp() : void
    {
        if (!self::$idc) {
            self::$idc = $this->createMock(CourseUtils::class);
            self::$idc->method('courseIdExists')
                ->will($this->returnCallback(function ($int) {
                    return $int % 2 === 0;
                }));
        }
        self::$fs_mock = $this->createMock(ConfigStorage::class);
        self::$fs_mock->method('loadCurrentConfig')
            ->willReturn(new Config(__DIR__ . '/../Filesystem/Fixtures/', ''));
    }

    public function test_init()
    {
        $cm = new CSVCourseMapping(self::$fs_mock, self::$idc);
        $this->assertInstanceOf(CourseMapping::class, $cm);
        return $cm;
    }


    /**
     * @depends test_init
     */
    public function test_new_id_and_create($cm)
    {
        $id = $cm->iliasCrsIdForExternCrsId((string) 1);
        $this->assertEquals($id, -1);
        $id = $cm->iliasCrsIdForExternCrsId((string) 2);
        $this->assertEquals($id, -3);
        $id = $cm->iliasCrsIdForExternCrsId((string) 1);
        $this->assertEquals($id, -1);
        $id = $cm->iliasCrsIdForExternCrsId((string) 1, false);
        $this->assertEquals($id, -1);
        $id = $cm->iliasCrsIdForExternCrsId((string) 3, false);
        $this->assertEquals($id, Mapping::NO_MAPPING_FOUND_INT);
    }

    /**
     * @depends test_new_id_and_create
     */
    public function test_reload_1()
    {
        $cm = new CSVCourseMapping(self::$fs_mock, self::$idc);
        $this->test_new_id_and_create($cm);
    }

    /**
     * @depends test_reload_1
     */
    public function test_new_id_dont_create()
    {
        $cm = new CSVCourseMapping(self::$fs_mock, self::$idc);
        $id = $cm->iliasCrsIdForExternCrsId((string) 3, false);
        $this->assertEquals($id, Mapping::NO_MAPPING_FOUND_INT);
        $id = $cm->iliasCrsIdForExternCrsId((string) 2, false);
        $this->assertEquals($id, -3);
    }

    /**
     * @depends test_new_id_dont_create
     */
    public function test_reload_new_id_create_1()
    {
        $cm = new CSVCourseMapping(self::$fs_mock, self::$idc);
        $id = $cm->iliasCrsIdForExternCrsId((string) 1);
        $this->assertEquals($id, -1);
        $id = $cm->iliasCrsIdForExternCrsId((string) 2);
        $this->assertEquals($id, -3);
        $id = $cm->iliasCrsIdForExternCrsId((string) 3);
        $this->assertEquals($id, -5);
    }

    public static function tearDownAfterClass() : void
    {
        @unlink(__DIR__ . '/../Filesystem/Fixtures/id_relations.csv');
    }
}
