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
use Madsoft\Library\Messages;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Session;

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
    //    const DEFAULT_OFFSET = 0;
    
    protected Database $database;
    protected Params $params;
    
    /**
     * Method __construct
     *
     * @param Messages $messages messages
     * @param Csrf     $csrf     csrf
     * @param Session  $session  session
     * @param Database $database database
     * @param Params   $params   params
     */
    public function __construct(
        Messages $messages,
        Csrf $csrf,
        Session $session,
        Database $database,
        Params $params
    ) {
        parent::__construct($messages, $csrf, $session);
        $this->database = $database;
        $this->params = $params;
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
        $rows =  $this->database->getRows(...$this->getListParams());
        if (!$rows) {
            return $this->getErrorResponse('Empty list');
        }
        // TODO order field (ASC/DESC)
        return $this->getResponse(
            [
                    'rows' => $rows,
                ]
        );
    }
    
    /**
     * Method getListParams
     *
     * @return mixed[]
     */
    protected function getListParams(): array
    {
        return [
            $this->params->get('table'),
            explode(',', $this->params->get('fields')),
            $this->params->get('join'),
            $this->params->get('where'),
            $this->params->get('filter', []),
            $this->params->get('filterLogic'),
            (int)$this->params->get('limit'),
            (int)$this->params->get('offset'),
        ];
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
        $row  =$this->database->getRow(...$this->getViewParams());
        if (!$row) {
            return $this->getErrorResponse('Not found');
        }
        return $this->getResponse(
            [
                    'row' => $row
                ]
        );
    }
    
    /**
     * Method getViewParams
     *
     * @return mixed[]
     */
    protected function getViewParams(): array
    {
        return [
            $this->params->get('table'),
            explode(
                ',',
                $this->params->get('fields', self::DEFAULT_FIELDS)
            ),
            $this->params->get('join', ''),
            $this->params->get('where', ''),
            $this->params->get('filter', []),
            $this->params->get(
                'filterLogic',
                self::DEFAULT_FILTER_LOGIC
            )
        ];
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
        $affecteds = $this->database->setRow(...$this->getEditParams());
        if ($affecteds > 0) {
            return $this->getAffectEditDeleteResponse($affecteds);
        }
        if ($affecteds == 0) {
            return $this->getNoAffectEditDeleteResponse();
        }
        return $this->getErrorResponse('Not found');
    }
    
    /**
     * Method getEditParams
     *
     * @return mixed[]
     */
    protected function getEditParams(): array
    {
        return [
            $this->params->get('table'),
            $this->params->get('values', []),
            $this->params->get('where', ''),
            $this->params->get('filter', []),
            $this->params->get('filterLogic'), //self::DEFAULT_FILTER_LOGIC),
            (int)$this->params->get('limit') //, self::DEFAULT_LIMIT)
        ];
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
        $insertId = $this->database->addRow(...$this->getCreateParams());
        if ($insertId) {
            $target = $this->params->get('onSuccessRedirectTarget', null);
            if ($target) {
                return $this->getInsertRedirectResponse(
                    $target,
                    $insertId,
                    $this->params->get('successMessage')
                );
            }
            return $this->getInsertResponse(
                $insertId,
                $this->params->get('successMessage'),
            );
        }
        return $this->getErrorResponse('Not inserted');
    }
    
    /**
     * Method getCreateParams
     *
     * @return mixed[]
     */
    protected function getCreateParams(): array
    {
        return [
            $this->params->get('table'),
            $this->params->get('values', []),
        ];
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
        $affecteds = $this->database->delRow(...$this->getDeleteParams());
        if ($affecteds) {
            return $this->getAffectEditDeleteResponse($affecteds);
        }
        return $this->getErrorResponse('Not affected');
    }
    
    /**
     * Method getDeleteParams
     *
     * @return mixed[]
     */
    protected function getDeleteParams(): array
    {
        return [
            $this->params->get('table'),
            $this->params->get('where', ''),
            $this->params->get('filter', []),
            $this->params->get(
                'filterLogic',
                self::DEFAULT_FILTER_LOGIC
            ),
            (int)$this->params->get('limit', self::DEFAULT_LIMIT)
        ];
    }
    
    /**
     * Method getAffectEditDeleteResponse
     *
     * @param int|string $affecteds affecteds
     *
     * @return mixed[]
     */
    protected function getAffectEditDeleteResponse($affecteds): array
    {
        $target = $this->params->get('onSuccessRedirectTarget', null);
        if ($target) {
            return $this->getAffectRedirectResponse(
                $target,
                $affecteds,
                $this->params->get('successMessage')
            );
        }
        return $this->getAffectResponse($affecteds);
    }
    
    /**
     * Method getNoAffectEditDeleteResponse
     *
     * @return mixed[]
     */
    protected function getNoAffectEditDeleteResponse(): array
    {
        $target = $this->params->get('onSuccessRedirectTarget', null);
        if ($target) {
            return $this->getNoAffectRedirectResponse(
                $target,
                $this->params->get('noAffectMessage')
            );
        }
        return $this->getNoAffectResponse();
    }
}
