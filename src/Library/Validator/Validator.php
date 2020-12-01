<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Validator
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Validator;

use Madsoft\Library\Invoker;
use RuntimeException;

/**
 * Validator
 *
 * @category  PHP
 * @package   Madsoft\Library\Validator
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Validator
{
    protected Invoker $invoker;
    
    /**
     * Method __construct
     *
     * @param Invoker $invoker invoker
     */
    public function __construct(Invoker $invoker)
    {
        $this->invoker = $invoker;
    }
    
    /**
     * Method addErrors
     *
     * @param string[][] $errors errors
     * @param string     $fname  fname
     * @param mixed      $value  value
     * @param mixed[]    $rules  rules
     *
     * @return string[][]
     */
    protected function addErrors(
        array &$errors,
        string $fname,
        $value,
        array $rules
    ): array {
        $errs = $this->getValueErrors($value, $rules);
        if ($errs) {
            $errors[$fname] = $errs;
        }
        return $errors;
    }
    
    /**
     * Method getErrors
     *
     * @param mixed[] $fields fields
     *
     * @return string[][]
     */
    public function getErrors(array $fields): array
    {
        $errors = [];
        foreach ($fields as $fname => $field) {
            if (!is_string($fname)) {
                throw new RuntimeException(
                    "Field name should be string, "
                        . "descriptor array should be associative"
                );
            }
            if (!isset($field['value'])) {
                throw new RuntimeException("Field 'value' is missing: '$fname'");
            }
            if (!isset($field['rules'])) {
                throw new RuntimeException("Field 'rules' are missing: '$fname'");
            }
            $errors = $this->addErrors(
                $errors,
                $fname,
                $field['value'],
                $field['rules']
            );
        }
        return $errors;
    }
    
    /**
     * Method getFirstError
     *
     * @param mixed[] $fields fields
     *
     * @return string[][]
     */
    public function getFirstError(array $fields): array
    {
        $errors = [];
        foreach ($fields as $fname => $field) {
            if (!is_string($fname)) {
                throw new RuntimeException(
                    "Field name should be string, "
                        . "descriptor array should be associative"
                );
            }
            $errors = $this->addErrors(
                $errors,
                $fname,
                $field['value'],
                $field['rules']
            );
            if (array_key_exists($fname, $errors)) {
                break;
            }
        }
        return $errors;
    }
    
    /**
     * Method checkValue
     *
     * @param mixed   $value value
     * @param mixed[] $args  args
     *
     * @return string[]
     * @throws RuntimeException
     */
    public function getValueErrors($value, array $args): array
    {
        if (empty($args)) {
            throw new RuntimeException(
                'Validation rule arguments can not be empty. '
                    . 'Empty array given.'
            );
        }
        $restargs = $args;
        $errors = [];
        foreach ($args as $class => $params) {
            if (is_numeric($class)) {
                throw new RuntimeException(
                    "Non-associative validation rule contract descriptor given. "
                        . "Format should looks like array[rule][property] = value."
                );
            }
            $rule = $this->invoker->getInstance($class);
            $rule->apply($params ?: []);
            if (!$rule->check($value)) {
                $errors[] = $rule->getMessage();
            }
            unset($restargs[$class]);
        }
        if (!empty($restargs)) {
            $classes = implode("', '", array_keys($restargs));
            throw new RuntimeException(
                "Trying to apply some validation rule properties "
                    . "but rules are not defined or missing: '$classes'"
            );
        }
        return $errors;
    }
}
