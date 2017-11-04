<?php
/**
 * DataSource.php
 *
 * Estoy modificando el archivo.
 * De nueva cuenta estoy modificando el archivo.
 * Estoy modificando el archivo.
 * De nueva cuenta estoy modificando el archivo.
 *
 * PHP Version 5
 *
 * DataSource File Doc Comment
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
 * Represents a data source
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
class DataSource
{

    public $connection;
    public $name;
    public $type;
    public $text;
    public $file;
    public $parameters;
    public $filters;

    /**
     * Creates a new instance of a datasource
     *
     * @param String $connection The connection to use
     * @param String $name       The datasource's name
     * @param String $type       The datasource's type
     * @param String $text       The datasource's text, the actual sql e.g.
     * @param String $text       The datasource's file path, may include "$root" as reference
     *
     * @return void
     */
    function __construct(
        $connection,
        $name,
        $type,
        $text = "",
        $file = ""
    ) {
        $this->connection = $connection;
        $this->name = $name;
        $this->type = $type;
        $this->text = $text;
        $this->file = $file;
    } // end class DataSource  } // end function __construct

    /**
     * Adds a parameter to the datasource
     *
     * @param String       $name         The parameter's name
     * @param String       $type         The parameter's type ( Session|Input )
     * @param mixed[]|null $defaultValue The parameter's default value
     *
     * @return void
     */
    function addParam( $name, $type, $defaultValue = null )
    {
        $param = new Parameter($name, $type, $defaultValue);
        $this->parameters[] = $param;
    }

    /**
     * Adds a filter to the datasource
     *
     * @param String  $key   The filter's name
     * @param mixed[] $value The filter's value
     *
     * @return void
     */
    function addFilter( $key, $value )
    {
        $this->filters[$key] = $value;
    } // end function addFilter

    /**
     * Returns a proper parameter from a datasource configuration parameter
     *
     * @param DataSourceParameter $param A datasource configuration parameter
     *
     * @return Assoc Array
     */
    private function getParameter( $param )
    {
        switch ( $param->type ) {
        case 'session':
            $value = Session::get( $param->name );
            break;
        case 'input':
            $value = Input::get( $param->name );
            break;
        } // end switch

        if (!$value && !$param->defaultValue ) {
            throw new Exception("Param not exists " . $paramName, 1);
        }  // end if not value

        if (!$value && $param->defaultValue ) {
            return array( $param->name => $param->defaultValue );
        }  // end if not value

        if ($value) {
            return array( $param->name => $value );
        } // end if value
    } // end function getParameter

    /**
     * Makes a web request and return the data
     *
     * @return Array
     */
    private function getHttp($params = null)
    {
        $conn = Web::getWebConnection($this->connection);
        $url = $conn->endPoint . "/" . $this->text;
        $response = Http::request($conn->method, $url, $conn->parameters, $conn->headers);
        print_r([$conn, $this, $url, $response]);

        if ($response->data) {
            return json_decode($response->data, true);
        } // end if response data
    } // end function getHttp

    /**
     * Returns a result from a DataSource
     *
     * @param mixed[]|null $params      The name of the datasource to query
     * @param Int|null     $pageItems   The name of the datasource to query
     * @param Int|null     $currentPage The name of the datasource to query
     *
     * @return Assoc Array
     */
    public function getResultSet(
        $params = null,
        $pageItems = null,
        $currentPage = null
    ) {

        if ($this->type == "HTTP") {
            return $this->getHttp($params);
        } // end if type is HTTP

        if ($this->type == 'JSON') {
            $fileName = Str::replace(':ROOT', ROOT, $this->file);
            $fileName = Str::replace('/', DS, $fileName);
            $result = File::read($fileName);
            $result = json_decode($result, true);

            if ($this->filters) {
                foreach ($this->filters as $key => $value) {
                    $result = Db::resultSetFilter($result, $key, $value);
                } // end foreach
            } // end ds Filters

            return $result;
        } // end if JSON

        $db = new Db( $this->connection );
        if ($this->type == 'SQLQUERY') {
            if ($this->text) {
                $sql = $this->text;
            } elseif ($this->file) {
                $fileName = Str::replace(':ROOT', ROOT, $this->file);
                $fileName = Str::replace('/', DS, $fileName);
                $sql = File::read($fileName);
            }

            $pattern = '/(::{1}[^:=<>\s\',;]+)/';
            $tmp = preg_replace($pattern, '', $sql);
            $pattern = "/'([^']*?)'/";
            $tmp = preg_replace($pattern, '', $tmp);
            $pattern = '/(:[^=<>\s\',;]+)/';
            preg_match_all($pattern, $tmp, $matches);
            $matches = array_unique($matches[0]);

            foreach ($matches as $match) {
                $paramName = str_replace(":", "", $match);
                foreach ($ds->parameters as $param) {
                    if ( $paramName != $param->name
                        || ( $params && array_key_exists( $paramName, $params ) )
                    ) {
                        continue;
                    } // end if $paramName = $param->name

                    $params[] = $this->getParameter( $param );

                } // end foreach param
            } // end foreach

            if ( $pageItems ) {
                if (!$currentPage) {
                    $currentPage = 1;
                }
                $result
                    = $db->paginateQuery(
                        $pageItems,
                        $currentPage,
                        $sql,
                        $params
                    );
            } else {
                $result = $db->query($sql, $params);
            } // end pageItems
        } // end if SQLQUERY

        return $result;
    } // end function getResultFromDataSource
} // end class DataSource
