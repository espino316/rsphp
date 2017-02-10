<?php
/**
 * GcmPusher.php
 *
 * PHP Version 5
 *
 * GcmPusher File Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

namespace RSPhp\Framework;

// API access key from Google API's Console
define('API_ACCESS_KEY', App::get('GOOGLE_API_PUSH_KEY'));
define('ANDROID_PUSH_URL', 'https://android.googleapis.com/gcm/send');

/**
 * Does push notifications for android
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
class GCMPusher
{
    public static $apiKey;

    static $androidPushUri = 'https://android.googleapis.com/gcm/send';

    /**
     * Send a GCM push message
     *
     * @param mixed[] $to       To whom send the message
     * @param String  $message  The message to send
     * @param String  $title    The message's title
     * @param String  $subTitle Message's subtitle
     * @param mixed[] $info     The actual message
     * @param Int     $vibrate  optional, boolean either 1 or 0, default 1
     * @param Int     $sound    optional, boolean either 1 or 0, default 1
     *
     * @return Boolean
     */
    public static function send(
        $to,
        $message,
        $title,
        $subTitle,
        $info = null,
        $vibrate = 1,
        $sound = 1
    ) {
        if (is_array($to) ) {
            $fields['registration_ids'] = $to;
        } else {
            $fields['to'] = $to;
        }

        $msg['message'] = $message;
        $msg['title'] = $title;
        $msg['subtitle'] = $subTitle;
        $msg['vibrate'] = $vibrate;
        $msg['sound'] = $sound;

        if ($info != null ) {
            $msg['info'] = $info;
        }

        $fields['data'] = $msg;

        $headers[] = 'Authorization: key=' . API_ACCESS_KEY;
        $headers[] = 'Content-Type: application/json';

        $result
            = self::doCurl(
                ANDROID_PUSH_URL,
                $headers,
                $fields
            );

        $result = json_decode($result, true);

        if ($result['success'] == "1" ) {
            return true;
        } else {
            return false;
        }
    } // end function send

    /**
     * Do a curl requests
     *
     * @param String $url     The url to request
     * @param Array  $headers Http headers
     * @param Array  $fields  Parameters
     *
     * @return mixed[]
     */
    static function doCurl(
        $url,
        $headers,
        $fields
    ) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

    } // end function doCurl
} // end class GcmPusher
