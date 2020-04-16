<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class Mocks extends TestCase
{
    public function getIliasDBMock() : MockObject
    {
        return $this->createMock(\ilDBInterface::class);
    }
}
