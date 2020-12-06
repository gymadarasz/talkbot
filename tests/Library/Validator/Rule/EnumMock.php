<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */

namespace Madsoft\Tests\Library\Validator\Rule;

use Madsoft\Library\Validator\Rule\Enum;

/**
 * EnumMock
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class EnumMock extends Enum
{
    /**
     * Method setValues
     *
     * @param string[] $values values
     *
     * @return self
     */
    public function setValues(array $values): self
    {
        $this->values = $values;
        return $this;
    }
}
