<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class DeletedDigester implements Digester
{
    protected $deleted;

    public function __construct(bool $deleted)
    {
        $this->deleted = $deleted;
    }

    public function digest(array $payload)
    {
        return ['deleted' => $this->deleted];
    }
}
