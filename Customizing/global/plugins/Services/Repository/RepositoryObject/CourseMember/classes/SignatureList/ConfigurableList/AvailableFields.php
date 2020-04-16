<?php

/* Copyright (c) 2019 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types = 1);

namespace CaT\Plugins\CourseMember\SignatureList\ConfigurableList;

interface AvailableFields
{
    public function getStandardFields() : array;
    public function getLpFields() : array;
    public function getUdfFields() : array;
    public function getRoles() : array;
}
