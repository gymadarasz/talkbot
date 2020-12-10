<?php

declare(strict_types=1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Layout;

use Madsoft\Library\Csrf;
use Madsoft\Library\FileCollector;
use Madsoft\Library\Invoker;
use Madsoft\Library\Layout\View\ErrorPage;
use Madsoft\Library\Layout\View\Header;
use Madsoft\Library\Layout\View\Meta;
use Madsoft\Library\Layout\View\Navbar;
use Madsoft\Library\Messages;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;
use Madsoft\Library\Template;

/**
 * Layout
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Layout extends ArrayResponder
{
    const TPL_PATH = Template::TPL_PATH;

    protected Invoker $invoker;
    protected Template $template;
    protected Params $params;
    protected FileCollector $fileCollector;

    /**
     * Method __construct
     *
     * @param Messages      $messages      messages
     * @param Csrf          $csrf          csrf
     * @param Invoker       $invoker       invoker
     * @param Template      $template      template
     * @param Params        $params        params
     * @param FileCollector $fileCollector fileCollector
     */
    public function __construct(
        Messages $messages,
        Csrf $csrf,
        Invoker $invoker,
        Template $template,
        Params $params,
        FileCollector $fileCollector
    ) {
        parent::__construct($messages, $csrf);
        $this->invoker = $invoker;
        $this->template = $template;
        $this->params = $params;
        $this->fileCollector = $fileCollector;
    }

    /**
     * Method getOutput
     *
     * @return mixed[]
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getOutput(): array
    {
        $views = $this->params->get('views');
        foreach ($views as &$view) {
            $class = $view[0];
            $method = $view[1];
            $args = $view[2] ?? [];
            $view = $this->invoker->getInstance($class)->{$method}(...$args);
        }
        return $this->getResponse($views);
    }

    /**
     * Method getHtmlPage
     *
     * @param mixed[] $response response
     * @param string  $error    error
     *
     * @return string
     */
    public function getHtmlPage(array $response, string $error): string
    {
        $response['jsFiles'] = $this->fileCollector->getJsFiles();
        $response['cssFiles'] = $this->fileCollector->getCssFiles();

        if ($error) {
            $this->params->setOverrides(
                [
                        'title' => 'Error happened',
                        'header' => 'Error happened',
                    ]
            );
            $response['meta'] = $this->invoker->getInstance(Meta::class)
                ->getMeta();
            $response['navbar'] = $this->invoker->getInstance(Navbar::class)
                ->getNavbar();
            $response['header'] = $this->invoker->getInstance(Header::class)
                ->getHeader();
            $response['body'] = $this->invoker->getInstance(ErrorPage::class)
                ->getErrorContent();

            return $this->template->setEncoder(null)->process(
                'index.phtml',
                $response,
                $this::TPL_PATH
            );
        }
        return $this->template->setEncoder(null)->process(
            $this->params->get('tplfile', 'index.phtml'),
            $response,
            $this::TPL_PATH
        );
    }
}
