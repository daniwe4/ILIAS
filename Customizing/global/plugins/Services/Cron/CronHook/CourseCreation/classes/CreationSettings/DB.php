<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseCreation\CreationSettings;

interface DB
{
    /**
     * @param int[] 	$roles
     */
    public function save(array $roles);
    /**
     * @return int[]
     */
    public function select() : array;
}
