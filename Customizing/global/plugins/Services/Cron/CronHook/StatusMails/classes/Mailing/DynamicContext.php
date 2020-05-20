<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\Mailing;

use CaT\Plugins\StatusMails\History\UserActivity;
use CaT\Plugins\StatusMails\Course\CourseFlags;
use ILIAS\TMS\Mailing as TMSMailing;

/**
 * Abstract class for mailing-context that can be filled with data.
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
abstract class DynamicContext implements TMSMailing\MailContext
{
    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var UserActivity[]
     */
    protected $data;

    /**
     * @var ContentBlocks\TemplateUserEntry
     */
    protected $text_template;

    /**
     * @var CourseFlags[]
     */
    protected $flags;

    public function __construct(ContentBlocks\TemplateUserEntry $template, \Closure $txt)
    {
        $this->text_template = $template;
        $this->txt = $txt;
    }

    /**
     * Apply data to template.
     * @param UserActivity[] $data
     */
    protected function fillBlock(array $data) : string
    {
        if (count($data) === 0) {
            return '-';
        }
        $template = $this->getContentBlock();
        $buffer = [];
        foreach ($data as $activity) {
            $flags = $this->getFlagsForCourse($activity->getCourseObjId());
            $buffer[] = $template->apply($activity, $flags);
        }
        return implode('<br /><br />', $buffer);
    }

    protected function getContentBlock() : ContentBlocks\TemplateUserEntry
    {
        //currently, there is but one block.
        //this will probably change.
        return $this->text_template;
    }

    final public function getFlagsForCourse(int $crs_obj_id) : CourseFlags
    {
        return $this->flags[$crs_obj_id];
    }

    /**
     * @param UserActivity[] $data
     * @return    DynamicContext
     */
    final public function withData(array $data) : DynamicContext
    {
        $clone = clone $this;
        $clone->data = $data;
        return $clone;
    }

    /**
     * @return    UserActivity[]
     */
    final public function getData() : array
    {
        return $this->data;
    }

    /**
     * @param CourseFlags[] $flags
     * @return    DynamicContext
     */
    final public function withFlags(array $flags) : DynamicContext
    {
        $clone = clone $this;
        $clone->flags = $flags;
        return $clone;
    }

    /**
     * @return    CourseFlags[]
     */
    final public function getFlags() : array
    {
        return $this->flags;
    }

    public function placeholderIds() : array
    {
        return array_keys(static::$PLACEHOLDER);
    }

    /**
     * @inheritdoc
     */
    public function placeholderDescriptionForId(string $placeholder_id) : string
    {
        return $this->txt(static::$PLACEHOLDER[$placeholder_id]);
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
