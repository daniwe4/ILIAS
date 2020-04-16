<?php declare(strict_types=1);

namespace CaT\Plugins\OrguByMailDomain\Configuration;

class Configuration
{
    protected $id;
    protected $title;
    protected $orgu_ids;
    protected $position_id;
    protected $description;

    public function __construct(
        int $id,
        string $title,
        array $orgu_ids,
        int $position,
        string $description
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->orgu_ids = $orgu_ids;
        $this->position = $position;
        $this->description = $description;
    }

    public function getId() : int
    {
        return $this->id;
    }


    public function getTitle() : string
    {
        return $this->title;
    }

    public function withTitle(string $title) : Configuration
    {
        $other = clone $this;
        $other->title = $title;
        return $other;
    }

    public function getOrguIds() : array
    {
        return $this->orgu_ids;
    }

    public function withOrguIds(array $orgu_ids) : Configuration
    {
        $other = clone $this;
        $other->orgu_ids = $orgu_ids;
        return $other;
    }

    public function getPosition() : int
    {
        return $this->position;
    }

    public function withPosition(int $position) : Configuration
    {
        $other = clone $this;
        $other->position = $position;
        return $other;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function withDescription(string $description) : Configuration
    {
        $other = clone $this;
        $other->description = $description;
        return $other;
    }
}
