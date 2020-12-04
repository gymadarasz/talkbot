<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Responder
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Responder;

use Madsoft\Library\Csrf;
use Madsoft\Library\Messages;

/**
 * ArrayResponder
 *
 * @category  PHP
 * @package   Madsoft\Library\Responder
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
abstract class ArrayResponder
{
    const LBL_SUCCESS = 'Operation success';
    const LBL_ERROR = 'Operation failed';
    const LBL_WARNING = 'Operation success but some errors occurred';
    
    protected Messages $messages;
    protected Csrf $csrf;

    /**
     * Method __construct
     *
     * @param Messages $messages messages
     * @param Csrf     $csrf     csrf
     */
    public function __construct(Messages $messages, Csrf $csrf)
    {
        $this->messages = $messages;
        $this->csrf = $csrf;
    }
    /**
     * Method getErrorResponse
     *
     * @param string     $error  error
     * @param string[][] $errors errors
     * @param mixed[]    $data   data
     *
     * @return mixed[]
     */
    public function getErrorResponse(
        string $error = self::LBL_ERROR,
        array $errors = [],
        array $data = []
    ): array {
        $this->messages->add('error', $error);
        return $this->getResponse($data, $errors);
    }
    
    /**
     * Method getWarningResponse
     *
     * @param string  $message message
     * @param mixed[] $data    data
     *
     * @return mixed[]
     */
    public function getWarningResponse(
        string $message = self::LBL_WARNING,
        array $data = []
    ): array {
        $this->messages->add('success', $message);
        return $this->getResponse($data);
    }

    /**
     * Method getAffectResponse
     *
     * @param int    $affectedRows affectedRows
     * @param string $message      message
     *
     * @return mixed[]
     */
    public function getAffectResponse(
        int $affectedRows,
        string $message = self::LBL_SUCCESS
    ): array {
        return $this->getSuccessResponse($message, ['affected' => $affectedRows]);
    }

    /**
     * Method getInsertResponse
     *
     * @param int    $insertId insertId
     * @param string $message  message
     *
     * @return mixed[]
     */
    public function getInsertResponse(
        int $insertId,
        string $message = self::LBL_SUCCESS
    ): array {
        return $this->getSuccessResponse($message, ['insertId' => $insertId]);
    }
    
    /**
     * Method getSuccessResponse
     *
     * @param string  $message message
     * @param mixed[] $data    data
     *
     * @return mixed[]
     */
    public function getSuccessResponse(
        string $message = self::LBL_SUCCESS,
        array $data = []
    ): array {
        $this->messages->add('success', $message);
        return $this->getResponse($data);
    }
    
    /**
     * Method getResponse
     *
     * @param mixed[]    $data   data
     * @param string[][] $errors errors
     *
     * @return mixed[]
     */
    public function getResponse(array $data = [], array $errors = []): array
    {
        $data['csrf'] = $this->csrf->get();
        $messages = $this->messages->get();
        if ($messages) {
            $data['messages'] = $messages;
        }
        if ($errors) {
            $data['errors'] = $errors;
        }
        return $data;
    }
}
