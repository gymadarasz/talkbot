<?php

declare(strict_types=1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Layout\View;

use Madsoft\Library\Config;
use Madsoft\Library\Params;
use Madsoft\Library\Template;

/**
 * Navbar
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Navbar
{
    const TPL_PATH = __DIR__ . '/phtml/';

    protected Template $template;
    protected Params $params;
    protected Config $config;

    /**
     * Method __construct
     *
     * @param Template $template template
     * @param Params   $params   params
     * @param Config   $config   config
     */
    public function __construct(
        Template $template,
        Params $params,
        Config $config
    ) {
        $this->template = $template;
        $this->params = $params;
        $this->config = $config;
    }

    /**
     * Method getNavbar
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getNavbar(): string
    {
        return $this->template->setEncoder(null)->process(
            'navbar.phtml',
            [
                            'brand' => $this->config->get('Site')->get('brand'),
                            'links' => $this->getLinks(),
                        ],
            $this::TPL_PATH
        );
    }

    /**
     * Method getLinks
     *
     * @return mixed[]
     */
    protected function getLinks(): array
    {
        $base = $this->config->get('Site')->get('base');
        $query = $this->params->get('q', '');
        return [
            'left' => [
                [
                    'active' => $query === '',
                    'dropdown' => [],
                    'disabled' => false,
                    'href' => $base,
                    'text' => 'Home',
                ]
            ],
            'right' => [
                [
                    'active' => $query ==='login' || $query ==='registry',
                    'dropdown' => [
                        'right' => true,
                        'items' => [
                            [
                                'divider' => false,
                                'href' => "$base?q=login",
                                'text' => 'Login'
                            ],
                            [
                                'divider' => false,
                                'href' => "$base?q=registry",
                                'text' => 'Register'
                            ],
                        ],
                    ],
                    'disabled' => false,
                    'href' => '#',
                    'text' => 'Login',
                ]
            ],
        ];
    }
}
