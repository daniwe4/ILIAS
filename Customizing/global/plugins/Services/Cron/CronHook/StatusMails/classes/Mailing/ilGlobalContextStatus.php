<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\Mailing;

use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use CaT\Ente\ILIAS\Entity;

/**
 * Context for status-mails
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
class ilGlobalContextStatus extends ilMailContextStatus
{
    use ilHandlerObjectHelper;

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var \ilObjRootFolder
     */
    protected $owner;

    public function __construct(Entity $entity, \ilObjRootFolder $owner, callable $txt)
    {
        $this->entity = $entity;
        $this->owner = $owner;
        $this->txt = $txt;
    }

    /**
     * @inheritdoc
     */
    protected function getEntityRefId()
    {
        return $this->entity()->getRefId();
    }

    /**
     * @inheritdoc
     */
    public function entity()
    {
        return $this->entity;
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }
}
