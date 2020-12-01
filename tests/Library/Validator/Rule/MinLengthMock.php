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

use Madsoft\Library\Validator\Rule\MinLength;

/**
 * MinLengthMock
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class MinLengthMock extends MinLength
{
    /**
     * Method setMin
     *
     * @param int $min min
     *
     * @return self
     */
    public function setMin(int $min): self
    {
        $this->min = $min;
        return $this;
    }
}
