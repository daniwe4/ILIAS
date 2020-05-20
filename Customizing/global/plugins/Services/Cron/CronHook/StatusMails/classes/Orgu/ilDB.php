<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\Orgu;

/**
 * Provide Superior-objects.
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
class ilDB implements DB
{
    /**
     * @var \TMSPositionHelper
     */
    protected $pos_helper;

    public function __construct(\TMSPositionHelper $pos_helper)
    {
        $this->pos_helper = $pos_helper;
    }

    /**
     * @inheritdoc
     */
    public function getAllSuperiorsAndMinions() : array
    {
        $superiors = [];
        $user_with_position = $this->pos_helper->getUserIdsWithAtLeastOnePositionWithAuthority();
        foreach ($user_with_position as $key => $user) {
            $superiors[] = new Superior((int) $user, $this->pos_helper->getUserIdWhereUserHasAuhtority($user));
        }

        return $superiors;
    }
}
