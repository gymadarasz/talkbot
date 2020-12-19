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
 * ViewForm
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
abstract class ViewForm extends RowView
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
     * Method getForm
     *
     * @param string[][] $formParams formParams
     * @param string[]   $dataset    dataset
     * @param string     $tplFile    tplFile
     *
     * @return string
     * @throws RuntimeException
     */
    protected function getForm(
        array $formParams,
        array $dataset = [],
        string $tplFile = 'form.phtml'
    ): string {
        if (!array_key_exists('fields', $formParams)) {
            throw new RuntimeException(
                'Form "fields" are not defined in parameter array. '
                    . 'Use params[edit-form][fields]'
            );
        }
        $formParams['fields'] = $this->buildFields(
            $this->prefill($formParams['fields'], $dataset),
        );
        return $this->template->setEncoder(null)->process(
            $tplFile,
            $formParams,
            $this::TPL_PATH
        );
    }
    
    /**
     * Method prefill
     *
     * @param mixed[]  $fields  fields
     * @param string[] $dataset dataset
     *
     * @return string[]
     */
    protected function prefill(array $fields, array $dataset): array
    {
        foreach ($fields as &$field) {
            $key = $field['name'];
            if (array_key_exists($key, $dataset)) {
                $field['value'] = $dataset[$key];
                unset($dataset[$key]);
                continue;
            }
            if (!array_key_exists('value', $field)) {
                $field['value'] = '';
            }
        }
        if (!empty($dataset)) {
            throw new RuntimeException(
                'Trying to fill a form with data but there is no fields for: "'
                    . implode(
                        '", "',
                        array_keys($dataset)
                    )
            );
        }
        return $fields;
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
