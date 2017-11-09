<?php
/**
 * Input.php
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
 * Input Class Doc Comment
 *
 * Access all inputs
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
class Input
{
    /**
     * @var Array Holds the input data
     */
    protected static $data;

    /**
     * @var String Holds the Http method
     */
    protected static $method;

    /**
     * @var String Holds the Http method
     */
    protected static $headers = array();

    /**
     * @vat Holds the queryString, if any
     */
    protected static $queryString;

    /**
     * Setup the query string
     *
     * @param String $queryString The queryString
     *
     * @return Null
     */
    public static function setQueryString($queryString)
    {
        self::$queryString = $queryString;
    } // end function setQueryString

    /**
     * Return the query string
     *
     * @return String
     */
    public static function getQueryString($queryString)
    {
        return self::$queryString;
    } // end function getQueryString

    /**
     * Return a header value from a key
     *
     * @param String $headerKey The header key
     *
     * @return Mixed
     */
    static function getHeader( $headerKey = null )
    {
        if ( $headerKey ) {
            return self::$headers[strtolower($headerKey)];
        } // end if headerKey

        return self::$headers;
    } // end function getHeader

    /**
     * Returns the method
     *
     * @return String
     */
    static function getMethod()
    {
        if ( php_sapi_name() == "cli" ) {
            return "CLI";
        } // end if cli

        return strtoupper($_SERVER['REQUEST_METHOD']);
    } // end function getMethod

    /**
     * Loads the data from php://input into $data
     *
     * @return void
     */
    static function load()
    {
        $headers = array();
        if ( self::getMethod() != "CLI" ) {
            $headers = getallheaders();
        } // end if not cli get headers

        foreach( $headers as $key => $value ) {
            self::$headers[strtolower($key)] = $value;
        } // end foreach header

        $str = file_get_contents("php://input");
        parse_str($str, self::$data);
        if (count(self::$data) === 0 ) {

            //	Check if data
            if (count($_POST) > 0 ) {
                //	is multipart
                foreach ( array_keys($_POST) as $key) {
                    self::$data[$key] = $_POST[$key];
                } // end foreach
            } // end if

            //	Check if files
            if (count($_FILES) > 0 ) {
                foreach ( array_keys($_FILES) as $key) {
                    self::$data[$key] = $_FILES[$key];
                } // end foreach
            }
        } // end if
    } // end load

    /**
     * Returns a value from $key
     *
     * @param String $key The key for the array item
     *
     * @return mixed[]
     */
    static function get($key = null)
    {
        if ($key == null ) {
            return self::$data;
        } else {
            if (array_key_exists($key, self::$data)) {
                return self::$data[$key];
            } else {
                return null;
            }
        }
    } // end function get

    /**
     * Returns Json data
     *
     * @return String
     */
    static function getJson()
    {
        if ( self::getHeader("Content-Type") == "application/json" ) {
            $inputs = self::get();
            reset($inputs);
            $jsonData = key($inputs);

            if ( json_decode( $jsonData ) === null ) {
                throw new Exception("Data not serializable to json");
            } // end if json decode null

            return $jsonData;
        } else {
            $inputs = self::get();
            return json_encode( $inputs );
        } // end if application json
    } // end function getJson

    /**
     * Returns an array of values, either if the request were form, url or json
     *
     * @return Array
     */
    static function getArray() {
        if ( self::getHeader("Content-Type") == "application/json" ) {
            $inputs = self::get();
            reset($inputs);
            $jsonData = key($inputs);
            $arrayData = json_decode($jsonData, true);

            if ( $arrayData === null ) {
                throw new Exception("Data not serializable to json");
            } // end if json decode null

            return $arrayData;
        } else {
            return self::get();
        } // end if application json
    } // end function getArray

    /**
     * Saves an uploaded file
     *
     * @param String      $key        The name of the input
     * @param String|null $folder     The name of the folder to store the file
     * @param String|null $name       The name for the file to be stored
     * @param Array|null  $conditions The conditions that the file must met
     *
     * @return String
     */
    static function saveUploadedFile(
        $key,
        $folder = null,
        $name = null,
        $conditions = null
    ) {
        $file = self::get($key);

        if (!isset($file['error'])
            || is_array($file['error'])
        ) {
              throw new RuntimeException('Invalid parameters.');
        }

        // Check $_FILES['upfile']['error'] value.
        switch ( $file['error'] ) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
        }

        if (!$folder ) {
            $folder = ROOT.DS.'public'.DS.'files'.DS;
        } // end if folder

        if (!$name ) {
            $name = $file["name"];
        } // end if $name

        if (!preg_match("/(\S*)\.[a-z]{3,4}/", $name) ) {
            $ext = File::getExtension($file['name']);
            $name = $name.'.'.$ext;
        } // end not has extension

        //	Sets the destination
        $fileNameDestination = $folder.$name;

        //	Conditions: MAX_SIZE, MIME TYPES
        if ($conditions ) {
            if (isset($condictions['MAX_SIZE']) ) {
                if ($file["size"] > $conditions['MAX_SIZE'] ) {
                    throw new Exception(
                        "Max size exceed " . $condictions['MAX_SIZE']
                    );
                } // end if size > max size
            } // end if maz size
            if (isset($conditions['MIME_TYPES']) ) {
                if (is_array($conditions['MIME_TYPES']) ) {
                    $result = false;
                    foreach ( $conditions['MIME_TYPES'] as $mimeType ) {
                        if ($mimeType === $file['type'] ) {
                            $result = true;
                        } // end if mimeType
                    } // end for each mime type
                    if (! $result ) {
                        throw new Exception(
                            "Type not founded in " .
                            implode(" || ", $condictions['MIME_TYPES'])
                        );
                    } // end if not result
                } // end MIME_TYPES is array
            } // end MIME TYPES
        } // end if conditions

        if (File::exists($fileNameDestination) ) {
            File::delete($fileNameDestination);
        } // end if exists, delete

        if (!move_uploaded_file($file['tmp_name'], $fileNameDestination) ) {
            throw new Exception("Fail to move uoloaded file to destination");
        } // end if ! move_uploaded_file

        return $fileNameDestination;
    } // end function saveFile

    /**
     * Validates the input values
     *
     * @return Boolean
     */
    public static function validate( $rules ) {
        $val = new Validation();
        foreach( $rules as $key => $value ) {
            $val->addRule( $key, $value );
        } // end foreach

        if ( ! $val->validate( self::get() ) ) {
            throw new Exception(
                $val->getErrors()
            );
        } // end if validate

        return true;
    } // end function validates
} // end class Input
