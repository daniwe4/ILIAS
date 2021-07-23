<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

class ilLSItemsDBStub extends ilLSItemsDB
{
    protected function getIconPathForType(string $type) : string
    {
        return './image/tester/myimage.png';
    }
}
