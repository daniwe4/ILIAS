<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\General;

/**
 * Venue configuration entries for general settings
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class General
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Name of the venue
     *
     * @var string
     */
    protected $name = "";

    /**
     * Assigned tags
     *
     * @var \CaT\Plugins\Venues\Tags\Tag[] | []
     */
    protected $tags = array();

    /**
     * Assigned tags for search
     *
     * @var \CaT\Plugins\Venues\Tags\Tag[] | []
     */
    protected $search_tags = array();

    /**
     * @var string
     */
    protected $homepage = "";

    public function __construct(
        int $id,
        string $name,
        string $homepage = "",
        array $tags = array(),
        array $search_tags = array()
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->homepage = $homepage;
        $this->tags = $tags;
        $this->search_tags = $search_tags;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getHomepage() : string
    {
        return $this->homepage;
    }

    /**
     * @return \CaT\Plugins\Venues\Tags\Tag[] | []
     */
    public function getTags() : array
    {
        return $this->tags;
    }

    /**
     * @return \CaT\Plugins\Venues\Tags\Tag[] | []
     */
    public function getSearchTags() : array
    {
        return $this->search_tags;
    }
}
