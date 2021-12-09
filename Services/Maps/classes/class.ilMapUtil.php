<?php declare(strict_types=1);

/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Map Utility Class.
 */
class ilMapUtil
{
    const DEFAULT_TILE = "a.tile.openstreetmap.org b.tile.openstreetmap.org c.tile.openstreetmap.org";
    const DEFAULT_GEOLOCATION = null;

    public static ?ilSetting $_settings = null;

    public static function settings()
    {
        if (self::$_settings === null) {
            self::$_settings = new ilSetting("maps");
        }
        return self::$_settings;
    }

    /**
    * Checks whether Map feature is activated.
    * API key must be provided.
    *
    * @return	boolean		activated true/false
    */
    public static function isActivated() : bool
    {
        return self::settings()->get("enable") == 1;
    }
    
    // RK TODO: check inputs of setters
    
    public static function setActivated(bool $activated) : void
    {
        self::settings()->set("enable", $activated ? "1" : "0");
    }
    
    public static function setType($type)
    {
        self::settings()->set("type", $type);
    }
    
    public static function getType()
    {
        return self::settings()->get("type");
    }
    
    public static function setStdLatitude($lat)
    {
        self::settings()->set("std_latitude", $lat);
    }
    
    public static function getStdLatitude()
    {
        return self::settings()->get("std_latitude");
    }
    
    public static function setStdLongitude($lon)
    {
        self::settings()->set("std_longitude", $lon);
    }
    
    public static function getStdLongitude()
    {
        return self::settings()->get("std_longitude");
    }

    public static function setStdZoom($zoom)
    {
        self::settings()->set("std_zoom", $zoom);
    }

    public static function getStdZoom()
    {
        return self::settings()->get("std_zoom");
    }

    public static function setApiKey($api_key)
    {
        self::settings()->set("api_key", $api_key);
    }

    public static function getApiKey()
    {
        return self::settings()->get("api_key");
    }

    public static function setStdTileServers($tile)
    {
        self::settings()->set("std_tile", $tile);
    }
    
    /**
     * Returns the tile server to be used in the installation.
     *
     * @return	string		tile server url
     */
    public static function getStdTileServers()
    {
        $std_tile = self::settings()->get("std_tile");
        return $std_tile ? $std_tile : self::DEFAULT_TILE;
    }
    

    public static function setStdGeolocationServer($geolocation)
    {
        self::settings()->set("std_geolocation", $geolocation);
    }

    /**
     * Returns the reverse geolocation server to be used in the installation.
     *
     * @return	string		tile server url
     */
    public static function getStdGeolocationServer()
    {
        $std_geoloc = self::settings()->get("std_geolocation");
        return $std_geoloc ? $std_geoloc : self::DEFAULT_GEOLOCATION;
    }

    /**
    * Get default longitude, latitude and zoom.
    *
    * @return	array		array("latitude", "longitude", "zoom")
    */
    public static function getDefaultSettings()
    {
        return array(
            "longitude" => self::settings()->get("std_longitude"),
            "latitude" => self::settings()->get("std_latitude"),
            "zoom" => self::settings()->get("std_zoom"));
    }
    
    /**
    * Get an instance of the GUI class.
    */
    public static function getMapGUI()
    {
        $type = self::getType();
        switch ($type) {
            case "googlemaps":
                return new ilGoogleMapGUI();
            case "openlayers":
                 $map = new ilOpenLayersMapGUI();
                 $map->setTileServers(self::getStdTileServers());
                 $map->setGeolocationServer(self::getStdGeolocationServer());
                 return $map;
            default:
                return new ilGoogleMapGUI();
        }
    }
    
    /**
    * Get a dict { $id => $name } for available maps services.
    *
    * @return array
    */
    public static function getAvailableMapTypes()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $lng->loadLanguageModule("maps");
        return array( "openlayers" => $lng->txt("maps_open_layers_maps")
                    , "googlemaps" => $lng->txt("maps_google_maps")
                    );
    }
}
