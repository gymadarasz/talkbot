<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Library\Validator
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Library\Validator;

use RuntimeException;

/**
 * Rule
 *
 * @category  PHP
 * @package   Madsoft\Library\Validator
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
abstract class Rule
{
    const MESSAGE = 'Invalid value';
    
    /**
     * Method getMessage
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this::MESSAGE;
    }
    
    /**
     * Method apply
     *
     * @param mixed[] $props props
     *
     * @return self
     */
    public function apply(array $props = []): self
    {
        $restprops = $props;
        foreach (array_keys(get_object_vars($this)) as $key) {
            $restprops = $this->applyKey($restprops, $props, $key);
        }
        if (!empty($restprops)) {
            $class = get_class($this);
            $propstr = implode("', '$class::\$", array_keys($restprops));
            throw new RuntimeException(
                "Trying to apply some non-existing property on rule: '$class', "
                    . "invalid properties: '$propstr'."
            );
        }
        return $this;
    }
    
    /**
     * Method applyKey
     *
     * @param mixed[] $restprops restprops
     * @param mixed[] $props     props
     * @param string  $key       key
     *
     * @return mixed[]
     * @throws RuntimeException
     */
    protected function applyKey(array $restprops, array $props, string $key): array
    {
        if (array_key_exists($key, $props)) {
            if ($this->$key instanceof self) {
                $this->$key = $this->$key->apply($props[$key] ?: []);
                unset($restprops[$key]);
                return $restprops;
            }
            $varType = gettype($this->$key);
            $propType = gettype($props[$key]);
            if ($varType === 'NULL' || $varType === $propType) {
                $this->$key = $props[$key];
                unset($restprops[$key]);
                return $restprops;
            }
            $class = get_class($this);
            throw new RuntimeException(
                "Validation rule property "
                    . "'$class::\$$key' should be a "
                    . "'$varType' but '$propType' given."
            );
        }
        if ($this->$key instanceof self
            && !array_keys(get_object_vars($this->$key))
        ) {
            unset($restprops[$key]);
            return $restprops;
        }
        $class = get_class($this);
        throw new RuntimeException(
            "Missing propery argument contract of rule: '$key', "
                . "invalid property definition for '$class::\$$key'."
        );
    }
    
    /**
     * Method check
     *
     * @param mixed $value value
     *
     * @return bool
     */
    abstract public function check($value): bool;
}
