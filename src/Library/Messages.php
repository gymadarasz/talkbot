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

use RuntimeException;

/**
 * Messages
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Messages
{
    const MESSAGE_TYPES = ['success', 'alert', 'warning', 'info', 'error', 'debug'];

    /**
     * Variable $messages
     *
     * @var string[][]
     */
    protected array $messages = [];

    /**
     * Method add
     *
     * @param string $type    type
     * @param string $message message
     *
     * @return self
     */
    public function add(string $type, string $message): self
    {
        $this->validateMessageType($type);
        if (!isset($this->messages[$type])) {
            $this->messages[$type] = [];
        }
        if (!in_array($message, $this->messages[$type], true)) {
            $this->messages[$type][] = $message;
        }
        return $this;
    }
    
    /**
     * Method validateMessageType
     *
     * @param string $type type
     *
     * @return void
     * @throws RuntimeException
     */
    public function validateMessageType(string $type): void
    {
        if (!in_array($type, $this::MESSAGE_TYPES, true)) {
            throw new RuntimeException("Invalid message type: '$type'");
        }
    }

    /**
     * Method get
     *
     * @return string[][]
     */
    public function get(): array
    {
        return $this->messages;
    }
}
