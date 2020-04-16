<?php

use CaT\Plugins\CancellationFeeReport\Settings as Settings;
use PHPUnit\Framework\TestCase;

/**
 * @group needsInstalledILIAS
 */
class DBSettingsRepositoryTest extends TestCase
{
    protected $db;

    protected $backupGlobals = false;


    public function setUp()
    {
        global $DIC;
        if (!$DIC) {
            require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
            ilUnitUtil::performInitialisation();
            global $DIC;
        }

        $this->db = $DIC['ilDB'];
    }

    public function test_init()
    {
        return new Settings\DBSettingsRepository($this->db);
    }

    /**
     * @depends test_init
     */
    public function test_create($rep)
    {
        $set = $rep->create(-1);

        $this->assertFalse($set->online());
        $this->assertEquals($set->id(), -1);
        $this->assertTrue($rep->exists($set->id()));
    }

    /**
     * @depends test_create
     */
    public function test_load()
    {
        $rep = $this->test_init();
        $set = $rep->load(-1);
        $this->assertFalse($set->online());
        $this->assertEquals($set->id(), -1);
        $this->assertTrue($rep->exists($set->id()));
    }

    /**
     * @depends test_load
     */
    public function test_update()
    {
        $rep = $this->test_init();
        $set = $rep->load(-1);

        $set = $set->withOnline(true);
        $rep->update($set);

        $rep = $this->test_init();
        $set = $rep->load(-1);

        $this->assertTrue($set->online());
        $this->assertEquals($set->id(), -1);
        $this->assertTrue($rep->exists($set->id()));
    }

    /**
     * @depends test_update
     */
    public function test_delete()
    {
        $rep = $this->test_init();
        $set = $rep->load(-1);
        $rep->delete($set);
        $this->assertFalse($rep->exists(-1));
    }

    /**
     * @depends test_create
     */
    public function test_recreate()
    {
        $this->test_init()->create(-2);
        try {
            $this->test_init()->create(-2);
            $this->assertFalse('did not throw');
        } catch (\Exception $e) {
        }
    }

    /**
     * @depends test_load
     */
    public function test_load_invalid()
    {
        try {
            $this->test_init()->load(-3);
            $this->assertFalse('did not throw');
        } catch (\Exception $e) {
        }
    }


    public static function tearDownAfterClass()
    {
        require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
        ilUnitUtil::performInitialisation();
        global $DIC;
        $db = $DIC['ilDB'];

        $db->manipulate('DELETE FROM ' . Settings\DBSettingsRepository::TABLE
                            . '	WHERE ' . Settings\DBSettingsRepository::ROW_ID . ' < 0');
    }
}
