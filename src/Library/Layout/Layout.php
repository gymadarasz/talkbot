<?php declare(strict_types = 1);

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
use Madsoft\Library\Invoker;
use Madsoft\Library\Messages;
use Madsoft\Library\Params;
use Madsoft\Library\Responder\ArrayResponder;

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
    protected Invoker $invoker;
    protected Params $params;
    
    /**
     * Method __construct
     *
     * @param Messages $messages messages
     * @param Csrf     $csrf     csrf
     * @param Invoker  $invoker  invoker
     * @param Params   $params   params
     */
    public function __construct(
        Messages $messages,
        Csrf $csrf,
        Invoker $invoker,
        Params $params
    ) {
        parent::__construct($messages, $csrf);
        $this->invoker = $invoker;
        $this->params = $params;
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
}
