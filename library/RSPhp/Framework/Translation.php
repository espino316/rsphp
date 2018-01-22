<?php

namespace RSPhp\Framework;

class Translation
{
    private static $items;
    private static $language;

    /**
     * Gets an specific item or all the translation items
     */
    public static function get($key = null)
    {
        if (!$key) {
            return self::$items;
        } // end if not key

        return self::$items[$key];
    } // end public function get

    /**
     * Loads the translation into memory
     *
     * @return null
     */
    public static function load()
    {
        //  Initialize array
        self::$items = array();

        //  Get the language
        self::$language = App::get("language");

        //  If no language, return
        if (!self::$language) {
            return;
        } // end if not language translation

        //  Get the file
        $file = ROOT.DS."application".DS."Data".DS."Languages".DS.self::$language.".json";

        //  Load the translation items
        self::$items = json_decode(File::read($file), true);

    } // end load translation
} // end class Translation
