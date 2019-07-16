<?php

namespace RSPhp\Framework;

use RSPhp\Framework\Controller;
use RSPhp\Framework\View;
use RSPhp\Framework\Input;
use RSPhp\Framework\Uri;
use RSPhp\Framework\Db;
use RSPhp\Framework\Output;
use RSPhp\Framework\Logger;
use Exception;

/**
 * RestApi Controller
 */
class RestApiModelController extends Controller
{
    /**
     * @var string Stores the api endpoint, without BASE_URL
     */
    protected $endPoint;

    /**
     * @var Function Stores the function to authenticate
     */
    private $auth;

    /**
     * @var string Stores the connection name
     */
    private $connName;

    /**
     * @var array Collection of routes
     */
    public $routes;

    /**
     * @var Db The database connection to use
     */
    protected $db;

    /**
     * @var array List of allowed verbs
     */
    private $allowedVerbs = array(
        "get", "post", "put", "patch", "delete", "options"
    ); // end array

    /**
     * Initialize properties
     * @param string $endPoint The endpoint -segment- to publish the api e.g. api/v1
     * @param Function $auth The authorization function to perform on each request
     * @param string $connName The database connection name to use
     *
     * @return RestApiModelController
     */
    function initialize($endPoint, $auth = null, $connName = "default")
    {
        $this->endPoint = $endPoint .= (!Str::endsWith($endPoint, "/")) ? "/" : "";
        $this->auth = $auth;
        $this->connName = $connName;
        $this->db = new Db($this->connName);
    } // end function constructs
    /**
     * Creates a new instance of RestApiModelController
     * @param string $endPoint The endpoint -segment- to publish the api e.g. api/v1
     * @param Function $auth The authorization function to perform on each request
     * @param string $connName The database connection name to use
     *
     * @return RestApiModelController
     */
    function __construct($endPoint, $auth = null, $connName = "default")
    {
        $this->initialize($endPoint, $auth, $connName);
    } // end function constructs

    /**
     * Ends response with an error
     */
    function error($errorMessage)
    {
        Output::clean();
        Output::setStatusCode(500);
        //  MySql on error for rsMySql procedures
        $errorMessage = Str::replace('SQLSTATE[45000]: <<Unknown error>>: 55001', '', $errorMessage);
        $this->jsonResponse(array("error" => trim($errorMessage)));
    } // end function error

    /**
     * Clean the buffer,
     * Set the status code for unauthorized
     *
     * @return null
     */
    function notFound($url)
    {
        Output::clean();
        Output::setStatusCode(404);
        $this->jsonResponse(array("Not found url " => $url));
    } // end function unauthorized

    /**
     * Clean the buffer,
     * Set the status code for unauthorized
     *
     * @return null
     */
    function unauthorized()
    {
        Output::clean();
        Output::setStatusCode(401);
        $this->jsonResponse(array("authorized" => false));
    } // end function unauthorized

    /**
     * Clean the buffer,
     * Set the status code for no content
     *
     * @return null
     */
    function noContent()
    {
        Output::clean();
        Output::setStatusCode(204);
    } // end function unauthorized

    /**
     * Takes an array or object, prints its json and set success status code
     *
     * @param object|array $results The object or array to serialize
     *
     * @return null
     */
    function setResponse($results)
    {
        if ( !$results ) {
            Output::clean();
            Output::setStatusCode(404);
            return;
        } // end if not results

        Output::setStatusCode(200);
        $this->jsonResponse($results); // end jsonResponse
    } // end function setResponse

    /**
     * Stores the route in the collection for OPTIONS verb
     *
     * @param string    $uri    The uri to match
     * @param function  $fn     The function to perform
     *
     * @return null
     */
    function options($uri, $fn)
    {
        $this->routes[] = new Route("options", $uri, $fn);
    } // end function

    /**
     * Stores the route in the collection for GET verb
     *
     * @param string    $uri    The uri to match
     * @param function  $fn     The function to perform
     *
     * @return null
     */
    function get($uri, $fn)
    {
        $this->routes[] = new Route("get", $uri, $fn);
    } // end function

    /**
     * Stores the route in the collection for POST verb
     *
     * @param string    $uri    The uri to match
     * @param function  $fn     The function to perform
     *
     * @return null
     */
    function post($uri, $fn)
    {
        $this->routes[] = new Route("post", $uri, $fn);
    } // end function

    /**
     * Stores the route in the collection for PUT verb
     *
     * @param string    $uri    The uri to match
     * @param function  $fn     The function to perform
     *
     * @return null
     */
    function put($uri, $fn)
    {
        $this->routes[] = new Route("put", $uri, $fn);
    } // end function

    /**
     * Stores the route in the collection for DELETE verb
     *
     * @param string    $uri    The uri to match
     * @param function  $fn     The function to perform
     *
     * @return null
     */
    function delete($uri, $fn)
    {
        $this->routes[] = new Route("delete", $uri, $fn);
    } // end function

    /**
     * Stores the route in the collection for DELETE verb
     *
     * @param string    $uri    The uri to match
     * @param function  $fn     The function to perform
     *
     * @return null
     */
    function patch($uri, $fn)
    {
        $this->routes[] = new Route("patch", $uri, $fn);
    } // end function

    /**
     * Runs the api, meaning will checkup for method and request matches
     */
    function run()
    {
        //  Gets the verb
        $verb = Str::toLower(Input::getMethod());

        //  Verify is REST API allowed
        if (!in_array($verb, $this->allowedVerbs)) {
            //  return unauthorized
            return $this->unauthorized();
        } // end if not in array

        //  Get the current uri
        $uri = Str::replace(BASE_URL."/".$this->endPoint, "", Uri::currentUrl());
        //  Filter the routes by verb
        $routes = array_map(
            function ($route) use ($verb, $uri) {
                $matches = $route->matchUri($uri, $route->uri);
                return (($route->method == $verb) && $matches) ? array($route, $matches) : null;
            }, // end anonymous array filter function
            $this->routes
        ); // end array filter
        $routes = array_filter($routes);

        //  If no routes: unauthorized
        if (!count($routes)) {
            return $this->notFound($uri);
        } // end if no routes

        //  Take the first one
        $route = array_shift($routes);

        //  Perform the route
        $matches = $route[1];
        $route = $route[0];

        try {
            if ($route->method != 'options' && $this->auth) {
                call_user_func($this->auth);
            } // end if this auth

            if (is_array($matches)) {
                call_user_func_array($route->newUri, $matches);
            } else {
                call_user_func($route->newUri);
            } // end if array
        } catch (UnauthorizedException $ex) {
            $this->unauthorized();
            return;
        } catch (NoContentException $ex) {
            $this->noContent();
            return;
        } catch (Exception $ex) {
            Logger::error($ex->getMessage());
            $this->error($ex->getMessage());
            return;
        } // end try catch
    } // end function run

    /**
     * Runs the automatic rest api for all database tables
     * in the current connection
     *
     * @return null
     */
    function runAutomaticApi()
    {
        try {
            $this->auth();
            $db = new Db();
            $method = Input::getMethod();
            $segments = Uri::getSegments();
            $endPoint = BASE_URL."/api/v1/z2/x21/:miembro_id/";
            $endPoint = BASE_URL."/api/v1/";

            //  Get the route
            //  The parameters are fields
            //  Count the route segments
            //  Reduce the uri segments
            //  Get the table name
            //  Get the parameters

            array_shift($segments);
            array_shift($segments);
            $tableName = $segments[0];
            array_shift($segments);
            $segmentCount = count($segments);

            switch( $method ) {
                case "GET":
                    if ( $segmentCount === 0 ) {
                        $results = $db->get($tableName);
                        return $this->setResponse();
                    } // end if segment count === 0

                    //  Here we construct the query
                    //  we need the primary key for the table
                    //  and the segments to match
                    $primaryKeys = $db->getPrimaryKeys( $tableName );

                    if (count($primaryKeys) !== count($segments)) {
                        throw new Exception("Not all segments corresponding to key in table");
                    } // end if count primaryKeys <> count segments

                    $where = array();
                    foreach($primaryKeys as $i => $key) {
                        $where[$key] = $segments[$i];
                    } // end foreach primary key

                    //  Get the result
                    $results = $db->from($tableName)->where($where)->get();
                    //  Set the response
                    return $this->setResponse();
                break;
                case "POST":
                    if ( $segmentCount > 0 ) {
                        throw new Exception( "Post method must not include more url data" );
                    } // end if segmentCount > 0

                    Logger::debug("Input array");
                    Logger::debug(Input::getArray());
                    $rows = $db->insert( $tableName, Input::getArray() );
                    //  Here get id

                    $keys = $db->getPrimaryKeys( $tableName );
                    $keys = implode(",", $keys);
                    $id = $db->select($keys)->from($tableName)->where(Input::getArray())->first();
                    $id = implode("/",$id);

                    Output::setStatusCode(201);
                    Output::setHeader("Location", $endPoint . $tableName . "/" .$id );
                    return;
                break;
                case "PUT":

                    $input = Input::getArray();
                    $primaryKeys = $db->getPrimaryKeys( $tableName );

                    if (count($primaryKeys) !== count($segments)) {
                        throw new Exception("Not all segments corresponding to key in table");
                    } // end if count primaryKeys <> count segments

                    $where = array();
                    foreach( $primaryKeys as $i => $key ) {
                        $where[$key] = $segments[$i];
                    } // end foreach

                    $exists =
                        $db->select(implode(",", $where))->
                        from($tableName)->
                        where($where)->get();

                    if (!$exists) {
                        Output::setStatusCode(404);
                    } // end if not exists

                    $db->update( $tableName, $input, $where );

                    $keys = implode(",", $primaryKeys);
                    $id = $db->select($keys)->from($tableName)->where(Input::getArray())->first();
                    $id = implode("/",$id);
                    Output::setStatusCode(201);
                    Output::setHeader("Location", $endPoint . $tableName . "/" .$id );
                    return;
                break;
                case "DELETE":
                    if ( $segmentCount === 0 ) {
                        throw new Exception( "Must specify a record id to delete" );
                    } // end if segment count === 0

                    $primaryKeys = $db->getPrimaryKeys( $tableName );

                    if (count($primaryKeys) !== count($segments)) {
                        throw new Exception("Not all segments corresponding to key in table");
                    } // end if count primaryKeys <> count segments

                    $where = array();
                    foreach($primaryKeys as $i => $key) {
                        $where[$key] = $segments[$i];
                    } // end foreach primary key

                    $db->delete( $tableName, $where );
                    Output::setStatusCode(204);
                    return;
                break;
            } // end switch $method
        } catch(Exception $ex) {
            Output::setStatusCode(415);
            $this->jsonResponse(
                array(
                    "error" => true,
                    "description" => $ex->getMessage()
                ) // end array
            ); // end jsonResponse
        } // end try catch
    } // end function index
} // end class RestApiController;
