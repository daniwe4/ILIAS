<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\Mailing;

/**
 * Context for status-mails
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
class ilMailContextUpcoming extends DynamicContext
{
    protected static $PLACEHOLDER = array(
        'EMPLOYEES_TRAININGS_NEXT_WEEK' => 'placeholder_statusmail_trainings_next_week'
    );

    /**
     * @inheritdoc
     */
    public function valueFor(string $placeholder_id, array $contexts = []) : ?string
    {
        switch ($placeholder_id) {
            case 'EMPLOYEES_TRAININGS_NEXT_WEEK':
                return $this->fillBlock($this->getData());
            default:
                return null;
        }
    }
}
