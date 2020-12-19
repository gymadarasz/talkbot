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

use Madsoft\Library\Layout\View\RowView;
use Madsoft\Library\Validator\Rule\Mandatory;
use Madsoft\Library\Validator\Validator;
use RuntimeException;

/**
 * Content
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Content extends RowView implements Assoc
{
    protected ?int $contentId = null;
    
    /**
     * Variable $row
     *
     * @var string[]|null
     */
    protected ?array $row = null;
    
    protected Database $database;
    protected Params $params;
    protected Validator $validator;


    /**
     * Method __construct
     *
     * @param Database  $databse   databse
     * @param Params    $params    params
     * @param Validator $validator validator
     */
    public function __construct(
        Database $databse,
        Params $params,
        Validator $validator
    ) {
        $this->database = $databse;
        $this->params = $params;
        $this->validator = $validator;
    }
    
    /**
     * Method getContentId
     *
     * @return int|null
     */
    public function getContentId(): ?int
    {
        if (null === $this->contentId) {
            if ($this->params->has('content')) {
                $this->contentId = (int)$this->params->get('content')['id'];
            }
        }
        return $this->contentId;
    }
    
    /**
     * Method get
     *
     * @param string $key     key
     * @param mixed  $default default
     *
     * @return string
     */
    public function get(string $key, $default = null)
    {
        if (null !== $default) {
            throw new RuntimeException('Default content values are not allowed');
        }
        $this->getRow();
        if (null === $this->row || !array_key_exists($key, $this->row)) {
            throw new RuntimeException(
                "Content is not found or does't have a key: '$key'"
            );
        }
        return $this->row[$key];
    }
    
    /**
     * Method getRow
     *
     * @return string[]
     */
    public function getRow(): array
    {
        if (null === $this->row) {
            // TODO could have banned content and/or banned user
            $this->row = $this->database->getRow(
                ...$this->getDatasetParams(
                    $this->params->get('content-dataset')
                )
            );
            if (empty($this->row)) {
                $this->row = $this->getNotFoundContent();
            }
            
            $error = $this->validator->getFirstError(
                [
                'title' => [
                    'value' => $this->row['title'],
                    'rules' => [
                        Mandatory::class => null
                    ],
                ],
                'description' => [
                    'value' => $this->row['description'],
                    'rules' => [
                        Mandatory::class => null
                    ],
                ],
                'header' => [
                    'value' => $this->row['header'],
                    'rules' => [
                        Mandatory::class => null
                    ],
                ],
                'body' => [
                    'value' => $this->row['body'],
                    'rules' => [
                        Mandatory::class => null
                    ],
                ],
                ]
            );
            if ($error) {
                // TODO pretty print...
                throw new RuntimeException(
                    "Validation error on content: " . print_r($error, true)
                );
            }
        }
        return $this->row;
    }
    
    /**
     * Method getNotFoundContent
     *
     * @return mixed[]
     */
    public function getNotFoundContent(): array
    {
        return [
                'id' => null,
                'title' => 'Content is not found',
                'description' => 'Content is not found',
                'header' => 'Content is not found',
                'body' => 'Content is not found',
                'owner_user_id' => null,
            ];
    }

    /**
     * Method has
     *
     * @param string $key key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        throw new RuntimeException('Content values are strictly exists: ' . $key);
    }

    /**
     * Method set
     *
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return Assoc
     */
    public function set(string $key, $value): Assoc
    {
        throw new RuntimeException(
            'Contents are readable here: '
            . $key . '=>' . (string)$value
        );
    }
}
