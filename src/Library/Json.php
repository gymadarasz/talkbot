<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library;

use RuntimeException;

/**
 * Json
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Json
{
    
    /**
     * Method encode
     *
     * @param mixed $data data
     *
     * @return string
     * @throws RuntimeException
     */
    public function encode($data): string
    {
        $str = json_encode($data);
        if (false === $str) {
            throw new RuntimeException($this->getError($str));
        }
        return $str;
    }
    
    /**
     * Method decode
     *
     * @param string $json json
     *
     * @return mixed
     * @throws RuntimeException
     */
    public function decode(string $json)
    {
        $data = json_decode($json, true);
        if (false === $data) {
            throw new RuntimeException($this->getError($data));
        }
        return $data;
    }
    
    /**
     * Method getError
     *
     * @param mixed $result result
     *
     * @return string
     */
    protected function getError($result): string
    {
        $error = 'Could not decode JSON!';
        //Backwards compatability.
        if (!function_exists('json_last_error')) {
            return $error;
        }

        //Get the last JSON error.
        $jsonError = json_last_error();

        //In some cases, this will happen.
        if (is_null($result) && $jsonError == JSON_ERROR_NONE) {
            return $error;
        }

        //If an error exists.
        if ($jsonError != JSON_ERROR_NONE) {
            $error .= ' ' . $this->getErrorDetails($jsonError);
        }
        
        return $error;
    }
    
    /**
     * Method getErrorDetails
     *
     * @param mixed $jsonError jsonError
     *
     * @return string
     */
    protected function getErrorDetails($jsonError): string
    {
        //Use a switch statement to figure out the exact error.
        switch ($jsonError) {
        case JSON_ERROR_DEPTH:
            return 'Maximum depth exceeded!';
                
        case JSON_ERROR_STATE_MISMATCH:
            return 'Underflow or the modes mismatch!';
        case JSON_ERROR_CTRL_CHAR:
            return 'Unexpected control character found';
        case JSON_ERROR_SYNTAX:
            return 'Malformed JSON';
        case JSON_ERROR_UTF8:
            return 'Malformed UTF-8 characters found!';
        }
            
        return 'Unknown error!';
    }
}
