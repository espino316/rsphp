<?php
/**
 * Db.php
 *
 * PHP Version 5
 *
 * Db File Doc Comment
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
 * Simple contains the connections to databases
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */
class Db
{

    public static $connections;
    public static $dataSources;

    /**
     * Returns a datasource from it's name
     *
     * @param String $dsName The datasource's name
     *
     * @return DataSource
     */
    public static function getDataSource( $dsName )
    {
        if (isset(self::$dataSources[$dsName]) ) {
            return self::$dataSources[$dsName];
        } else {
            return null;
        } // end if then else
    } // end getDataSource

    /**
     * Returns a proper parameter from a datasource configuration parameter
     *
     * @param DataSourceParameter $param A datasource configuration parameter
     *
     * @return Assoc Array
     */
    private static function _getParameter( $param )
    {
        switch ($param->type) {
        case 'session':
            $value = Session::get($param->name);
            break;
        case 'input':
            $value = Input::get($param->name);
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
    } // end function _getParameter

    /**
     * Returns a result from a DataSource
     *
     * @param String       $dsName      The name of the datasource to query
     * @param mixed[]|null $params      The name of the datasource to query
     * @param Int|null     $pageItems   The name of the datasource to query
     * @param Int|null     $currentPage The name of the datasource to query
     *
     * @return Assoc Array
     */
    public static function getResultFromDataSource(
        $dsName,
        $params = null,
        $pageItems = null,
        $currentPage = null
    ) {

        if (! isset(self::$dataSources[$dsName])) {
            return;
        }    // end if is set dsName

        $ds = self::$dataSources[$dsName];

        if ($ds->type == 'JSON') {
            $fileName = StringHelper::replace(':ROOT', ROOT, $ds->file);
            $fileName = StringHelper::replace('/', DS, $fileName);
            $result = FileHelper::read($fileName);
            $result = json_decode($result, true);

            if ($ds->filters) {
                foreach ($ds->filters as $key => $value) {
                    $result = DbHelper::resultSetFilter($result, $key, $value);
                } // end foreach
            } // end ds Filters

            return $result;
        } // end if JSON

        $db = new DbHelper($ds->connection);
        if ($ds->type == 'SQLQUERY') {
            if ($ds->text) {
                $sql = $ds->text;
            } elseif ($ds->file) {
                $fileName = StringHelper::replace(':ROOT', ROOT, $ds->file);
                $fileName = StringHelper::replace('/', DS, $fileName);
                $sql = FileHelper::read($fileName);
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
                    if ($paramName != $param->name
                        || ( $params && array_key_exists($paramName, $params) )
                    ) {
                        continue;
                    } // end if $paramName = $param->name

                    $params[] = self::_getParameter($param);

                } // end foreach param
            } // end foreach

            if ($pageItems) {
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

} // end class
