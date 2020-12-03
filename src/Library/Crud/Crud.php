<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Crud
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Crud;

use Madsoft\Library\Database;
use Madsoft\Library\Logger;
use Madsoft\Library\Messages;
use Madsoft\Library\MysqlEmptyException;
use Madsoft\Library\MysqlNotFoundException;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Validator\Rule\Mandatory;
use Madsoft\Library\Validator\Validator;

/**
 * Crud
 *
 * @category  PHP
 * @package   Madsoft\Library\Crud
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Crud extends ArrayResponder // TODO: test for this class + owned crud also
{
    const DEFAULT_FIELDS = 'id';
    const DEFAULT_FILTER_LOGIC = 'AND';
    const DEFAULT_LIMIT = 25;
    const DEFAULT_OFFSET = 0;
    
    protected Database $database;
    protected Params $params;
    protected Validator $validator;
    protected Logger $logger;
    
    /**
     * Method __construct
     *
     * @param Messages  $messages  messages
     * @param Database  $database  database
     * @param Params    $params    params
     * @param Validator $validator validator
     * @param Logger    $logger    logger
     */
    public function __construct(
        Messages $messages,
        Database $database,
        Params $params,
        Validator $validator,
        Logger $logger
    ) {
        parent::__construct($messages);
        $this->database = $database;
        $this->params = $params;
        $this->validator = $validator;
        $this->logger = $logger;
    }
    
    /**
     * Method getListResponse
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getListResponse(): array
    {
        try {
            $errors = $this->validateListViewParams();
            if ($errors) {
                return $this->getErrorResponse('Invalid list parameter(s)', $errors);
            }
        
            // TODO order field (ASC/DESC)
            return $this->getResponse(
                [
                    'rows' => $this->database->getRows(
                        $this->params->get('table', ''),
                        explode(
                            ',',
                            $this->params->get('fields', self::DEFAULT_FIELDS)
                        ),
                        $this->params->get('filter', []),
                        $this->params->get(
                            'filterLogic',
                            self::DEFAULT_FILTER_LOGIC
                        ),
                        (int)$this->params->get('limit', self::DEFAULT_LIMIT),
                        (int)$this->params->get('offset', self::DEFAULT_OFFSET)
                    ),
                ]
            );
        } catch (MysqlEmptyException $exception) {
            $this->logger->exception($exception);
        }
        return $this->getErrorResponse('Empty list');
    }
    
    /**
     * Method getViewResponse
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getViewResponse(): array
    {
        try {
            $errors = $this->validateListViewParams();
            if ($errors) {
                return $this->getErrorResponse('Invalid view parameter(s)', $errors);
            }
        
            return $this->getResponse(
                $this->database->getRow(
                    $this->params->get('table', ''),
                    explode(
                        ',',
                        $this->params->get('fields', self::DEFAULT_FIELDS)
                    ),
                    $this->params->get('filter', []),
                    $this->params->get(
                        'filterLogic',
                        self::DEFAULT_FILTER_LOGIC
                    )
                )
            );
        } catch (MysqlNotFoundException $exception) {
            $this->logger->exception($exception);
        }
        return $this->getErrorResponse('Not found');
    }
    
    /**
     * Method validateListViewParams
     *
     * @return string[][]
     */
    protected function validateListViewParams(): array
    {
        return $this->validator->getErrors(
            [
                'table' => [
                    'value' => $this->params->get(
                        'table',
                        ''
                    ),
                    'rules' => [
                        Mandatory::class => null,
                    ],
                ],
            ]
        );
    }
    
    /**
     * Method getEditResponse
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getEditResponse(): array
    {
        return ['unimp']; // TODO
    }
    
    /**
     * Method getCreateResponse
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getCreateResponse(): array
    {
        return ['unimp'];// TODO
    }
    
    /**
     * Method getDeleteResponse
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getDeleteResponse(): array
    {
        return ['unimp'];// TODO
    }
}
