<?php

namespace RSPhp\Framework;

/**
 * Handles shared memory variables
 *
 * ** IMPORTANT **
 * You must have shmop extension enabled in order to use this class
 *
 */
class SharedMem {

    //  Set the reserved memory to 512KB
    private static $maxSize = 1024 * 512;

    /**
     * Return an unique integer to use it as cache id
     */
    private static function getCacheId()
    {
        return ftok(__FILE__, 'r');
    } // end function getCacheId

    /**
     * Sets the memory size to use
     *
     * @param $maxSize the size (number of bytes) to reserve
     *
     */
    public static function setMemoryBlockSize($maxSize)
    {
        self::$maxSize = $maxSize;
    } // end function set memory block size

    /**
     * Set a variable in the reserved memory value
     *
     * @param $itemKey The variable name
     * @param $itemValue The variable value, can be anything, class, array, basic data types
     *
     */
    public static function set($itemKey, $itemValue)
    {
        $cacheId = self::getCacheId();
        $flags = "a";
        $resourceId = @shmop_open($cacheId, $flags, 0, 0);

        if (!$resourceId) {
            $flags = "c";
            $items = array();
        } else {
            $flags = "w";
            $data = @shmop_read($resourceId, 0, self::$maxSize);
            $items = unserialize($data);

            @shmop_close( $resourceId );
        } // end if not resource id

        $offset = 0;
        $permissions = 0644;
        $items[$itemKey] = $itemValue;
        $data = serialize($items);
        $memSize = strlen($data);
        $resourceId = @shmop_open($cacheId, $flags, $permissions, self::$maxSize);

        if (!$resourceId) {
            throw new Exception("Cannot open memory resource");
        }

        @shmop_write($resourceId, $data, $offset);
        @shmop_close($resourceId);
    } // end function set

    /**
     * Returns the value of a shared memory variable
     *
     * @param $itemKey The variable name
     */
    public static function get($itemKey = "")
    {
        $cacheId = self::getCacheId();
        $flags = "a";
        $resourceId = @shmop_open($cacheId, $flags, 0, 0);

        if (!$resourceId) {
            if (!$itemKey) {
                return array();
            } // end if not item key

            return null;
        } // end if not resource id


        $memSize = @shmop_size($resourceId);
        $data = @shmop_read($resourceId, 0, self::$maxSize);
        $items = unserialize($data);

        shmop_close( $resourceId );
        $result = ($itemKey) ? $items[$itemKey] : $items;

        return $result;
    } // end function item key

} // end class App
