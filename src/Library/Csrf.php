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
 * Csrf
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Csrf
{
    protected ?int $csrf = null;
    
    protected Session $session;
    protected Params $params;
    
    /**
     * Method __construct
     *
     * @param Session $session session
     * @param Params  $params  params
     */
    public function __construct(Session $session, Params $params)
    {
        $this->session = $session;
        $this->params = $params;
    }
    
    /**
     * Method get
     *
     * @return int
     */
    public function get(): int
    {
        if (!$this->csrf) {
            $this->csrf = rand(100000000, 999999999);
            $this->session->set('csrf', $this->csrf);
        }
        return $this->csrf;
    }
    
    /**
     * Method getAsFormField
     *
     * @return string
     */
    public function getAsFormField(): string
    {
        return '<input type="hidden" name="csrf" value="' . $this->get() . '">';
    }
    
    /**
     * Method getAsArray
     *
     * @return int[]
     */
    public function getAsArray(): array
    {
        return ['csrf' => $this->get()];
    }
    
    /**
     * Method check
     *
     * @return self
     * @throws RuntimeException
     */
    public function check(): self
    {
        $csrf = $this->session->get('csrf');
        if (!$csrf) {
            throw new RuntimeException('CSRF token is missing from session.');
        }
        $sent = $this->params->get('csrf', '');
        if (!$sent) {
            throw new RuntimeException('CSRF token is not recieved by request.');
        }
        if ($csrf !== (int)$sent) {
            throw new RuntimeException('CSRF token mismatch');
        }
        return $this;
    }
}
