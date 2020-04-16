<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Rating;

/**
 * Venue configuration entries for rating settings
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Rating
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Rating venue got at the moment
     *
     * @var float
     */
    protected $rating = 0.0;

    /**
     * Some additional infos
     *
     * @var string
     */
    protected $info = "";

    public function __construct(
        int $id,
        float $rating = 0.0,
        string $info = ""
    ) {
        $this->id = $id;
        $this->rating = $rating;
        $this->info = $info;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getRating() : float
    {
        return $this->rating;
    }

    public function getInfo() : string
    {
        return $this->info;
    }
}
