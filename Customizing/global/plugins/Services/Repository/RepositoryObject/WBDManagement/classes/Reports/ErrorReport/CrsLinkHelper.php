<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\Reports\ErrorReport;

use \ILIAS\UI;

class CrsLinkHelper
{
    /**
     * @var UI\Renderer
     */
    protected $ren;

    /**
     * @var UI\Factory
     */
    protected $fac;

    public function __construct(UI\Renderer $ren, UI\Factory $fac)
    {
        $this->ren = $ren;
        $this->fac = $fac;
    }

    public function renderLink(int $ref_id) : string
    {
        return $this->ren->render(
            $this->fac->link()->standard((string) $ref_id, \ilLink::_getStaticLink($ref_id, 'crs'))
        );
    }
}
