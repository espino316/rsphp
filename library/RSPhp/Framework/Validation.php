<?php
/**
 * Validation.php
 *
 * PHP Version 5
 *
 * Validation File Doc Comment
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
 * Validates user input
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
class Validation
{

    protected $errors;
    public $errorCount;
    protected $rules;
    protected $messages;
    protected $values;

    /*
        Type of rules:
            required
            minlenght
            maxlenght
            email
            url
            integer
            IPs
    */

    /**
     * Clear the validation rules
     *
     * @return void
     */
    function clearRules()
    {
        $this->rules = array();
    }

    /**
     * Adds a validation rule
     *
     * Example:
     *  $validate->addRule(
     *      'inputName',
     *      'valType',
     *      'OptionalArgument'
     *  )
     *
     *  @param String      $key  The validation key
     *  @param String      $type The validation type
     *  @param String|null $arg  The rule argument
     *
     *  @return void
     */
    function addRule($key, $type, $arg = null)
    {
        $this->rules[] = array(
         'key' => $key,
         'type' => $type,
         'arg' => $arg
        );
    }

    /**
     * Validates a set of values
     *
     * @param Array $values The values to validate
     *
     * @return Boolean
     */
    function validate( $values )
    {

        $this->errorCount = 0;
        $this->errors = array();

        foreach ( $this->rules as $rule ) {

            switch( $rule['type']) {
            case "required":
                if (!$this->validRequired($values[$rule['key']])
                ) {
                    $this->errors[] = "Field $key is required";
                    $this->errorCount++;
                }
                break;
            case "minlenght":
                if (!$this->validMinLength($values[$rule['key']])
                ) {
                    $this->errors[]
                        = "Field $key must have at least " .
                        $rule['arg'] . " characters.";
                    $this->errorCount++;
                }
                break;
            case "maxlenght":
                if (!$this->validMaxLength($values[$rule['key']])
                ) {
                    $this->errors[]
                        = "Field $key must have at most " .
                        $rule['arg'] . " characters.";
                    $this->errorCount++;
                }
                break;
            case "email":
                if (!$this->validEmail($values[$rule['key']])
                ) {
                    $this->errors[] = "Field $key must be a valid email";
                    $this->errorCount++;
                }
                break;
            case "url":
                if (!$this->validUrl($values[$rule['key']])
                ) {
                    $this->errors[] = "Field $key must be a valid url";
                    $this->errorCount++;
                }
                break;
            case "integer":
                if (!$this->validInt($values[$rule['key']])
                ) {
                    $this->errors[] = "Field $key must be a valid integer";
                    $this->errorCount++;
                }
                break;
            case "IP":
                if (!$this->validIP($values[$rule['key']])
                ) {
                    $this->errors[] = "Field $key must be a valid IP address";
                    $this->errorCount++;
                }
                break;
            case "regex":
                if (!$this->validateRegEx($rule['arg'], $values[$rule['key']])
                ) {
                    $this->errors[]
                        = "Field $key must be have the regular " .
                        "expression pattern " . $rule['arg'];
                    $this->errorCount++;
                }
                break;
            } // end switch
        } // end foreach

        if ($this->errorCount > 0 ) {
            return false;
        } else {
            return true;
        }

    } // end function validate

    /**
     * Return the validation errors
     *
     * @return String
     */
    function getErrors()
    {
        $errMsg = '';
        foreach ($this->errors as $error) {
            $errMsg .= $error;
            $errMsg .= '<br/>';
        }
        return $errMsg;
    } // end function getErrors

    /**
     * Validates a minimal value
     *
     * @param Mixed $value The value to validate
     * @param Int   $min   The minimal value to compare
     *
     * @return Boolean
     */
    function validMin($value, $min)
    {
        return $value >= $min;
    }

    /**
     * Validates a maximal value
     *
     * @param Mixed $value The value to validate
     * @param Int   $max   The maximal value to compare
     *
     * @return Boolean
     */
    function validMax($value, $max)
    {
        return $value <= $max;
    }

    /**
     * Validates a minimal length
     *
     * @param Mixed $value The value to validate
     * @param Int   $min   The minimal length to compare
     *
     * @return Boolean
     */
    function validMinLength($value, $min)
    {
        return strlen($value) >= $min;
    } // end function validMinLength

    /**
     * Validates a maximal length
     *
     * @param Mixed $value The value to validate
     * @param Int   $max   The maximal length to compare
     *
     * @return Boolean
     */
    function validMaxLength($value, $max)
    {
        return strlen($value) <= $max;
    } // end function validMaxLength

    /**
     * Validates that value is present
     *
     * @param Mixed $value The value to validate
     *
     * @return Boolean
     */
    function validRequired($value)
    {
        return ($value != null) && !empty($value);
    } // end function validRequired

    /**
     * Validates an email
     *
     * @param String $value The email to validate
     *
     * @return Boolean
     */
    function validEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    } // end function validEmail

    /**
     * Validates value is boolean
     *
     * @param Mixed $value The value to validate
     *
     * @return Boolean
     */
    function validBool($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    } // end function validBool

    /**
     * Validates value is float
     *
     * @param Mixed $value The value to validate
     *
     * @return Boolean
     */
    function validFloat($value)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT);
    } // end function validFloat

    /**
     * Validates value is int
     *
     * @param Mixed $value The value to validate
     *
     * @return Boolean
     */
    function validInt($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    } // end function validInt

    /**
     * Validates value is IP address
     *
     * @param Mixed $value The value to validate
     *
     * @return Boolean
     */
    function validIP($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP);
    } // end function validIP

    /**
     * Validates value is MAC address
     *
     * @param Mixed $value The value to validate
     *
     * @return Boolean
     */
    function validMAC($value)
    {
        return filter_var($value, FILTER_VALIDATE_MAC);
    } // end function validMAC

    /**
     * Validates value is url address
     *
     * @param Mixed $value The value to validate
     *
     * @return Boolean
     */
    function validUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    } // end function validUrl

    /**
     * Validates a regex
     *
     * @param Mixed  $pattern The pattern to validate
     * @param String $subject The value to validate
     *
     * @return Boolean
     */
    function validateRegEx( $pattern, $subject )
    {
        $pattern = '/' . $pattern . '/';
        preg_match($pattern, $subject, $matches);
        return !empty($matches);
    } // end function ValidateRegEx

    /**
     * Validayes the inputs from a view
     * Saves the work of create rules
     *
     * @param String $viewName The name of the view to validate
     *
     * @return Boolean
     */
    function validateInputsFromView($viewName)
    {

        $this->errorCount = 0;
        $this->errors = array();

        $view = View::loadToString($viewName);

        if (!$view ) {
            return true;
        }

        $doc = new DOMDocument();
          $doc->loadHTML($view);
          $inputs = $doc->getElementsByTagName('input');
        foreach ($inputs as $input) {
            $name = $input->getAttribute('name');
            $value = Input::get($name);
            $required = $input->getAttribute('required');
            $pattern = $input->getAttribute('pattern');
            $type = $input->getAttribute('type');
            $title = $input->getAttribute('title');
            $minlenght = $input->getAttribute('minlenght');
            $maxlenght = $input->getAttribute('maxlenght');
            $min = $input->getAttribute('min');
            $max = $input->getAttribute('max');

            if ($required ) {
                if (!$value ) {
                    if ($title ) {
                        $this->errors[] = $title;
                        $this->errorCount++;
                    } else {
                        $this->errors[] = "Field $name is required";
                        $this->errorCount++;
                    } // end if then else $title
                } // end if !$value
            } // end if $required

            if ($pattern ) {
                if ($value ) {
                    if (!$this->validateRegEx($patter, $value) ) {
                        if ($title ) {
                            $this->errors[] = $title;
                            $this->errorCount++;
                        } else {
                            $this->errors[]
                                = "Field $name must be have the " .
                                "regular expression pattern " .
                                $pattern;
                            $this->errorCount++;
                        } // end if then else $title
                    } // end validateRegEx
                } // end if $value
            } // end if $pattern

            switch ($type) {
            case 'email':
                if ($value ) {
                    if (! validEmail($value) ) {
                        if ($title ) {
                            $this->errors[] = $title;
                            $this->errorCount++;
                        } else {
                            $this->errors[] = "Field $name must be a valid email";
                            $this->errorCount++;
                        }
                    } // end if !validEmail
                } // end if $value
                break;

            case 'url':
                if ($value ) {
                    if (! validUrl($value) ) {
                        if ($title ) {
                            $this->errors[] = $title;
                            $this->errorCount++;
                        } else {
                            $this->errors[] = "Field $name must be a valid email";
                            $this->errorCount++;
                        }
                    } // end if !validEmail
                } // end if $value
                break;

            default:
                // code...
                break;
            } // end switch
        } // end foreach

        if ($this->errorCount > 0 ) {
            return false;
        } else {
            return true;
        }

    } // end function validateInputsFromView
} // end class
