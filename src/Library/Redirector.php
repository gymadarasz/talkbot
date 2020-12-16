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

/**
 * Redirector
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Redirector
{
    protected Config $config;
    protected Session $session;
    
    /**
     * Method __construct
     *
     * @param Config  $config  config
     * @param Session $session session
     */
    public function __construct(Config $config, Session $session)
    {
        $this->config = $config;
        $this->session = $session;
    }
    
    /**
     * Method getRedirectResponse
     *
     * @param string $target      target
     * @param string $messageType messageType
     * @param string $messageText messageText
     *
     * @return string
     */
    public function getRedirectResponse(
        string $target,
        string $messageType,
        string $messageText
    ): string {
        $redirect = $this->config->get('Site')->get('base')
                . '/?' . Router::ROUTE_QUERY_KEY . '=' . $target;
        $this->session->set(
            'message',
            [
                'type' => $messageType,
                'text' => $messageText,
            ]
        );
        header("Location: $redirect");
        return "<script>document.location.href = '$redirect';</script>";
    }
}
