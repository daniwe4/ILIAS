<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\Orgu;

/**
 * The superior recieves a mail with information about her employees.
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
class Superior
{
    /**
     * @var int
     */
    protected $superior_id;

    /**
     * @var int[]
     */
    protected $employees;

    public function __construct(int $superior_id, array $employees)
    {
        $this->superior_id = $superior_id;
        $this->employees = array_filter(
            $employees,
            function ($employee_id) {
                return $employee_id !== $this->superior_id;
            }
        );
    }

    public function getUserId() : int
    {
        return $this->superior_id;
    }

    /**
     * @return int[]
     */
    public function getEmployees() : array
    {
        return $this->employees;
    }
}
