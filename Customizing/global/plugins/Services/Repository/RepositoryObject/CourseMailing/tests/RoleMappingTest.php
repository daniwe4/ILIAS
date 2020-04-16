<?php

use PHPUnit\Framework\TestCase;

use CaT\Plugins\CourseMailing\RoleMapping\RoleMapping;

class RoleMappingTest extends TestCase
{
    public function initProvider()
    {
        return
        [[1,2,3,null,[]]
        ,[1,2,3,4,[]]
        ,[1,2,3,null,[5]]
        ,[1,2,3,null,[5,6]]
        ,[1,2,3,4,[5,6]]];
    }


    /**
     * @dataProvider initProvider
     */
    public function test_init(
        $id,
        $obj_id,
        $role_id,
        $mail_template_id,
        array $attachment_ids
    ) {
        $setting = new RoleMapping(
            $id,
            $obj_id,
            $role_id,
            $mail_template_id,
            $attachment_ids
        );
        $this->assertEquals($id, $setting->getId());
        $this->assertEquals($obj_id, $setting->getObjectId());
        $this->assertEquals($role_id, $setting->getRoleId());
        $this->assertEquals('', $setting->getRoleTitle());
        $this->assertEquals($mail_template_id, $setting->getTemplateId());
        $this->assertEquals($attachment_ids, $setting->getAttachmentIds());
    }

    /**
     * @depends test_init
     */
    public function test_with_role_id()
    {
        $set = new RoleMapping(1, 2, 3, 4, [5,6]);
        $this->assertEquals($set->withRoleId(30)->getRoleId(), 30);
    }

    /**
     * @depends test_init
     */
    public function test_with_role_title()
    {
        $set = new RoleMapping(1, 2, 3, 4, [5,6]);
        $this->assertEquals($set->withRoleTitle('role2')->getRoleTitle(), 'role2');
    }

    /**
     * @depends test_init
     */
    public function test_with_template_id()
    {
        $set = new RoleMapping(1, 2, 3, 4, [5,6]);
        $this->assertEquals($set->withTemplateId(40)->getTemplateId(), 40);
        $this->assertNull($set->withTemplateId(null)->getTemplateId());
    }

    /**
     * @depends test_init
     */
    public function test_with_attachment_ids()
    {
        $set = new RoleMapping(1, 2, 3, 4, [5,6]);
        $this->assertEquals($set->withAttachmentIds([50,60])->getAttachmentIds(), [50,60]);
        $this->assertEquals($set->withAttachmentIds([])->getAttachmentIds(), []);
    }
}
