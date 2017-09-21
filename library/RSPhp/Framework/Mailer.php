<?php
/**
 * Mailer.php
 *
 * PHP Version 5
 *
 * Mailer File Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

namespace RSPhp\Framework;

use Exception;
use PEAR;
use Mail_mime;
use Mail;

/**
 * Send emails
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
class Mailer
{

    static $to;
    static $from;
    static $subject;
    static $message;
    static $html = false;
    static $attachments = array();

    private static $_emailToVerify;
    private static $_smtpConn;
    private static $_recordLog;
    private static $_server;
    private static $_lastResponse;
    private static $_isValid = true;

    /**
     * Sets the configuration
     *
     * @param Array $config The configuration array
     *
     * @return void
     */
    static function setConfig( $config )
    {
        if ( ! is_array( $config ) ) {
            throw new Exception( "Config must be an array" );
        } // end if is_array

        if ( count( $config ) < 3 ) {
            throw new Exception( "Config must have at least three arguments" );
        } // end if config

        if ( !array_key_exists( "mailServer", $config ) ||
            !array_key_exists( "mailUser", $config ) ||
            !array_key_exists( "mailPassword", $config )
        ) {
            throw new Exception( "Config must have mailServer, mailUser, mailPassword" );
        } // end if not key exists

        foreach( $config as $key => $value ) {
            switch ( $key ) {
                case "mailServer":
                    App::set( "MAIL_SERVER", $value );
                break;
                case "mailUser":
                    App::set( "MAIL_USER", $value );
                break;
                case "mailPassword":
                    App::set( "MAIL_PWD", $value );
                break;
                case "mailPort":
                    App::set( "MAIL_PORT", $value );
                break;
            } // end switch
        } // end foreach
    } // end static function setConfig

    /**
     * Adds an attachment to the message
     *
     * @param String $file The attachment path
     *
     * @return void
     */
    static function addAttachment( $file )
    {
        if (is_array($file) ) {
            foreach ( $file as $attachment ) {
                self::$attachments[] = $attachment;
            } // end foreach
        } else {
            self::$attachments[] = $file;
        }// end if then else is array
    } // end function addAttachment

    /**
     * Send the  email
     *
     * @return void
     */
    static function send()
    {
        $host = App::get('MAIL_SERVER');
        $username = App::get('MAIL_USER');
        $password = App::get('MAIL_PWD');
        $port = App::get('MAIL_PORT');

        if ( ! $port ) {
            $port = 25;
        } // end if not port

        $headers['From'] = self::$from;
        $headers['To'] = self::$to;
        $headers['Subject'] = self::$subject;
        if (self::$html ) {
            $headers['Content-Type'] = 'text/html; charset=ISO-8859-1';
        }

        $config['host'] = $host;
        $config['auth'] = true;
        $config['username'] = $username;
        $config['password'] = $password;
        $config['port'] = $port;

        if (count(self::$attachments) ) {
            $mime = new Mail_mime("\r\n");
            if (self::$html ) {
                $mime->setHTMLBody(self::$message);
            } else {
                $mime->setTXTBody(self::$message);
            } // end if html

            foreach ( self::$attachments as $attachment ) {
                $mime->addAttachment($attachment, 'application/octet-stream');
            } // foreach

            self::$message = $mime->get();
            $headers = $mime->headers($headers);
        } // end if $attachments

        $smtp
            = Mail::factory(
                'smtp',
                $config
            );

        $mail
            = $smtp->send(
                self::$to,
                $headers,
                self::$message
            );

        //	Clear the attachments
        self::$attachments = array();

        if (PEAR::isError($mail) ) {
            throw new Exception($mail->getMessage());
        } else {
            return $mail;
        } // end if PEAR error

    } // end function send

    /**
     * Send the mail using mail()
     *
     * @return void
     */
    static function sendLocal()
    {

        if (self::$html ) {
            $headers = "From: " . self::$from . "\r\n";
            $headers .= "Reply-To: ". self::$from . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $headers .= 'X-Mailer: PHP/' . phpversion();
        } else {
            $headers = "From: " . self::$from . "\r\n" .
              'Reply-To: ' . self::$from . "\r\n" .
              'X-Mailer: PHP/' . phpversion();
        }

        mail(self::$to, self::$subject, self::$message, $headers);
    } // end function send

    /**
     * Verify an email with the _server
     *
     * @param String $email The email address to verify
     *
     * @return Assoc Array
     */
    static function verifyEmail( $email )
    {
        $verifier = new EmailVerifier($email);
        $verifier->verify();
        $result['result'] = $verifier->_isValid;
        $result['log'] = $verifier->getLog();
        return $result;
    }

} // end class Mailer
