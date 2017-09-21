<?php
/**
 * Uri.php
 *
 * PHP Version 5
 *
 * Uri File Doc Comment
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
 * Helper for Uri manipulation
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
class Uri
{

    protected static $segments;

    /**
     * Sets the segments
     *
     * @param Array $segments The array of segments
     *
     * @return void
     */
    static function setSegments( $segments )
    {
        self::$segments = $segments;
    } // end function setSegments

    /**
     * Retrieve a segment
     *
     * @param String $index The index of the segment
     *
     * @return String
     */
    static function getSegment( $index )
    {
        if (isset(self::$segments[ $index ]) ) {
            return self::$segments[ $index ];
        } else {
            return null;
        } // end if then else index exists
    } // end function getSegment

    /**
     * Get Segments
     *
     * @return Array
     */
    static function getSegments()
    {
        return self::$segments;
    } // end function getSegments

    /**
     * Retrieve the number of segments
     *
     * @return Int
     */
    static function getSegmentsLength()
    {
        if (isset(self::$segments) ) {
            return count(self::$segments);
        } else {
            return 0;
        } // end if then else is $segments set
    } // end function getSegmentsLength

    /**
     * Redirects to another page
     *
     * @param String $url        The web address to redirect
     * @param Int    $statusCode The status for the redirection
     *
     * @return void
     */
    static function redirect($url, $statusCode = 303)
    {
        $output = ob_get_contents();
        if ($output ) {
            ob_clean();
            echo '<meta http-equiv="refresh" content="0; URL=' . $url . '">';
            exit;
        } else {
            header('Location: ' . $url, true, $statusCode);
            exit;
        }

    } // end redirect

    /**
     * Redirect with POST data.
     *
     * @param String $url     URL.
     * @param Array  $data    POST data. Example: array('foo' => 'var', 'id' => 123)
     *                          Example: array('foo' => 'var', 'id' => 123)
     * @param Array  $headers Optional. Extra headers to send.
     *
     * @return void
     */
    static function redirectPost($url, array $data, array $headers = null)
    {
        $params = array(
          'http' => array(
         'method' => 'POST',
         'content' => http_build_query($data)
          )
        );
        if (!is_null($headers)) {
            $params['http']['header'] = '';
            foreach ($headers as $k => $v) {
                $params['http']['header'] .= "$k: $v\n";
            }
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if ($fp) {
            echo @stream_get_contents($fp);
            die();
        } else {
            // Error
            throw new Exception("Error loading '$url', $php_errormsg");
        }
    } // end function redirectPost

    /**
     * Verify an ip
     *
     * @param String $ip The ip address
     *
     * @return Boolean
     */
    function checkIP( $ip )
    {
        if (!empty($ip) && ip2long($ip)!=-1 && ip2long($ip)!=false) {

            $private_ips = array (
            array('0.0.0.0','2.255.255.255'),
            array('10.0.0.0','10.255.255.255'),
            array('127.0.0.0','127.255.255.255'),
            array('169.254.0.0','169.254.255.255'),
            array('172.16.0.0','172.31.255.255'),
            array('192.0.2.0','192.0.2.255'),
            array('192.168.0.0','192.168.255.255'),
            array('255.255.255.0','255.255.255.255')
             );

            foreach ($private_ips as $r) {
                $min = ip2long($r[0]);
                $max = ip2long($r[1]);
                if ((ip2long($ip) >= $min)
                    && (ip2long($ip) <= $max)
                ) {
                    return false;
                }
            }
                return true;
        } else {
             return false;
        }
    }

    /**
     * Determines the IP
     *
     * @return String
     */
    function determineIP()
    {
        if (checkIP($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }
        foreach (explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
            if (checkIP(trim($ip))) {
                return $ip;
            }
        }
        if (checkIP($_SERVER["HTTP_X_FORWARDED"])) {
             return $_SERVER["HTTP_X_FORWARDED"];
        } elseif (checkIP($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"])) {
             return $_SERVER["HTTP_X_CLUSTER_CLIENT_IP"];
        } elseif (checkIP($_SERVER["HTTP_FORWARDED_FOR"])) {
             return $_SERVER["HTTP_FORWARDED_FOR"];
        } elseif (checkIP($_SERVER["HTTP_FORWARDED"])) {
             return $_SERVER["HTTP_FORWARDED"];
        } else {
             return $_SERVER["REMOTE_ADDR"];
        }
    } // end function determineIP

    /**
     * Returns information about the request
     *
     * @return array
     */
    static function getTrackInfo()
    {
         $info['time'] = '"'.Date::now().'"';
         $info['ip'] = '"'.determineIP().'"';;
         $info['url'] = '""';
         $info['referer'] = '""';
         $info['browser'] = '""';

        if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI']) ) {
            $info['url'] = '"'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'"';
        }

        if (isset($_SERVER['HTTP_REFERER']) ) {
            $info['referer'] = '"'.$_SERVER['HTTP_REFERER'].'"';
        }

        if (isset($_SERVER['HTTP_USER_AGENT']) ) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
                $browser = 'Internet explorer';
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false) { //For Supporting IE 11
                $browser = 'Internet explorer';
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== false) {
                $browser = 'Mozilla Firefox';
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false) {
                $browser = 'Google Chrome';
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false) {
                $browser = "Opera Mini";
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false) {
                $browser = "Opera";
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false) {
                $browser = "Safari";
            } else {
                echo 'Unknown';
            }
            $info['browser'] = '"'.$browser.'"';
        }

         return $info;
    } // end getTrackInfo
} // end class Uri
