<?php
/**
 * View.php
 *
 * PHP Version 5
 *
 * View File Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

namespace RSPhp\Framework;

use DOMDocument;
use DOMNode;
use DOMElement;
use DOMXPath;
use stdClass;
use Exception;

/**
 * Print HTML and manage views
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
class View
{
    protected static $vars;

    /**
     * @var The regex pattern to search fields
     */
    private static $fieldPattern = '/\$[a-zA-Z0-9]*/';

    /**
     * Set a variable value to the view
     *
     * @param String  $key   The variable's name
     * @param mixed[] $value The variable's value
     *
     * @return void
     */
    static function set($key, $value)
    {
        self::$vars[$key] = $value;
    } // end function set

    /**
     * Returns a value from a key
     *
     * @param string $key The key of the value
     *
     * @return mixed[]
     */
    static function get($key)
    {
        if (self::$vars != null ) {
            if (array_key_exists($key, self::$vars)) {
                return self::$vars[$key];
            } else {
                return null;
            }
        } else {
            return null;
        }
    } // end function get

    /**
     * Clear the variables in the case closed
     *
     * @return void
     */
    static function clearVars()
    {
        self::$vars = null;
    }

    /**
     * This function prints the populated template to reponse
     *
     * @param String $viewName The name of the view. Must be the file name without
     *         extension. It's the template
     * @param Array  $data     Array that contains key value pairs for use them in
     *         templating by simple replacing
     *
     * @throws Exception
     *
     * @return void
     */
    static function load($viewName, $data = null)
    {
        echo self::loadToString($viewName, $data);
    }

    /**
     * Converts an array to object
     *
     * @param array $array The array to convert
     *
     * @return Object
     */
    private static function _convertToObjects($array)
    {
        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = self::_convertToObjects($value);
            }
            $object->$key = $value;
        }
        return $object;
    } // end function _convertToObjects

    /**
     * Returns true if view exists
     *
     * @param String $viewName
     *
     * @return Boolean
     */
    static function exists($viewName)
    {
        $filePhp = ROOT.DS.'application'.DS.'Views'.DS.$viewName.'.php';
        $fileHtml = ROOT.DS.'application'.DS.'Views'.DS.$viewName.'.html';
        return (File::exists($filePhp) || File::exists($fileHtml));
    } // end function exists

    /**
     * This function returns the populated template as string
     *
     * @param String $viewName The name of the view. Must be the file name without
     *         extension. It's the template
     * @param Array  $data     Array that contains key value pairs for use them in
     *         templating
     *
     * @throws Exception
     *
     * @return String|void
     */
    static function loadToString($viewName, $data = null)
    {
        $filePath
            = ROOT.DS.'application'.DS.'Views'.DS.$viewName.'.php';

        $exists = File::exists( $filePath );

        if ( !$exists ) {
            $filePath
                = ROOT.DS.'application'.DS.'Views'.DS.$viewName.'.html';
            $exists = File::exists( $filePath );
        } // end if not exists

        if ( $exists ) {
            $view = self::_requireToVar($filePath);
            $view = str_replace('$baseUrl', BASE_URL, $view);
            $view = str_replace('$timestamp', time(), $view);

            //  Get the translations
            $translations = array(
                '$translate' => Translation::get()
            ); // end array translations

            //  Get the actual data
            $data = array_merge(
                ($data ? $data : array()),
                App::get(),
                $translations
            ); // end array_merge

            if ($data != null ) {
                foreach ( array_keys($data) as $itemKey ) {

                    $searchKey = $itemKey;
                    if (!Str::startsWith($itemKey, "$")) {
                        $searchKey = "$$itemKey";
                    } // end if not starts with $

                    if (is_array($data[$itemKey]) ) {

                        //  Get the array
                        $dataItem = $data[$itemKey];

                        //  Loop
                        foreach ($dataItem as $key => $value) {
                            if (!is_array($value)) {
                                $view
                                    = str_replace(
                                        $itemKey."[\"".$key."\"]",
                                        $value,
                                        $view
                                    );
                            } // end if not is array value
                        } // end foreach
                    } else if (is_object($data[$itemKey]) ) {
                        $properties = get_object_vars($data[$itemKey]);

                        foreach ( array_keys($properties) as $key ) {

                            if (is_object($properties[$key]) ) {
                                $subProperties
                                    = get_object_vars($properties[$key]);

                                foreach (
                                    array_keys($subProperties) as $subKey
                                ) {
                                    $view
                                        = str_replace(
                                            $itemKey."->".$key."->".$subKey,
                                            $subProperties[$subKey],
                                            $view
                                        );
                                }

                            } else {
                                $view
                                    = str_replace(
                                        $itemKey."->".$key,
                                        $properties[$key],
                                        $view
                                    );
                            } // end if is object else
                        } // end foreach
                    } else {
                        $view = str_replace($searchKey, $data[$itemKey], $view);
                    } // end if then else
                } // end foreach
            } // end if data
            $view = self::dataBind($view, $data);
            $view = Str::specialCharsToHTML($view);
            $view = Str::replace( self::$_xmlTag, "", $view );
            return $view;
        } else {
            throw new Exception("View $filePath does not exists.");
        } // end if file do not exists
    } // end function loadToString

    /**
     * Returns a file as a variable. It may be a php script that will be evaluated
     *
     * @param String $file The file path to return as a variable
     *
     * @return String
     */
    private static function _requireToVar($file)
    {
        ob_start();
        include $file;
        return ob_get_clean();
    } // end function _requireToVar

    /**
     * Populates a template of data
     *
     * @param String     $template The html template fo pupulate
     * @param Array|null $data     The population variables
     *
     * @return void
     */
    static function populateTemplate( $template, $data = null )
    {

        $template = str_replace('$baseUrl', BASE_URL, $template);

        if ($data != null ) {
            foreach ( array_keys($data) as $itemKey ) {
                if (Str::contains($itemKey, "$") ) {
                    if (is_object($data[$itemKey]) ) {

                        $properties = get_object_vars($data[$itemKey]);

                        foreach ( array_keys($properties) as $key ) {
                            $template
                                = str_replace(
                                    $itemKey."->".$key,
                                    $properties[$key],
                                    $template
                                );
                        }
                    } else if (is_array($data[$itemKey]) ) {
                        foreach ( array_keys($data[$itemKey]) as $key ) {
                            if (is_array($data[$itemKey][$key]) ) {
                                break;
                            }
                            $template
                                = str_replace(
                                    $itemKey."[".$key."]",
                                    $data[$itemKey][$key],
                                    $template
                                );
                        }
                    } else {
                        $template
                            = str_replace(
                                $itemKey,
                                $data[$itemKey],
                                $template
                            );
                    } // end if is object
                }
            } // end foreach
        } // end if data null

        return $template;
    } // end static function populateTemplate

    /**
     * Parse an attribute and copy to another element
     *
     * @param DOMElement $origin The origin element
     * @param DOMElement $destination The destination element
     * @param String $attributeName The attribute's name
     * @param Array $row The datarow containing the variables name
     */
    private static function parseAttribute($origin, $destination, $attributeName, $row) {

        //  Get value
        $value = $origin->getAttribute($attributeName);

        //  Parse fields
        $fields = Str::pregMatchAll(self::$fieldPattern, $value);

        //  Loop every field
        foreach ($fields as $field) {

            //  Get the column name
            $colName = Str::replace('$', '', $field);

            //  If is a row
            if (isset($row[$colName])) {
                $value = Str::replace($field, $row[$colName], $value);
            } else {
                $value = Str::replace($field, "", $value);
            }   // end if row[field]
        } // end foreach

        //  Set the attribute
        $destination->setAttribute($attributeName, $value);
    } // end function parseAttribute

    private static function getDataBindSelect($dataBindItem, $data = null)
    {
        if ($dataBindItem->hasAttribute('data-source')
            && $dataBindItem->hasAttribute('data-display-field')
            && $dataBindItem->hasAttribute('data-value-field')
        ) {
            if ($data) {
                $dataBindItem->data = $data;
            } // end if data

            $dataSelect = self::getResultFromDataSourcedElement($dataBindItem);
            $value = '';

            if ($dataBindItem->hasAttribute('value') ) {
                $value = $dataBindItem->getAttribute('value');
            } // end if hasAttribute value

            if ($dataBindItem->hasAttribute('data-bind') ) {
                if ($dataSelect) {
                    $valueField = $dataBindItem->getAttribute('data-bind');
                    $value = $data[$valueField];
                } // end if data

                if (Input::get($valueField)) {
                    $value = Input::get($valueField);
                } // end if input
            } // end if hasAttribute value

            $attributes = null;
            if ($dataBindItem->hasAttribute('onchange') ) {
                $attributes["onchange"] = $dataBindItem->getAttribute('onchange');
            } // end if hasAttribute value

            if ($dataBindItem->hasAttribute('required') ) {
                $attributes["required"] = $dataBindItem->getAttribute('required');
            } // end if hasAttribute value

            $defaultBlank = false;
            if ($dataBindItem->hasAttribute('data-default-blank') ) {
                $isBlank = $dataBindItem->getAttribute('data-default-blank');
                if ($isBlank == "true") {
                    $defaultBlank = true;
                } // end if defaultBlank
            } // end if hasAttribute value

            //	Get the SELECT
            $select
                = Html::formSelect(
                    $dataBindItem->getAttribute('id'),
                    $dataSelect,
                    $dataBindItem->getAttribute('data-value-field'),
                    $dataBindItem->getAttribute('data-display-field'),
                    $value,
                    true,
                    $attributes,
                    $defaultBlank
                );

            $node = self::getDomNode($select);
            return $node;
        } // end if has attributes
        //  Finish databind to select
        return $dataBindItem;
    } // end function getDataBindSelect

    /**
     * Returns a populated node
     *
     * @param DOMNode $element The element to repeat
     * @param array The data to use for the repeater
     *
     * @return string
     */
    private static function getRepeater($element, $data) {
        $html = self::domHtml($element);
        $newHtml = "";
        $dataSource = $element->getAttribute('data-repeater');
        $data = ($data[$dataSource]) ? $data[$dataSource] : $data;

        foreach ($data as $item) {
            $newHtml .= self::populateTemplate($innerHTML, $data);
        } // end function

        return $newHtml;
    } // end function

    /**
     * Binds all selects in $html
     *
     * @param String     $html The HTML to parse
     * @param Array|null $data The arrya containing info for databind
     *
     * @return void
     */
    private static function viewsDataBind( $html, $data = null )
    {

        $dom = self::getDomFromHTML($html);

        //	Sections
        $items = $dom->getElementsByTagName('section');
        $count = $items->length - 1;

        if ($count > -1 ) {
            $toReplace = array();

            while ( $count > -1 ) {
                $item = $items->item($count);
                if ($item->hasAttribute('data-view')
                ) {

                    $viewName = $item->getAttribute('data-view');
                    if (Str::contains($viewName, '$') ) {
                        if (isset($data[$viewName]) ) {
                            $viewName = $data[$viewName];
                        } // end if is set data [ viewName ]
                    } // end if contains $

                    $view = View::loadToString($viewName, $data);
                    //	Replacement
                    $replacement = null;
                    $replacement['search'] = $item->ownerDocument->saveHTML($item);
                    $replacement['replace'] = $view;
                    $toReplace[] = $replacement;
                } // end if has attributes
                $count--;
            } // end while section

            if (!empty($toReplace) ) {
                foreach ( $toReplace as $replacement ) {
                    $old = $replacement['search'];
                    $new = $replacement['replace'];
                    $html = str_replace($old, $new, $html);
                } // end foreach $replacement
            } // end if $toReplace not empty
        } // end if count > -1

        /* Here begins data-source attribute for divs */
        $isFragment = true;
        if (Str::contains($html, '<html') ) {
            $isFragment = false;
        } // end if contain html

        //  Get the dom
        $dom = self::getDomFromHTML($html);

        //  Set the xpath object
        $xPath = new DOMXPath($dom);

        //  Get all divs with data source
        $items = $xPath->query("//*[@data-source]");

        //  Loop throug items
        for ($i = 0; $i < $items->length; $i++) {

            //  Get the first item
            $item = $items->item($i);

            if ($item->tagName == "table") {
                continue;
            } // end if table, leave it to _tableDataBind

            $data = self::getResultFromDataSourcedElement($item);

            for ($iattribute = 0; $iattribute < $item->attributes->length; $iattribute++) {
                $aValue = $item->attributes[$iattribute]->value;
                $aName = $item->attributes[$iattribute]->name;
                if (Str::contains($aValue, "@")) {
                    preg_match_all(
                        "/\@.[^\/,-,+,\$,\%,!,#,&,(,),?,¿,¡,~]*/",
                        $aValue,
                        $matches
                    ); // end preg_match_all

                    if ($matches) {
                        $matches = $matches[0];
                        foreach($matches as $match) {
                            $aKey = Str::replace("@", "", $match);
                            $realValue = $data[0][$aKey];
                            $aValue = Str::replace($match, $realValue, $aValue);
                        } // end foreach matches
                        $item->setAttribute($aName, $aValue);
                    } // end if matches
                } // end if @
            } // end for i attribute
            //  Get the template item
            $templateItem = $xPath->query("*[@data-template='true']", $item);

            //  Only if exists
            if ($templateItem->length > 0) {

                //  Get the first item only one template
                $templateItem = $templateItem->item(0);

                //  Get all controls data binded
                $dataBinds = $xPath->query("*[@data-bind]", $templateItem);

                //  Get the number or controls
                $len = $dataBinds->length;

                //  Loop the rows in the data source
                foreach ($data as $row) {

                    //  Create a div
                    $div = $dom->createElement($templateItem->tagName);

                    //  Set class, onclick y href
                    if ($templateItem->hasAttribute("class")) {
                        self::parseAttribute($templateItem, $div, "class", $row);
                    } // end if has attribute class

                    if ($templateItem->hasAttribute("onclick")) {
                        self::parseAttribute($templateItem, $div, "onclick", $row);
                    } // end if has attribute class

                    if ($templateItem->hasAttribute("href")) {
                        self::parseAttribute($templateItem, $div, "href", $row);
                    } // end if has attribute class

                    //  Loop the databinded controls
                    for ($i = 0; $i < $len; $i++) {

                        //  Get the data binded control
                        $dataBindItem = $dataBinds->item($i);

                        //  Get the filed is data binded
                        $dataFieldName = $dataBindItem->getAttribute("data-bind");

                        //  Create a text node
                        $textNode = $dom->createTextnode($row[$dataFieldName]);

                        //  Create a new element
                        $element = $dom->createElement($dataBindItem->tagName);

                        //  Updating it the text not
                        $element->appendChild($textNode);
                        $div->appendChild($element);
                    } // end for

                    //  Add the div to the original data-sourced item
                    $item->appendChild($div);
                } // end foreach

                //  Remove the data-template
                $item->removeChild($templateItem);
            } else {

                if ($item->tagName == "select") {
                    $node = @$dom->importNode(self::getDataBindSelect($item), true);
                    $item->parentNode->replaceChild($node, $item);
                    continue;
                } // end if

                //  Get all controls data binded
                $dataBinds = $xPath->query("*[@data-bind]", $item);

                //  Get the number or controls
                $len = $dataBinds->length;

                //  Loop the rows in the data source
                foreach ($data as $row) {

                    //  Loop the databinded controls
                    for ($i = 0; $i < $len; $i++) {

                        //  Get the data binded control
                        $dataBindItem = $dataBinds->item($i);

                        if ($dataBindItem->tagName == "select") {
                            //  Here we do databind to select
                            $node = @$dom->importNode(self::getDataBindSelect($dataBindItem, $row), true);
                            $dataBindItem->parentNode->replaceChild($node, $dataBindItem);
                            continue;
                        } // end if select

                        //  Get the filed is data binded
                        $dataFieldName = $dataBindItem->getAttribute("data-bind");

                        //  Create a new element
                        $element = $dom->createElement($dataBindItem->tagName);

                        if ($dataBindItem->tagName == "input") {
                            $dataBindItem->setAttribute("value", $row[$dataFieldName]);
                        } else {
                            $textNode = $dom->createTextnode($row[$dataFieldName]);
                            $element->appendChild($textNode);
                            $dataBindItem->appendChild($element);
                        } // end if input
                        //  Create a text node
                    } // end for
                } // end foreach
            } // end if template item

            $item->removeAttribute("data-source");
            $item->removeAttribute("data-filter");
        } // end for

        //  Save the dom
        $html = $dom->saveHTML();

        /*	Here begins data-bind attibute */
        $isFragment = true;
        if (Str::contains($html, '<html') ) {
            $isFragment = false;
        }

        $toReplace = null;
        $dom = self::getDomFromHTML($html);

        $items = $dom->getElementsByTagName('input');
        $count = $items->length - 1;

        if ($count > -1 ) {
            while ( $count > -1 ) {
                $item = $items->item($count);
                if ($item->hasAttribute('data-bind')
                ) {
                    $name = $item->getAttribute('data-bind');

                    if (isset($data[$name]) ) {
                              $replacement = null;
                              $replacement['search']
                                  = $item->ownerDocument->saveHTML($item);

                              $item->setAttribute('value', $data[$name]);

                              $replacement['replace']
                                  = $item->ownerDocument->saveHTML($item);
                              $toReplace[] = $replacement;
                    } else if (Str::contains($name, "[") ) {
                                 $arr = explode("[", $name);
                                 $key1 = $arr[0];
                                 $key2 = Str::replace("]", "", $arr[1]);
                        if (isset($data[$key1][$key2]) ) {
                            $replacement = null;
                            $replacement['search']
                                = $item->ownerDocument->saveHTML($item);

                            $item->setAttribute('value', $data[$key1][$key2]);

                            $replacement['replace']
                                = $item->ownerDocument->saveHTML($item);
                            $toReplace[] = $replacement;
                        } // end if isset
                    } // end if data[name]
                } // end if has attributes
                $count--;
            } // end while item data-source

            //  Data repeaters
            $items = $xPath->query("//*[@data-repeater]");
            for ($i = 0; $i < $items->length; $i++) {
                //  Get the first item
                $item = $items->item($i);
                $replacement = null;
                $replacement['search'] = $item->ownerDocument->saveHTML($item);
                $replacement['replace'] = self::getReapeater($item, $data);
                $toReplace[] = $replacement;
            } // end for each item

            if (!empty($toReplace) ) {
                foreach ( $toReplace as $replacement ) {
                    $old = $replacement['search'];
                    $new = $replacement['replace'];
                    $html = str_replace($old, $new, $html);
                } // end foreach $replacement
            } // end if $toReplace not empty

            if ($isFragment ) {
                $body = $dom->getElementsByTagName('body');
                $body = $body->item(0);
                $html = self::domInnerHTML($body);
            } else {
                $dom->formatOutput = true;
                $html = $dom->saveHTML();
            } // end if isFragment

        } // end if $count > -1

        // Spans
        $toReplace = null;
        $dom = self::getDomFromHTML($html);
        $items = $dom->getElementsByTagName('span');

        $count = $items->length - 1;

        if ($count > -1 ) {
            while ( $count > -1 ) {
                $item = $items->item($count);
                if ($item->hasAttribute('data-bind')
                ) {
                    $name = $item->getAttribute('data-bind');
                    if (isset($data[$name]) ) {
                              $replacement = null;
                              $replacement['search']
                                  = $item->ownerDocument->saveHTML($item);

                              $textNode = $dom->createTextnode($data[$name]);
                              $item->appendChild($textNode);

                              $replacement['replace']
                                  = $item->ownerDocument->saveHTML($item);
                              $toReplace[] = $replacement;
                    } // end if data[name]
                } // end if has attributes
                $count--;
            } // end while item data-source

            if (!empty($toReplace) ) {
                foreach ( $toReplace as $replacement ) {
                    $old = $replacement['search'];
                    $new = $replacement['replace'];
                    $html = str_replace($old, $new, $html);
                } // end foreach $replacement
            } // end if $toReplace not empty

            if ( $isFragment ) {
                $body = $dom->getElementsByTagName('body');
                $body = $body[0];
                $html = self::domInnerHTML($body);
            } else {
                $dom->formatOutput = true;
                $html = $dom->saveHTML();
            }
        } // end if $count > -1

        return $html;
    } // end function selectsDataBind


    /**
     * Binds all selects in $html
     *
     * @param String     $html The HTML to parse
     * @param Array|null $data The arrya containing info for databind
     *
     * @return void
     */
    private static function selectsDataBind( $html, $data = null )
    {
        $sourceData = $data;
        $isFragment = true;
        if (Str::contains($html, '<html') ) {
            $isFragment = false;
        }

        $dom = self::getDomFromHTML($html);

        //	DataSource Selects
        $items = $dom->getElementsByTagName('select');
        $count = $items->length - 1;

        if ($count == -1 ) {
            return $html;
        }

        $toReplace = array();

        while ( $count > -1 ) {

            $item = $items->item($count);

            if ($item->hasAttribute('data-source')
                && $item->hasAttribute('data-display-field')
                && $item->hasAttribute('data-value-field')
            ) {

                $data = self::getResultFromDataSourcedElement($item);

                $value = '';
                if ($item->hasAttribute('value') ) {
                    $value = $item->getAttribute('value');
                } // end if hasAttribute value

                if ($item->hasAttribute('data-bind') ) {
                    if ($data) {
                        $value = $item->getAttribute('data-bind');
                        $value = $data[$value];
                    } // end if data
                } // end if hasAttribute value

                $attributes = null;
                if ($item->hasAttribute('onchange') ) {
                    $attributes["onchange"] = $item->getAttribute('onchange');
                } // end if hasAttribute value

                if ($item->hasAttribute('required') ) {
                    $attributes["required"] = $item->getAttribute('required');
                } // end if hasAttribute value

                $defaultBlank = false;
                if ($item->hasAttribute('data-default-blank') ) {
                    $isBlank = $item->getAttribute('data-default-blank');
                    if ($isBlank == "true") {
                        $defaultBlank = true;
                    } // end if defaultBlank
                } // end if hasAttribute value

                //	Get the SELECT
                $select
                    = Html::formSelect(
                        $item->getAttribute('id'),
                        $data,
                        $item->getAttribute('data-value-field'),
                        $item->getAttribute('data-display-field'),
                        $value,
                        true,
                        $attributes,
                        $defaultBlank
                    );

                //	Replacement
                $replacement = null;
                $replacement['parent'] = $item->parentNode;
                $replacement['search'] = $item;
                $replacement['replace'] = self::getDomNode( $select );
                $toReplace[] = $replacement;
            } // end if has attributes
            $count--;
        } // end while item data-source

        if (!empty($toReplace) ) {
            foreach ( $toReplace as $replacement ) {
                $old = $replacement['search'];
                $new = $replacement['replace'];
                $parent = $replacement['parent'];
                $new = @$parent->ownerDocument->importNode( $new, true );
                $parent->replaceChild( $new, $old );
            } // end foreach $replacement
        } // end if $toReplace not empty

        if ($isFragment ) {
            $body = $dom->getElementsByTagName('body');
            $body = $body[0];
            $html = self::domInnerHTML($body);
        } else {
            $dom->formatOutput = true;
            $html = $dom->saveHTML();
        }

        return $html;
    } // end function selectsDataBind

    /**
     * Returns and attribute form a DOM Node
     *
     * @param DOMElement $element       The element to substract the attribute
     * @param String     $attributeName The atribute's name
     * @param mixed[]    $defaultValue  The value to return if there is no attribute
     *
     * @return mixed[]
     */
    public static function domGetAttribute(
        $element,
        $attributeName,
        $defaultValue
    ) {
        if ($element->hasAttribute($attributeName) ) {
            return $element->getAttribute($attributeName);
        } else {
            return $defaultValue;
        }
    } // end function domGetAttribute

    private static function getResultFromDataSourcedElement($item, $pageItems = null, $currentPage = null)
    {
        //	Get the data
        $dsName = $item->getAttribute('data-source');

        //  Get the filters
        $filters = $item->getAttribute("data-filters");

        //   If it's a table
        if ( Str::startsWith($dsName, "table:")) {

            //  Instantiate Db default
            $db = new Db();

            //  Get the table name
            $table = Str::replace("table:", "", $dsName);

            //  If any filter
            if ($filters) {

                //  Get the filters
                $filters = explode(",", $filters);

                //  There always segment 3 at start controller/function/segment
                $segment = 2;

                //  For each filter
                foreach ($filters as $filter) {

                    //  Get the condition
                    $condition = explode(":", $filter);

                    //  TODO: Implement fallback, only if not get the first, do the next

                    //  If more than one item
                    if (count($condition) > 1) {
                        //  Options
                        switch ($condition[0]) {
                            case "session":
                                if (Session::get($condition[1])) {
                                    $db->where($condition[1], Session::get($condition[1]));
                                } // end if session
                                break;
                            case "input":
                                RS::debug("hit input", $condition);
                                if (Input::get($condition[1])) {
                                    $db->where($condition[1], Input::get($condition[1]));
                                } // end if session
                                break;
                            case "segment":
                                if (Uri::getSegment($segment)) {
                                    $db->where($condition[1], Uri::getSegment($segment));
                                } // end if session
                                $db->where($condition[1], Uri::getSegment($segment));
                                $segment++;
                                break;

                            case "data":
                                RS::debug("hit data", $condition);
                                if ($item->data[$condition[1]]) {
                                    $db->where($condition[1], $item->data[$condition[1]]);
                                } // end if condition 1
                                break;
                        } // end switch
                    } else {

                        //  If in input
                        if (Input::get($condition[0])) {

                            //  Set where on input
                            $db->where($condition[0], Input::get($condition[0]));

                        //  Else id session
                        } else if (Session::get($condition[0])) {

                            //  Set where on session
                            $db->where($condition[0], Input::get($condition[0]));
                        } // end if then else input or session
                        //  If in session
                    } // end if count> 1
                } // end foreach

                $result = $db->get($table);
            } else {
                //  Get the result
                $result = $db->get($table);
            } // end if then else filters
        } else {

            //  Get the datasource
            $ds = Db::getDataSource( $dsName );

            //  If any filter
            if ($filters) {

                //  Get the filters
                $filters = explode(",", $filters);

                //  There always segment 3 at start controller/function/segment
                $segment = 2;

                //  For each filter
                foreach ($filters as $filter) {
                    //  Get the condition
                    $condition = explode(":", $filter);
                    //  If more than one item
                    if (count($condition) > 1) {
                        //  Options
                        switch ($condition[0]) {
                            case "session":
                                $ds->addParam($condition[1], "session");
                                break;
                            case "input":
                                $ds->addParam($condition[1], "input");
                                break;
                            case "segment":
                                if (Uri::getSegment($segment)) {
                                    $ds->addParam($condition[1], "segment", Uri::getSegment($segment));
                                    $segment++;
                                } // end if uri segment
                                break;
                        } // end switch
                    } else {

                        //  If in input
                        if (Input::get($condition[0])) {

                            //  Set where on input
                            $ds->addParam($condition[0], "input");
                        //  Else id session
                        } else if (Session::get($condition[0])) {

                            //  Set where on session
                            $ds->addParam($condition[0], "session");
                        } // end if then else input or session
                        //  If in session
                    } // end if count> 1
                } // end foreach
            } // end if then else filters

            $result = $ds->getResultSet(null, $pageItems, $currentPage);
        } // end if then else starts with table

        return $result;
    } // end function getResultFromDataBindedControl

    /**
     * Binds all selects in $html
     *
     * @param String $html The HTML to parse
     *
     * @return void
     */
    private static function tablesDataBind( $html )
    {

        $isFragment = true;
        if (Str::contains($html, '<html') ) {
            $isFragment = false;
        }

        $dom = self::getDomFromHTML($html);

        //	DataSource Selects
        $items = $dom->getElementsByTagName('table');
        $count = $items->length - 1;

        if ($count == -1 ) {
            //	Nothing to do
            return $html;
        }

        $toReplace = array();

        while ( $count > -1 ) {
            $item = $items->item($count);
            if ($item->hasAttribute('data-source')
            ) {
                $options = null;
                $columns = null;

                $id = self::domGetAttribute($item, 'id', 'table'.time());
                $pagination = self::domGetAttribute($item, 'data-pagination', null);
                $pageItems = self::domGetAttribute($item, 'data-page-items', null);
                $currentPageSegment
                    = self::domGetAttribute(
                        $item,
                        'data-current-page-segment',
                        null
                    );

                $currentPage = Uri::getSegment($currentPageSegment);

                $paginationUrl
                    = self::domGetAttribute($item, 'data-pagination-url', null);

                $options['id'] = $id;
                $options['pagination'] = $pagination;
                $options['page_items'] = $pageItems;
                $options['current_page'] = $currentPage;
                $options['pagination_url'] = $paginationUrl;
                array_filter($options);

                $result = self::getResultFromDataSourcedElement($item, $pageItems, $currentPage);

                if ( $pagination ) {
                       $data = $result['results'];
                       $options['pages'] = $result['pages'];
                } else {
                         $data = $result;
                } // end if pagination

                if (isset($item->firstChild) ) {
                    $tr = $item->firstChild;
                    if ($tr->tagName == 'tr' ) {

                        $options["attributes"]["class"] = "table table-hover";
                        $ths = $tr->getElementsByTagName('th');
                        foreach ($ths as $th) {

                            if ($th->hasAttribute('data-field-type') ) {
                                $type = $th->getAttribute('data-field-type');
                            } else {
                                $type = 'text';
                            } // end if then else data-field-type

                            switch ( $type ) {
                            case 'hyperlink':
                                $column = array();
                                $column["name"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-name',
                                        self::domGetAttribute($th, 'data-field', '')
                                    );
                                $column["type"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-field-type',
                                        'hyperlink'
                                    );
                                $column["header"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-header',
                                        $column['name']
                                    );
                                $column["text"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-text',
                                        null
                                    );
                                $column["visible"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-visible',
                                        null
                                    );

                                $column["url_format"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-url-format',
                                        null
                                    );
                                $urlFields
                                    = explode(
                                        ",",
                                        self::domGetAttribute(
                                            $th,
                                            'data-url-fields',
                                            ''
                                        )
                                    );
                                $column["url_fields"] = $urlFields;

                                $column["onclick_format"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-onclick-format',
                                        null
                                    );
                                $onclickFields
                                    = explode(
                                        ",",
                                        self::domGetAttribute(
                                            $th,
                                            'data-onclick-fields',
                                            ''
                                        )
                                    );
                                $column["onclick_fields"] = $onclickFields;

                                array_filter($column);
                                $columns[] = $column;
                                break;

                            case 'text':
                                $column = array();
                                $column["name"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-name',
                                        self::domGetAttribute(
                                            $th,
                                            'data-field',
                                            ''
                                        )
                                    );
                                $column["type"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-field-type',
                                        'text'
                                    );
                                $column["header"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-header',
                                        $column['name']
                                    );
                                $column["format"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-format-text',
                                        null
                                    );
                                $column["visible"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-visible',
                                        null
                                    );
                                array_filter($column);
                                $columns[] = $column;
                                break;

                            case 'textbox':
                                $column = array();
                                $column["name"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-name',
                                        self::domGetAttribute(
                                            $th,
                                            'data-field',
                                            ''
                                        )
                                    );
                                $column["type"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-field-type',
                                        'textbox'
                                    );
                                $column["header"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-header',
                                        $column['name']
                                    );
                                $column["format"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-format-text',
                                        null
                                    );
                                $column["visible"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-visible',
                                        null
                                    );
                                array_filter($column);
                                $columns[] = $column;
                                break;

                            case 'hidden':
                                $column = array();
                                $column["name"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-name',
                                        self::domGetAttribute(
                                            $th,
                                            'data-field',
                                            ''
                                        )
                                    );
                                $column["type"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-field-type',
                                        'hidden'
                                    );
                                $column["header"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-header',
                                        $column['name']
                                    );
                                $column["format"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-format-text',
                                        null
                                    );
                                $column["visible"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-visible',
                                        null
                                    );
                                array_filter($column);
                                $columns[] = $column;
                                break;

                            case 'textarea':
                                $column = array();
                                $column["name"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-name',
                                        self::domGetAttribute(
                                            $th,
                                            'data-field',
                                            ''
                                        )
                                    );
                                $column["type"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-field-type',
                                        'textarea'
                                    );
                                $column["header"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-header',
                                        $column['name']
                                    );
                                $column["format"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-format-text',
                                        null
                                    );
                                $column["visible"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-visible',
                                        null
                                    );
                                array_filter($column);
                                $columns[] = $column;
                                break;

                            case 'image':
                                $column = array();
                                $column["name"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-name',
                                        self::domGetAttribute(
                                            $th,
                                            'data-field',
                                            ''
                                        )
                                    );
                                $column["type"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-field-type',
                                        'image'
                                    );
                                $column["header"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-header',
                                        $column['name']
                                    );
                                $column["url"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-img-src',
                                        null
                                    );
                                $column["height"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-img-height',
                                        null
                                    );
                                $column["width"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-img-width',
                                        null
                                    );
                                $column["visible"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-visible',
                                        null
                                    );
                                $column["url_format"]
                                    = self::domGetAttribute(
                                        $th,
                                        'data-url-format',
                                        null
                                    );
                                $urlFields
                                    = explode(
                                        ",",
                                        self::domGetAttribute(
                                            $th,
                                            'data-url-fields',
                                            ''
                                        )
                                    );
                                $column["url_fields"] = $urlFields;
                                $columns[] = $column;
                                break;

                            case 'select':

                                $dsName
                                    = self::domGetAttribute(
                                        $th,
                                        'data-source',
                                        null
                                    );
                                $selectData
                                    = Db::getDataSource( $dsName )->getResultSet();

                                if ($selectData ) {

                                    $column = array();
                                    $column["name"]
                                        = self::domGetAttribute(
                                            $th,
                                            'data-name',
                                            self::domGetAttribute(
                                                $th,
                                                'data-field',
                                                ''
                                            )
                                        );
                                    $column["type"]
                                        = self::domGetAttribute(
                                            $th,
                                            'data-field-type',
                                            'select'
                                        );
                                    $column["header"]
                                        = self::domGetAttribute(
                                            th,
                                            'data-header',
                                            $column['name']
                                        );
                                    $column["visible"]
                                        = self::domGetAttribute(
                                            $th,
                                            'data-visible',
                                            null
                                        );
                                    $column["value_field"]
                                        = self::domGetAttribute(
                                            $th,
                                            'data-value-field',
                                            null
                                        );
                                    $column["display_field"]
                                        = self::domGetAttribute(
                                            $th,
                                            'data-display-field',
                                            null
                                        );
                                    $column['data'] = $selectData;
                                    array_filter($column);
                                    $columns[] = $column;

                                } else {
                                    throw
                                        new Exception(
                                            "Select field in table requieres " .
                                            "data-source attribute",
                                            1
                                        );
                                }

                                break;

                            default:
                                // Treats it as test
                                break;
                            } // end swith type
                        } // end for each $th

                        $options['columns'] = $columns;
                    } // end if tr
                } // end if isset

                //	Get the SELECT
                $dataTable
                    = Html::dataTable(
                        $data,
                        $options,
                        true
                    );

                //  If there's data
                if ($dataTable) {

                    //	Replacement
                    $replacement = null;
                    $replacement['parent'] = $item->parentNode;
                    $replacement['search'] = $item;
                    $replacement['replace'] = self::getDomNode( $dataTable );
                    $toReplace[] = $replacement;
                } // end if $dataTable
            } // end if has attributes
            $count--;
        } // end while item data-source

        if (!empty($toReplace) ) {
            foreach ( $toReplace as $replacement ) {
                $old = $replacement['search'];
                $new = $replacement['replace'];
                $parent = $replacement['parent'];
                $new = $parent->ownerDocument->importNode( $new, true );
                $parent->replaceChild( $new, $old );
            } // end foreach $replacement
        } // end if $toReplace not empty

        if ($isFragment ) {
            $body = $dom->getElementsByTagName('body');
            $body = $body[0];
            $html = self::domInnerHTML($body);
        } else {
            $dom->formatOutput = true;
            $html = $dom->saveHTML();
        }

        return $html;
    } // end function selectsDataBind

    /**
     * Performs data bind activities
     *
     * @param String     $html The HTML to parse
     * @param Array|null $data The data array to use for data binding
     *
     * @return void
     */
    public static function dataBind( $html, $data = null )
    {
        //	Remove strings, repopulate scripts at the end
        $html = self::viewsDataBind($html, $data);
        $html = self::tablesDataBind($html);
        return $html;
    } // end function dataBind


    /**
     * Returns the html from a DOM element
     *
     * @param DOMNode $element The DOM element for return its HTML
     *
     * @return String
     */
    public static function domHTML(DOMNode $element) {
        $element->ownerDocument->formatOutput = true;
        return $element->ownerDocument->saveHTML($child);
    } // end function dom html

    /**
     * Returns the inner html from a DOM element
     *
     * @param DOMNode $element The DOM element for return its inner HTML
     *
     * @return String
     */
    public static function domInnerHTML(DOMNode $element)
    {
         $innerHTML = "";
         $children  = $element->childNodes;

         foreach ($children as $child) {
             $element->ownerDocument->formatOutput = true;
             $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

         return $innerHTML;
    } // end function DOMinnerHTML

    public static function getDomNode( $html )
    {
        $dom = new DOMDocument();
        $dom->loadHTML( "<html>$html</html>" );
        $body = $dom->getElementsByTagName('body');
        $body = $body[0];
        return $body->firstChild;
    } // end public static function getDomNode

    /**
     * Returns a DOM Document from HTML string
     *
     * @param String $html The html to construct the DOM Document
     *
     * @return DOMDocument
     */
    public static function getDomFromHTML( $html )
    {
        $dom = new DOMDocument();
        $prefix = self::$_xmlTag;
        if (!Str::startsWith($html, $prefix) ) {
            $html = $prefix."\n".$html;
        }
        @$dom->loadHTML($html);
        return $dom;

        /*
        // TODO Strip <script> tags to ignore them
        <script([^']*?)<\/script>
        https://regex101.com/r/CK7evj/1
        https://regex101.com/delete/6nqyey0XnDX92WGJvB7ZLExc

        */
    } // end function getDomFromHTML

    private static $_xmlTag =  '<?xml version="1.0" encoding="UTF-8"?>';

} // end class view
