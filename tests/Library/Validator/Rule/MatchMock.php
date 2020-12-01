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

use Madsoft\Library\Validator\Rule\Match;

/**
 * MatchMock
 *
 * @category  PHP
 * @package   Madsoft\Tests\Library\Validator\Rule
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class MatchMock extends Match
{
    /**
     * Method setEqualTo
     *
     * @param mixed $equalTo equalTo
     *
     * @return self
     */
    public function setEqualTo($equalTo): self
    {
        $this->equalTo = $equalTo;
        return $this;
    }
}
