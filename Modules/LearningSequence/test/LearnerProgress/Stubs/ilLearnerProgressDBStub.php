<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

class ilLearnerProgressDBStub extends ilLearnerProgressDB
{
    protected function getLearningProgressFor(int $usr_id, LSItem $ls_item) : int
    {
        return 20;
    }
}
