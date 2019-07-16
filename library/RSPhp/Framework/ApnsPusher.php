<?php
/**
 * ApnsPusher.php
 *
 * PHP Version 5
 *
 * Input File Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

namespace RSPhp\Framework;

/**
 * ApnsPusher Class Doc Comment
 *
 * Send push notifications for iOS
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
class ApnsPusher
{

    //  Must set APNS_CERTIFICATE_FILE, APNS_PASSPHRASE and APNS_GATEWAY_URL
    //  as global variables
    private static $_certificatePath = App::get('APNS_CERTIFICATE_FILE');
    private static $_passPhrase = App::get('APNS_PASSPHRASE');
    //'ssl://gateway.sandbox.push.apple.com:2195'; // change to prod
    private static $_apiGateway = App::get('APNS_GATEWAY_URL');

    /**
     * Send the message to one device
     *
     * @param String      $deviceToken The to-deliver device's token
     * @param String      $title       The message's title
     * @param String      $message     The actual message to send
     * @param Array|null  $info        An array with additional information
     * @param String|null $sound       Indicates the sound to the device
     *
     * @return void
     */
    private static function _sendToDevice(
        $deviceToken,
        $title,
        $message,
        $info = null,
        $sound = 'default'
    ) {

        //  Create the stream
        $context = stream_context_create();

        //  Set the certificate
        stream_context_set_option(
            $context,
            'ssl',
            'local_cert',
            self::$_certificatePath
        );

        //  Set the passphrase
        stream_context_set_option(
            $context,
            'ssl',
            'passphrase',
            self::$_passPhrase
        );

        // Open a connection to the APNS server
        $conn = stream_socket_client(
            self::$_apiGateway,
            $err,
            $errstr,
            60,
            STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT,
            $context
        );

        //  If cannot connect, die
        if (!$conn ) {
            throw new Exception("Failed to connect: $err $errstr");
        }

        //  Construct the payload:

        //  The alert:
        $alert['title'] = $title;
        $alert['body'] = $message;

        $payload['aps'] = array();

        if ($info != null ) {
            $payload['aps']['info'] = $info;
        } // end if $info null

        $payload['aps']['alert'] = $alert;
        $payload['aps']['sound'] = $sound;
        $payload = json_encode($payload);

        // Build the binary notification
        $msg 
            =   chr(0) .
                pack('n', 32) .
                pack('H*', $deviceToken) .
                pack('n', strlen($payload)) .
                $payload;

        // Send it to the server
        $result
            =   fwrite(
                $conn,
                $msg,
                strlen($msg)
            ); // end fwrite

        if (!$result ) {
            throw new Exception("APNS message not sent");
        }

        // Close the connection to the server
        fclose($conn);

    } // end function _sendToDevice

    /**
     * Sends the message to the devices
     *
     * @param String      $to      The device's id
     * @param String      $title   The message's title
     * @param String      $message The actual message
     * @param Array|null  $info    An array of additional data
     * @param String|null $sound   The sound send to the device
     *
     * @return void
     */
    static function send(
        $to,
        $title,
        $message,
        $info = null,
        $sound = 'default'
    ) {

        if (is_array($to) ) {
            foreach ($to as $deviceToken) {
                self::_sendToDevice(
                    $deviceToken,
                    $title,
                    $message,
                    $info,
                    $sound
                );
            }
        } else {
            self::_sendToDevice(
                $to,
                $title,
                $message,
                $info,
                $sound
            );
        } // end if then else is array
    } // end function send

} // end class ApnsPusher

