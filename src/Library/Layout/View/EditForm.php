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

use Madsoft\Library\Database;
use Madsoft\Library\Params;
use Madsoft\Library\Template;
use RuntimeException;

/**
 * EditForm
 *
 * @category  PHP
 * @package   Madsoft\Library\Layout\View
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class EditForm extends ViewForm
{
    protected Database $database;
    
    /**
     * Method __construct
     *
     * @param Template $template template
     * @param Params   $params   params
     * @param Database $database database
     */
    public function __construct(
        Template $template,
        Params $params,
        Database $database
    ) {
        parent::__construct($template, $params);
        $this->database = $database;
    }
    
    /**
     * Method getEditForm
     *
     * @return string
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getEditForm(): string
    {
        $editFormParams = $this->params->get('edit-form');
        $dataset = $this->bind(
            $this->database->getRow(
                ...$this->getDatasetParams($editFormParams['dataset'])
            ),
            $editFormParams['formBindKey']
        );
        if (empty($dataset)) {
            throw new RuntimeException('Not found or not accsess for edit');
        }
        return $this->getForm($editFormParams, $dataset);
    }
    
    /**
     * Method bind
     *
     * @param string[] $datarow datarow
     * @param string   $formKey formKey
     *
     * @return string[]
     */
    protected function bind(array $datarow, string $formKey): array
    {
        $dataset = [];
        foreach ($datarow as $key => $value) {
            $dataset["{$formKey}[{$key}]"] = $value;
        }
        return $dataset;
    }
}
