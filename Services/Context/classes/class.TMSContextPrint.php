<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

include_once "Services/Context/interfaces/interface.ilContextTemplate.php";

class TMSContextPrint implements ilContextTemplate
{
    /**
     * Are redirects supported?
     *
     * @return bool
     */
    public static function supportsRedirects()
    {
        return true;
    }

    /**
     * Based on user authentication?
     *
     * @return bool
     */
    public static function hasUser()
    {
        return true;
    }

    /**
     * Uses HTTP aka browser
     *
     * @return bool
     */
    public static function usesHTTP()
    {
        return true;
    }

    /**
     * Has HTML output
     *
     * @return bool
     */
    public static function hasHTML()
    {
        return true;
    }

    /**
     * Uses template engine
     *
     * @return bool
     */
    public static function usesTemplate()
    {
        return true;
    }

    /**
     * Init client
     *
     * @return bool
     */
    public static function initClient()
    {
        return true;
    }

    /**
     * Try authentication
     *
     * @return bool
     */
    public static function doAuthentication()
    {
        return false;
    }

    /**
     * Check if persistent session handling is supported
     * @return boolean
     */
    public static function supportsPersistentSessions()
    {
        return false;
    }

    /**
     * Supports push messages
     *
     * @return bool
     */
    public static function supportsPushMessages()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function isSessionMainContext()
    {
        return true;
    }
}
