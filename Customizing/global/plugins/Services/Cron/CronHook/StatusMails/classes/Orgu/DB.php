<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\Orgu;

/**
 * The DB to factor Superior-objects.
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
interface DB
{
    /**
     * Get all users that have a superior-role somewhere.
     * Also retrieve their respective Employees.
     * @return    Superior[]
     */
    public function getAllSuperiorsAndMinions() : array;
}
