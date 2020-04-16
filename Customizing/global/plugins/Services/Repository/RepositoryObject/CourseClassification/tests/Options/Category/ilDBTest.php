<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Category;

use PHPUnit\Framework\TestCase;

class ilDBTest extends TestCase
{
    public function test_select()
    {
        $id = 10;
        $caption = 'caption';
        $topic_id = 25;
        $topic_caption = 'topic_caption';
        
        $row = [
            'id' => $id,
            'caption' => $caption,
            'topic_id' => $topic_id,
            'topic_caption' => $topic_caption
        ];
        
        $db_result = [$row];
        $query = 'SELECT A.id, A.caption, C.id AS topic_id, C.caption AS topic_caption' . PHP_EOL
            . ' FROM xccl_category A' . PHP_EOL
            . ' LEFT JOIN xccl_category_topic B' . PHP_EOL
            . '    ON A.id = B.category_id' . PHP_EOL
            . ' LEFT JOIN xccl_topic C' . PHP_EOL
            . '    ON B.topic_id = C.id' . PHP_EOL
            . ' ORDER BY A.id' . PHP_EOL
        ;
        
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
        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals($id, $category->getId());
        $this->assertEquals($caption, $category->getCaption());

        $topics = $category->getTopics();
        $this->assertIsArray($topics);
        $topic = array_shift($topics);
        $this->assertEquals($topic_id, $topic->getId());
        $this->assertEquals($topic_caption, $topic->getCaption());
        $this->assertNull($topic->getCategory());
    }

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
            ->with('xccl_category')
            ->willReturn($id)
        ;

        $il_db->expects($this->once())
            ->method('insert')
            ->with('xccl_category', $values)
        ;

        $db = new ilDB($il_db);
        $result = $db->create($caption);
        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals($id, $result->getId());
        $this->assertEquals($caption, $result->getCaption());
        $this->assertIsArray($result->getTopics());
        $this->assertEmpty($result->getTopics());
    }

    public function test_get_topics_by_id()
    {
        $category_id = 20;
        $id_1 = 15;
        $caption_1 = 'top topic';
        $id_2 = 50;
        $caption_2 = 'bad topic';
        $row1 = [
            'id' => $id_1,
            'caption' => $caption_1,
        ];
        $row2 = [
            'id' => $id_2,
            'caption' => $caption_2,
        ];

        $db_result = [$row1, $row2];
        $query = 'SELECT id, caption' . PHP_EOL
            . ' FROM xccl_topic A' . PHP_EOL
            . ' JOIN xccl_category_topic B' . PHP_EOL
            . '     ON B.topic_id = A.id' . PHP_EOL
            . ' WHERE B.category_id = ' . $category_id
        ;

        $il_db = $this->getILIASDBMock();
        $il_db->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($db_result)
        ;
        $il_db->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with($db_result)
            ->willReturnOnConsecutiveCalls($row1, $row2, null)
        ;
        $il_db->expects($this->atLeastOnce())
            ->method('quote')
            ->with($category_id)
            ->willReturn($category_id)
        ;

        $db = new ilDB($il_db);
        $result = $db->getTopicsById($category_id);
        $this->assertIsArray($result);
        $this->assertEquals(count($db_result), count($result));

        $topic_1 = array_shift($result);
        $this->assertEquals($id_1, $topic_1->getId());
        $this->assertEquals($caption_1, $topic_1->getCaption());

        $topic_2 = array_shift($result);
        $this->assertEquals($id_2, $topic_2->getId());
        $this->assertEquals($caption_2, $topic_2->getCaption());
    }
    
    protected function getILIASDBMock()
    {
        return $this->createMock(\ilDBInterface::class);
    }
}
