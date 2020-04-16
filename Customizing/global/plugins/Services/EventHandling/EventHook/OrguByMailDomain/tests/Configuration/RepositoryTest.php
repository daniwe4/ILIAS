<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\OrguByMailDomain\Configuration\Configuration as C;
use CaT\Plugins\OrguByMailDomain\Configuration\Repository as CR;

/**
 * @group needsInstalledILIAS
 */
class RepositoryTest extends TestCase
{
    protected $backupGlobals = false;

    protected static $created_ids = [];

    public function setUp() : void
    {
        require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
        ilUnitUtil::performInitialisation();
        global $DIC;
        $this->db = $DIC['ilDB'];
    }


    public function test_init()
    {
        $cr = new CR($this->db);
        $this->assertInstanceOf(CR::class, $cr);
        return $cr;
    }

    /**
     * @depends test_init
     */
    public function test_create()
    {
        $cr = $this->test_init();
        $c = $cr->create(
            'title1',
            [1,2,3],
            4,
            'desc1'
        );
        $id = $c->getId();
        $this->assertInternalType('int', $id);
        $this->assertFalse(in_array($id, self::$created_ids));
        self::$created_ids['title1'] = $id;
        $this->assertInstanceOf(C::class, $c);
        $this->assertEquals($c->getTitle(), '*@title1');
        $this->assertEquals($c->getOrguIds(), [1,2,3]);
        $this->assertEquals($c->getPosition(), 4);
        $this->assertEquals($c->getDescription(), 'desc1');


        $c = $cr->create(
            'title2',
            [11,12,13],
            14,
            'desc2'
        );
        $id = $c->getId();
        $this->assertInternalType('int', $id);
        $this->assertFalse(in_array($id, self::$created_ids));
        self::$created_ids['title2'] = $id;
        $this->assertInstanceOf(C::class, $c);
        $this->assertEquals($c->getTitle(), '*@title2');
        $this->assertEquals($c->getOrguIds(), [11,12,13]);
        $this->assertEquals($c->getPosition(), 14);
        $this->assertEquals($c->getDescription(), 'desc2');
    }

    /**
     * @depends test_create
     * @expectedException Exception
     */
    public function test_create_double()
    {
        $cr = $this->test_init();
        $c = $cr->create(
            'title1',
            [100],
            40,
            'foo'
        );
    }

    /**
     * @depends test_create
     */
    public function test_load_by_id()
    {
        $cr = $this->test_init();
        $c = $cr->loadById(self::$created_ids['title1']);
        $this->assertEquals($c->getId(), self::$created_ids['title1']);
        $this->assertEquals($c->getTitle(), '*@title1');
        $this->assertEquals($c->getOrguIds(), [1,2,3]);
        $this->assertEquals($c->getPosition(), 4);
        $this->assertEquals($c->getDescription(), 'desc1');
    }

    /**
     * @depends test_create
     */
    public function test_load_by_invalid_id()
    {
        $cr = $this->test_init();
        $this->assertNull($cr->loadById(-1));
        $this->assertNull($cr->loadByTitle('FAKE'));
    }


    /**
     * @depends test_create
     */
    public function test_load_by_title()
    {
        $cr = $this->test_init();
        $c = $cr->loadByTitle('title1');
        $this->assertEquals($c->getId(), self::$created_ids['title1']);
        $this->assertEquals($c->getTitle(), '*@title1');
        $this->assertEquals($c->getOrguIds(), [1,2,3]);
        $this->assertEquals($c->getPosition(), 4);
        $this->assertEquals($c->getDescription(), 'desc1');
    }

    /**
     * @depends test_load_by_id
     */
    public function test_update()
    {
        $cr = $this->test_init();
        $c = $cr->loadById(self::$created_ids['title1']);
        $cr->update(
            $c	->withTitle('title1-1')
                ->withOrguIds([6,7,8])
                ->withPosition(9)
                ->withDescription('desc1-1')
        );
        $cr = $this->test_init();
        $c = $cr->loadById(self::$created_ids['title1']);
        $this->assertEquals($c->getId(), self::$created_ids['title1']);
        $this->assertEquals($c->getTitle(), '*@title1-1');
        $this->assertEquals($c->getOrguIds(), [6,7,8]);
        $this->assertEquals($c->getPosition(), 9);
        $this->assertEquals($c->getDescription(), 'desc1-1');
    }

    /**
     * @depends test_load_by_id
     * @expectedException Exception
     */
    public function test_update_double()
    {
        $cr = $this->test_init();
        $c = $cr->loadById(self::$created_ids['title1']);
        $cr->update(
            $c	->withTitle('*@title2')
                ->withOrguIds([6,7,8])
                ->withPosition(9)
                ->withDescription('desc1-1')
        );
    }

    /**
     * @depends test_update
     */
    public function test_load_all()
    {
        $all = $this->test_init()->loadAll();
        $checked = [];
        foreach ($all as $c) {
            switch ($c->getId()) {
                case self::$created_ids['title1']:
                    $this->assertEquals($c->getTitle(), '*@title1-1');
                    $this->assertEquals($c->getOrguIds(), [6,7,8]);
                    $this->assertEquals($c->getPosition(), 9);
                    $this->assertEquals($c->getDescription(), 'desc1-1');
                    $checked[] = 'title1';
                    break;
                case self::$created_ids['title2']:
                    $this->assertEquals($c->getTitle(), '*@title2');
                    $this->assertEquals($c->getOrguIds(), [11,12,13]);
                    $this->assertEquals($c->getPosition(), 14);
                    $this->assertEquals($c->getDescription(), 'desc2');
                    $checked[] = 'title2';
                    break;
            }
        }
        $this->assertContains('title1', $checked);
        $this->assertContains('title2', $checked);
    }


    /**
     * @depends test_load_all
     */
    public function test_delete()
    {
        $cr = $this->test_init();
        $c = $cr->loadByTitle('title2');
        $cr->delete($c);
        $cr = $this->test_init();
        $this->assertNull($cr->loadByTitle('title2'));
        $c = $cr->loadByTitle('title1-1');
        $this->assertEquals($c->getId(), self::$created_ids['title1']);
        $this->assertEquals($c->getTitle(), '*@title1-1');
        $this->assertEquals($c->getOrguIds(), [6,7,8]);
        $this->assertEquals($c->getPosition(), 9);
        $this->assertEquals($c->getDescription(), 'desc1-1');
    }

    public static function tearDownAfterClass() : void
    {
        require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
        ilUnitUtil::performInitialisation();
        global $DIC;
        $db = $DIC['ilDB'];
        $db->manipulate(
            'DELETE FROM x_obmd_config WHERE ' .
            $db->in('id', self::$created_ids, false, 'integer')
        );
        $db->manipulate(
            'DELETE FROM x_obmd_conf_orgu WHERE ' .
            $db->in('id', self::$created_ids, false, 'integer')
        );
    }
}
