<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\TargetGroup;

use CaT\Plugins\CourseClassification\Options\ilDB as OptionDB;

class ilDB extends OptionDB
{
    const TABLE_NAME = "xccl_target_group";
}
