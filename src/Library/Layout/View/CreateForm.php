<?php declare(strict_types = 1);

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

use Madsoft\Library\Params;
use Madsoft\Library\Template;
use RuntimeException;

/**
 * CreateForm
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class CreateForm
{
    const TPL_PATH = __DIR__ . '/../../phtml/';
    
    protected Template $template;
    protected Params $params;
    
    /**
     * Method __construct
     *
     * @param Template $template template
     * @param Params   $params   params
     */
    public function __construct(Template $template, Params $params)
    {
        $this->template = $template;
        $this->params = $params;
    }
    
    /**
     * Method getCreateForm
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getCreateForm(): string
    {
        $createFormParams = $this->params->get('create-form');
        if (!array_key_exists('fields', $createFormParams)) {
            throw new RuntimeException(
                'Create form "fields" are not defined in '
                    . '"create-form" parameter array. '
                    . 'Use params[create-form][fields]'
            );
        }
        $createFormParams['fields'] = $this->buildFields(
            $createFormParams['fields']
        );
        return $this->template->setEncoder(null)->process(
            'create-form.phtml',
            $createFormParams,
            $this::TPL_PATH
        );
    }
    
    /**
     * Method buildFields
     *
     * @param mixed[] $fields fields
     *
     * @return string
     */
    protected function buildFields(array $fields): string
    {
        $results = '';
        foreach ($fields as $field) {
            if (!array_key_exists('type', $field) || !$field['type']) {
                throw new RuntimeException('Field "type" is missing.');
            }
            $results .= $this->template->process(
                'form-fields/' . $field['type'] . '.phtml',
                $field,
                $this::TPL_PATH
            );
        }
        return $results;
    }
}
