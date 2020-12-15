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
use Madsoft\Library\Merger;
use Madsoft\Library\Params;
use Madsoft\Library\Router;
use Madsoft\Library\Template;
use RuntimeException;

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
    protected Router $router;
    protected Merger $merger;

    /**
     * Method __construct
     *
     * @param Template $template template
     * @param Params   $params   params
     * @param Config   $config   config
     * @param Router   $router   router
     * @param Merger   $merger   merger
     */
    public function __construct(
        Template $template,
        Params $params,
        Config $config,
        Router $router,
        Merger $merger
    ) {
        $this->template = $template;
        $this->params = $params;
        $this->config = $config;
        $this->router = $router;
        $this->merger = $merger;
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
        $area = $this->router->getRoutingArea();
        return $this->template->setEncoder(null)->process(
            'navbar.phtml',
            [
                            'brand' => $this->config->get('Site')->get('brand'),
                            'links' => $this->getLinks($area),
                        ],
            $this::TPL_PATH
        );
    }

    /**
     * Method getLinks
     *
     * @param string $area area
     *
     * @return mixed[]
     */
    protected function getLinks(string $area): array
    {
        switch ($area) {
        case 'public':
            $right = $this->getLinksRightPublic();
            break;
        case 'protected':
            $right = $this->getLinksRightProtected();
            break;
        case 'private':
            $right = $this->getLinksRightPrivate();
            break;
        default:
            throw new RuntimeException(
                "Invalid routing area for navigation: '$area'"
            );
        }
        
        $extraLeft = $this->params->get(
            'navbar',
            ['extra' => ['links' => ['left' => []]]]
        )['extra']['links']['left'];
        
        $extraRight = $this->params->get(
            'navbar',
            ['extra' => ['links' => ['right' => []]]]
        )['extra']['links']['right'];
        
        return $this->setActiveLink(
            [
                'left' => $this->merger->merge(
                    [
                            [
                                //'active' => $query === '',
                                'dropdown' => [],
                                'disabled' => false,
                                'href' => '',
                                'text' => 'Home',
                            ]
                        ],
                    $extraLeft
                ),
                    'right' => $this->merger->merge(
                        $right,
                        $extraRight
                    )
            ]
        );
    }
    
    /**
     * Method getLinksRightPublic
     *
     * @return mixed[]
     */
    protected function getLinksRightPublic(): array
    {
        return [
                [
                    //'active' => $query ==='login' || $query ==='registry',
                    'dropdown' => [
                        'right' => true,
                        'items' => [
                            [
                                'divider' => false,
                                'href' => "q=login",
                                'text' => 'Login'
                            ],
                            [
                                'divider' => false,
                                'href' => "q=registry",
                                'text' => 'Register'
                            ],
                        ],
                    ],
                    'disabled' => false,
                    'href' => '#',
                    'text' => 'Login',
                ]
            ];
    }
    
    /**
     * Method getLinksRightProtected
     *
     * @return mixed[]
     */
    protected function getLinksRightProtected(): array
    {
        return [
                [
                    //'active' => $query ==='profile',
                    'dropdown' => [
                        'right' => true,
                        'items' => [
                            [
                                'divider' => false,
                                'href' => "q=logout",
                                'text' => 'logout'
                            ],
                        ],
                    ],
                    'disabled' => false,
                    'href' => '#',
                    'text' => 'Profile',
                ]
            ];
    }
    
    /**
     * Method getLinksRightPrivate
     *
     * @return mixed[]
     */
    protected function getLinksRightPrivate(): array
    {
        return [
                [
                    //'active' => $query ==='profile',
                    'dropdown' => [
                        'right' => true,
                        'items' => [
                            [
                                'divider' => false,
                                'href' => "q=logout",
                                'text' => 'logout'
                            ],
                        ],
                    ],
                    'disabled' => false,
                    'href' => '#',
                    'text' => 'Profile',
                ]
            ];
    }
    
    /**
     * Method setActiveLink
     *
     * @param mixed[] $navbar navbar
     *
     * @return mixed[]
     */
    protected function setActiveLink(array $navbar): array
    {
        $query = $this->params->get('q', '');
        foreach ($navbar as &$items) {
            foreach ($items as &$item) {
                $item = $this->isActiveItem($item, $query);
            }
        }
        return $navbar;
    }
    
    /**
     * Method isActiveItem
     *
     * @param mixed[] $item  item
     * @param string  $query query
     *
     * @return mixed[]
     */
    protected function isActiveItem(array $item, string $query): array
    {
        $item['active'] = false;
        if ($item['dropdown']) {
            foreach ($item['dropdown']['items'] as &$subitem) {
                $item = $this->parseString($item, (string)$subitem['href'], $query);
                if ($item['active']) {
                    break;
                }
            }
            return $item;
        }
        return $this->parseString($item, (string)$item['href'], $query);
    }
    
    /**
     * Method parseString
     *
     * @param mixed[] $item  item
     * @param string  $href  href
     * @param string  $query query
     *
     * @return mixed[]
     */
    protected function parseString(
        array $item,
        string $href,
        string $query
    ): array {
        $results = null;
        $key = Router::ROUTE_QUERY_KEY;
        parse_str($href, $results);
        if (!array_key_exists($key, $results)) {
            $results[$key] = '';
        }
        $item['active'] = $item['active'] || $results[$key] === $query;
        return $item;
    }
}
