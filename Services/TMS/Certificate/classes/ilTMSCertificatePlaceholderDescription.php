<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

class ilTMSCertificatePlaceholderDescription
{
    /**
     * @var ilLanguage
     */
    protected $language;

    public function __construct(ilLanguage $language)
    {
        $this->language = $language;
    }

    public function getTMSPlaceholder() : array
    {
        $ret = [];
        $ret["COURSE_TYPE"] = $this->language->txt("pl_course_type");
        $ret["COURSE_START_DATE"] = $this->language->txt("pl_course_start_date");
        $ret["COURSE_END_DATE"] = $this->language->txt("pl_course_end_date");

        if (\ilPluginAdmin::isPluginActive('xetr')) { //edutracking
            $ret["IDD_TIME"] = $this->language->txt("pl_idd_learning_time");
            $ret["IDD_USER_TIME"] = $this->language->txt("pl_idd_learning_time_user");
        }

        return $ret;
    }
}
