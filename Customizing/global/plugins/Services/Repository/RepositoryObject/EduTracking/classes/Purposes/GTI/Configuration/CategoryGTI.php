<?php

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\GTI\Configuration;

class CategoryGTI
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $id;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }
}
