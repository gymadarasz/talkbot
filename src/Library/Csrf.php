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
    protected ?string $csrf = null;
    
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
     * @return string
     */
    public function get(): string
    {
        if (!$this->csrf) {
            $this->csrf = (string)rand(100000000, 999999999);
            $this->session->set('csrf', $this->csrf);
        }
        return '<input type="hidden" name="csrf" value="' . $this->csrf . '">';
    }
    
    /**
     * Method check
     *
     * @return bool
     * @throws RuntimeException
     */
    public function check(): bool
    {
        $csrf = $this->session->get('csrf');
        if (!$csrf) {
            throw new RuntimeException('CSRF token is missing from session.');
        }
        $sent = $this->params->get('csrf', '');
        if (!$sent) {
            throw new RuntimeException('CSRF token is not recieved by request.');
        }
        return $csrf == $sent;
    }
}
