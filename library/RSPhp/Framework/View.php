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
            = ROOT . DS . 'application' . DS . 'views' . DS . $viewName . '.php';

        if (file_exists($filePath)) {
            $view = self::_requireToVar($filePath);
            $view = str_replace('$baseUrl', BASE_URL, $view);

            if ($data != null ) {
                foreach ( array_keys($data) as $itemKey ) {
                    if (String::contains($itemKey, "$") ) {
                        if (is_array($data[$itemKey]) ) {
                            $data[$itemKey] 
                                = self::_convertToObjects(
                                    $data[$itemKey]
                                );
                        }
                        if (is_object($data[$itemKey]) ) {
                            $properties = get_object_vars($data[$itemKey]);

                            foreach ( array_keys($properties) as $key ) {

                                if (is_object($properties[$key]) ) {
                                    $subProperties
                                        = get_object_vars($properties[$key]);

                                    foreach (
                                        array_keys($subProperties) as $subKey 
                                    ) {
                                        $view 
                                            = stri_replace(
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
                            $view = str_replace($itemKey, $data[$itemKey], $view);
                        } // end if then else
                    } // end if contains $
                } // end foreach
            } // end if data
            $view = self::dataBind($view, $data);
            $view = String::specialCharsToHTML($view);
            return $view;
        } else {
            throw new Exception("View $filePath does not exists.");
        }
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
                if (String::contains($itemKey, "$") ) {
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
     * Binds all selects in $html
     *
     * @param String     $html The HTML to parse
     * @param Array|null $data The arrya containing info for databind
     *
     * @return void
     */
    private static function _viewsDataBind( $html, $data = null )
    {

        $dom = self::getDomFromHTML($html);

        //	DataSource Selects
        $items = $dom->getElementsByTagName('section');
        $count = $items->length - 1;

        if ($count > -1 ) {
            $toReplace = array();

            while ( $count > -1 ) {
                $item = $items->item($count);
                if ($item->hasAttribute('data-view')
                ) {

                    $viewName = $item->getAttribute('data-view');

                    if (String::contains($viewName, '$') ) {
                        if (isset($data[$viewName]) ) {
                            $viewName = $data[$viewName];
                        } // end if is set data [ viewName ]
                    } // end if contains $

                    $view = View::loadToString($viewName);

                    //	Replacement
                    $replacement = null;
                    $replacement['search'] = $item->ownerDocument->saveHTML($item);
                    $replacement['replace'] = $view;
                    $toReplace[] = $replacement;
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
        } // end if count > -1

        /*	Here begins data-bind attibute */
        $isFragment = true;
        if (String::contains($html, '<html') ) {
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
                    } else if (String::contains($name, "[") ) {
                                 $arr = explode("[", $name);
                                 $key1 = $arr[0];
                                 $key2 = String::replace("]", "", $arr[1]);
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

            if ($isFragment ) {
                $body = $dom->getElementsByTagName('body');
                $body = $body->item(0);
                $html = self::domInnerHTML($body);
            } else {
                $html = @$dom->saveHTML();
            }

            if (!empty($toReplace) ) {
                foreach ( $toReplace as $replacement ) {
                    $old = $replacement['search'];
                    $new = $replacement['replace'];
                    $html = str_replace($old, $new, $html);
                } // end foreach $replacement
            } // end if $toReplace not empty
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

            if ($isFragment ) {
                $body = $dom->getElementsByTagName('body');
                $body = $body[0];
                $html = self::domInnerHTML($body);
            } else {
                $html = @$dom->saveHTML();
            }

            if (!empty($toReplace) ) {
                foreach ( $toReplace as $replacement ) {
                    $old = $replacement['search'];
                    $new = $replacement['replace'];
                    $html = str_replace($old, $new, $html);
                } // end foreach $replacement
            } // end if $toReplace not empty
        } // end if $count > -1

        return $html;
    } // end function _selectsDataBind


    /**
     * Binds all selects in $html
     *
     * @param String     $html The HTML to parse
     * @param Array|null $data The arrya containing info for databind
     *
     * @return void
     */
    private static function _selectsDataBind( $html, $data = null )
    {

        $isFragment = true;
        if (String::contains($html, '<html') ) {
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
                $data
                    = Db::getDataSource(
                        $item->getAttribute( "data-source" )
                    )->getResultSet();

                $value = '';
                if ($item->hasAttribute('data-bind') ) {
                    if ($data ) {
                        $value = $item->getAttribute('data-bind');
                        $value = $data[$value];
                    }
                } // end if hasAttribute value
                if ($item->hasAttribute('value') ) {
                         $value = $item->getAttribute('value');
                } // end if hasAttribute value

                //	Get the SELECT
                $select
                    = Html::formSelect(
                        $item->getAttribute('id'),
                        $data,
                        $item->getAttribute('data-value-field'),
                        $item->getAttribute('data-display-field'),
                        $value,
                        true
                    );

                //	Replacement
                $replacement = null;
                $replacement['search'] = $item->ownerDocument->saveHTML($item);
                $replacement['replace'] = $select;
                $toReplace[] = $replacement;
            } // end if has attributes
            $count--;
        } // end while item data-source

        if ($isFragment ) {
            $body = $dom->getElementsByTagName('body');
            $body = $body[0];
            $html = self::domInnerHTML($body);
        } else {
            $html = @$dom->saveHTML();
        }

        if (!empty($toReplace) ) {
            foreach ( $toReplace as $replacement ) {
                $old = $replacement['search'];
                $new = $replacement['replace'];
                $html = str_replace($old, $new, $html);
            } // end foreach $replacement
        } // end if $toReplace not empty

        return $html;
    } // end function _selectsDataBind

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

    /**
     * Binds all selects in $html
     *
     * @param String $html The HTML to parse
     *
     * @return void
     */
    private static function _tablesDataBind( $html )
    {

        $isFragment = true;
        if (String::contains($html, '<html') ) {
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

                //	Get the data
                $dsName = $item->getAttribute('data-source');
                $result
                    = Db::getDataSource( $dsName )->getResultSet(
                        null, $pageItems, $currentPage
                    );

                if ($pagination ) {
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

                //	Replacement
                $replacement = null;
                $replacement['search'] = $item->ownerDocument->saveHTML($item);
                $replacement['replace'] = $dataTable;
                $toReplace[] = $replacement;
            } // end if has attributes
            $count--;
        } // end while item data-source

        if ($isFragment ) {
            $body = $dom->getElementsByTagName('body');
            $body = $body[0];
            $html = self::domInnerHTML($body);
        } else {
            $html = @$dom->saveHTML();
        }

        if (!empty($toReplace) ) {
            foreach ( $toReplace as $replacement ) {
                $old = $replacement['search'];
                $new = $replacement['replace'];
                $html = str_replace($old, $new, $html);
            } // end foreach $replacement
        } // end if $toReplace not empty

        return $html;
    } // end function _selectsDataBind

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
        $html = self::_viewsDataBind($html, $data);
        $html = self::_selectsDataBind($html);
        $html = self::_tablesDataBind($html);
        return $html;
    } // end function dataBind


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
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

         return $innerHTML;
    } // end function DOMinnerHTML

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
        $prefix = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        if (!String::startsWith($html, $prefix) ) {
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

} // end class view
