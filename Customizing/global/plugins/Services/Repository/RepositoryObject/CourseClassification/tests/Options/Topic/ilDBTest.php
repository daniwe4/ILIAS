<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Topic;

use CaT\Plugins\CourseClassification\Options\Category\Category;
use PHPUnit\Framework\TestCase;

class ilDBTest extends TestCase
{
    public function test_create()
    {
        $id = 34;
        $caption = 'best ever';

        $values = [
            "id" => ["integer",$id],
            "caption" => ["text", $caption]
        ];

        $il_db = $this->getILIASDBMock();
        $il_db->expects($this->once())
            ->method('nextId')
            ->with('xccl_topic')
            ->willReturn($id)
        ;

        $il_db->expects($this->once())
            ->method('insert')
            ->with('xccl_topic', $values)
        ;

        $db = new ilDB($il_db);
        $result = $db->create($caption);
        $this->assertInstanceOf(Topic::class, $result);
        $this->assertEquals($id, $result->getId());
        $this->assertEquals($caption, $result->getCaption());
        $this->assertNull($result->getCategory());
    }

    public function test_get_affected_cc_object_obj_ids()
    {
        $topic_id = 12;
        $cc_1 = 44;
        $cc_2 = 98;
        $cc_3 = 150;

        $row_1 = [
            'obj_id' => $cc_1
        ];
        $row_2 = [
            'obj_id' => $cc_2
        ];
        $row_3 = [
            'obj_id' => $cc_3
        ];
        $db_result = [$row_1, $row_2, $row_3];
        $fnc_result = [$cc_1, $cc_2, $cc_3];
        $query = 'SELECT obj_id' . PHP_EOL
            . ' FROM xccl_data_topics' . PHP_EOL
            . ' WHERE option_id = ' . $topic_id;

        $il_db = $this->getILIASDBMock();
        $il_db->expects($this->once())
            ->method('quote')
            ->with($topic_id)
            ->willReturn($topic_id)
        ;
        $il_db->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($db_result)
        ;
        $il_db->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with($db_result)
            ->willReturnOnConsecutiveCalls($row_1, $row_2, $row_3, null)
        ;

        $db = new ilDB($il_db);
        $result = $db->getAffectedCCObjectObjIds($topic_id);
        $this->assertEquals($fnc_result, $result);
    }

    public function test_delete_assignments_by()
    {
        $topic_id = 12;
        $query = 'DELETE FROM xccl_category_topic' . PHP_EOL
            . ' WHERE topic_id = ' . $topic_id;

        $il_db = $this->getILIASDBMock();
        $il_db->expects($this->once())
            ->method('quote')
            ->with($topic_id)
            ->willReturn($topic_id)
        ;
        $il_db->expects($this->once())
            ->method('manipulate')
            ->with($query)
        ;

        $db = new ilDB($il_db);
        $db->deleteAssignmentsBy($topic_id);
    }

    public function test_get_category_by_id_result_null()
    {
        $category_id = 150;
        $db_result = [];
        $query = 'SELECT id, caption FROM xccl_category' . PHP_EOL
            . ' WHERE id = ' . $category_id;

        $il_db = $this->getILIASDBMock();
        $il_db->expects($this->once())
            ->method('quote')
            ->with($category_id)
            ->willReturn($category_id)
        ;
        $il_db->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($db_result)
        ;
        $il_db->expects($this->once())
            ->method('numRows')
            ->with($db_result)
            ->willReturn(count($db_result))
        ;
        $il_db->expects($this->never())
            ->method('fetchAssoc')
        ;

        $db = new ilDB($il_db);
        $result = $db->getCategoryById($category_id);
        $this->assertNull($result);
    }

    public function test_get_category_by_id()
    {
        $category_id = 150;
        $caption = 'cat_caption';

        $row = [
            'id' => $category_id,
            'caption' => $caption
        ];
        $db_result = [$row];
        $query = 'SELECT id, caption FROM xccl_category' . PHP_EOL
            . ' WHERE id = ' . $category_id;

        $il_db = $this->getILIASDBMock();
        $il_db->expects($this->once())
            ->method('quote')
            ->with($category_id)
            ->willReturn($category_id)
        ;
        $il_db->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($db_result)
        ;
        $il_db->expects($this->once())
            ->method('numRows')
            ->with($db_result)
            ->willReturn(count($db_result))
        ;
        $il_db->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with($db_result)
            ->willReturn($row)
        ;

        $db = new ilDB($il_db);
        $result = $db->getCategoryById($category_id);
        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals($category_id, $result->getId());
        $this->assertEquals($caption, $result->getCaption());
    }

    public function test_assign_category()
    {
        $topic_id = 421;
        $category_id = 123;
        $values = [
            "topic_id" => ["integer", $topic_id],
            "category_id" => ["integer", $category_id]
        ];

        $il_db = $this->getILIASDBMock();
        $il_db->expects($this->once())
            ->method('insert')
            ->with('xccl_category_topic', $values)
        ;

        $db = new ilDB($il_db);
        $db->assignCategory($topic_id, $category_id);
    }

    public function test_select()
    {
        $id = 10;
        $caption = 'caption';
        $category_id = 25;
        $category_caption = 'category_caption';

        $row = [
            'id' => $id,
            'caption' => $caption,
            'category_id' => $category_id,
            'category_caption' => $category_caption
        ];
        $db_result = [$row];

        $query = 'SELECT A.id, A.caption, C.id AS category_id, C.caption AS category_caption' . PHP_EOL
            . ' FROM xccl_topic A' . PHP_EOL
            . ' LEFT JOIN xccl_category_topic B' . PHP_EOL
            . '     ON A.id = B.topic_id' . PHP_EOL
            . ' LEFT JOIN xccl_category C' . PHP_EOL
            . '     ON B.category_id = C.id';

        $il_db = $this->getILIASDBMock();
        $il_db->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($db_result)
        ;
        $il_db->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with($db_result)
            ->willReturnOnConsecutiveCalls($row, null)
        ;

        $db = new ilDB($il_db);
        $result = $db->select();
        $this->assertIsArray($result);
        $category = array_shift($result);
        $this->assertInstanceOf(Topic::class, $category);
        $this->assertEquals($id, $category->getId());
        $this->assertEquals($caption, $category->getCaption());

        $category = $category->getCategory();
        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals($category_id, $category->getId());
        $this->assertEquals($category_caption, $category->getCaption());
    }

    public function testGetCategoriesForForm()
    {
        $id_1 = 23;
        $caption_1 = 'caption_1';
        $id_2 = 44;
        $caption_2 = 'caption_2';

        $row_1 = [
            'id' => $id_1,
            'caption' => $caption_1
        ];
        $row_2 = [
            'id' => $id_2,
            'caption' => $caption_2
        ];
        $db_result = [
            $row_1,
            $row_2
        ];
        $fnc_result = [
            $id_1 => $caption_1,
            $id_2 => $caption_2
        ];
        $query = 'SELECT id, caption FROM xccl_category';
        $il_db = $this->getILIASDBMock();
        $il_db->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($db_result)
        ;
        $il_db->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with($db_result)
            ->willReturnOnConsecutiveCalls($row_1, $row_2, null)
        ;

        $db = new ilDB($il_db);
        $result = $db->getCategoriesForForm();
        $this->assertEquals($fnc_result, $result);
    }

    public function test_get_assigned_categories()
    {
        $topic_id = 334;
        $category_id = 44;
        $category_id_2 = 84;

        $row = [
            'category_id' => $category_id
        ];
        $row_2 = [
            'category_id' => $category_id_2
        ];
        $db_result = [$row, $row_2];
        $fnc_result = [$category_id, $category_id_2];
        $query = 'SELECT category_id FROM' . PHP_EOL
            . ' xccl_category_topic' . PHP_EOL
            . ' WHERE topic_id = ' . $topic_id;

        $il_db = $this->getILIASDBMock();
        $il_db->expects($this->once())
            ->method('quote')
            ->with($topic_id)
            ->willReturn($topic_id)
        ;
        $il_db->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($db_result)
        ;
        $il_db->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with($db_result)
            ->willReturnOnConsecutiveCalls($row, $row_2, null)
        ;

        $db = new ilDB($il_db);
        $result = $db->getAssignedCategories($topic_id);

        $this->assertEquals($fnc_result, $result);
    }

    protected function getILIASDBMock()
    {
        return $this->createMock(\ilDBInterface::class);
    }
}
