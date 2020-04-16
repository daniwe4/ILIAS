<?php
namespace CaT\Plugins\CourseMailing\RoleMapping;

/**
 * This is the object for a role mapping
 */
class RoleMapping
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $role_id;

    /**
     * @var string
     */
    protected $role_title;

    /**
     * @var int
     */
    protected $mail_template_id;

    /**
     * @var string[]
     */
    protected $attachment_ids;

    public function __construct(
        int $id,
        int $obj_id,
        int $role_id,
        int $mail_template_id = null,
        array $attachment_ids = []
    ) {
        $this->id = $id;
        $this->obj_id = $obj_id;
        $this->role_id = $role_id;
        $this->mail_template_id = $mail_template_id;
        $this->attachment_ids = $attachment_ids;

        $this->role_title = '';
    }

    /**
    * @return int
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * @return int
    */
    public function getObjectId()
    {
        return $this->obj_id;
    }

    /**
    * @return int
    */
    public function getRoleId()
    {
        return $this->role_id;
    }

    /**
     * @param int 	$id
     * @return RoleMapping
     */
    public function withRoleId($id)
    {
        assert('is_int($id)');
        $clone = clone $this;
        $clone->role_id = $id;
        return $clone;
    }

    /**
    * @return string
    */
    public function getRoleTitle()
    {
        return $this->role_title;
    }

    /**
     * @param string 	$title
     * @return RoleMapping
     */
    public function withRoleTitle($title)
    {
        assert('is_string($title)');
        $clone = clone $this;
        $clone->role_title = $title;
        return $clone;
    }

    /**
    * @return	int|null
    */
    public function getTemplateId()
    {
        return $this->mail_template_id;
    }

    /**
     * @param	int|null	$id
     * @return	RoleMapping
     */
    public function withTemplateId($id)
    {
        assert('is_int($id) || is_null($id)');
        $clone = clone $this;
        $clone->mail_template_id = $id;
        return $clone;
    }

    /**
    * @return string[]
    */
    public function getAttachmentIds()
    {
        return $this->attachment_ids;
    }

    /**
     * @param string[] 	$attachment_ids
     * @return RoleMapping
     */
    public function withAttachmentIds(array $attachment_ids)
    {
        $clone = clone $this;
        $clone->attachment_ids = $attachment_ids;
        return $clone;
    }
}
