<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Account\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Account\View;

use Madsoft\Library\Account\Activate;
use Madsoft\Library\Params;
use Madsoft\Library\Session;
use Madsoft\Library\Template;

/**
 * ActivatePage
 *
 * @category  PHP
 * @package   Madsoft\Library\Account\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class ActivatePage
{
    const TPL_PATH = __DIR__ . '/phtml/';
    
    protected Template $template;
    protected Activate $activate;
    protected Params $params;
    protected Session $session;
    
    /**
     * Method __construct
     *
     * @param Template $template template
     * @param Activate $activate activate
     * @param Params   $params   params
     * @param Session  $session  session
     */
    public function __construct(
        Template $template,
        Activate $activate,
        Params $params,
        Session $session
    ) {
        $this->template = $template;
        $this->activate = $activate;
        $this->params = $params;
        $this->session = $session;
    }
    
    /**
     * Method getActivatePage
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getActivatePage(): string
    {
        $response = $this->activate->getActivateResponse(
            $this->params,
            $this->session
        );
        
        return $this->template->setEncoder(null)->process(
            'login.phtml',
            $response,
            $this::TPL_PATH
        );
    }
}
