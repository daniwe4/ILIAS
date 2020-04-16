<?php

/* Copyright (c) 2019 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types = 1);

namespace CaT\Plugins\CourseMember\SignatureList\ConfigurableList;

interface ConfigurableListConfigRepo
{
    const NONE_INT = -1;

    public function create(
        string $name,
        string $description,
        array $standard_fields,
        array $lp_fields,
        array $udf_fields,
        array $roles,
        array $additional,
        string $mail_template
    ) : ConfigurableListConfig;

    public function exists(string $name) : bool;

    public function load(int $id) : ConfigurableListConfig;

    public function save(ConfigurableListConfig $cfg);

    public function tableData() : array;

    public function inUse(int $id) : bool;

    public function delete(int $id);

    /**
     * @return int | null
     */
    public function getDefaultTeplateId();

    /**
     * @return int | null
     */
    public function getSelectedCourseTemplate(int $crs_id);

    public function setSelectedCourseTemplate(int $crs_id, int $tpl_id);

    public function getAvailablePlaceholders() : array;

    /**
     * @return int | null
     */
    public function getTemplateByMailId(string $template_mail_id);
}
