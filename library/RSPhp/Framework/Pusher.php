<?php
/**
 * Pusher.php
 *
 * PHP Version 5
 *
 * Pusher File Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

namespace Espino\RRSPhp;

 /**
 * Send push notifications, iOS and Android
 *
 * Please report bugs on https://github.com/espino316/rsphp/issues
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */
class Pusher
{

    /**
     * Sends a push notification
     *
     * @param Int     $platform Indicates the platform, 1 for Android, 2 for iOS
     * @param mixed[] $to       Array or string with the recipient
     * @param String  $title    The message's title
     * @param String  $message  The actual message
     * @param Array   $info     The message's information
     * @param Bool    $sound    Indicates if the push notification will have a sound
     *
     * @return void
     */
    static function send(
        $platform,
        $to,
        $title,
        $message,
        $info,
        $sound = null
    ) {
        if ($platform == 1 ) { // Android
            if ($sound ) {
                $sound = 'default';
            }

            GCMPusher::send(
                $to,
                $message,
                $title,
                $title,
                $info,
                1,
                $sound
            );

        } else if ($platform == 2 ) { // iOS
            if ($sound == null ) {
                $sound = 'default';
            }

            ApnsPusher::send(
                $to,
                $title,
                $message,
                $info,
                $sound
            );
        } // end if then else platform
    } // end function send
} // end class Pusher
