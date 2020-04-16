<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\AdditionalLinks;

/**
 * This is an (labeled) additional link.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class AdditionalLink
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $url;

    public function __construct(string $label, string $url)
    {
        $this->label = $label;
        $this->url = $url;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function getUrl() : string
    {
        return $this->url;
    }
}
