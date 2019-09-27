<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

class TMSSession
{
    const SEARCH_REF_ID = "active_search";

    public function setCurrentSearch($value)
    {
        ilSession::set(self::SEARCH_REF_ID, $value);
    }

    public function getCurrentSearch()
    {
        return ilSession::get(self::SEARCH_REF_ID);
    }
}
