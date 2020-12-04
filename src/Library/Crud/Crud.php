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

use Madsoft\Library\Csrf;
use Madsoft\Library\Database;
use Madsoft\Library\Logger;
use Madsoft\Library\Messages;
use Madsoft\Library\MysqlEmptyException;
use Madsoft\Library\MysqlNoAffectException;
use Madsoft\Library\MysqlNoInsertException;
use Madsoft\Library\MysqlNotFoundException;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;

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
    protected Logger $logger;
    
    /**
     * Method __construct
     *
     * @param Messages $messages messages
     * @param Csrf     $csrf     csrf
     * @param Database $database database
     * @param Params   $params   params
     * @param Logger   $logger   logger
     */
    public function __construct(
        Messages $messages,
        Csrf $csrf,
        Database $database,
        Params $params,
        Logger $logger
    ) {
        parent::__construct($messages, $csrf);
        $this->database = $database;
        $this->params = $params;
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
            // TODO order field (ASC/DESC)
            return $this->getResponse(
                [
                    'rows' => $this->database->getRows(
                        $this->params->get('table'),
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
            return $this->getResponse(
                $this->database->getRow(
                    $this->params->get('table'),
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
     * Method getEditResponse
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getEditResponse(): array
    {
        try {
            return $this->getAffectResponse(
                $this->database->setRow(
                    $this->params->get('table'),
                    $this->params->get('values', []),
                    $this->params->get('filter', []),
                    $this->params->get(
                        'filterLogic',
                        self::DEFAULT_FILTER_LOGIC
                    ),
                    (int)$this->params->get('limit', self::DEFAULT_LIMIT)
                )
            );
        } catch (MysqlNoAffectException $exception) {
            $this->logger->exception($exception);
        }
        return $this->getErrorResponse('Not affected');
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
        try {
            return $this->getInsertResponse(
                $this->database->addRow(
                    $this->params->get('table'),
                    $this->params->get('values', [])
                )
            );
        } catch (MysqlNoInsertException $exception) {
            $this->logger->exception($exception);
        }
        return $this->getErrorResponse('Not inserted');
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
        try {
            return $this->getAffectResponse(
                $this->database->delRow(
                    $this->params->get('table'),
                    $this->params->get('filter', []),
                    $this->params->get(
                        'filterLogic',
                        self::DEFAULT_FILTER_LOGIC
                    ),
                    (int)$this->params->get('limit', self::DEFAULT_LIMIT)
                )
            );
        } catch (MysqlNoAffectException $exception) {
            $this->logger->exception($exception);
        }
        return $this->getErrorResponse('Not affected');
    }
}
