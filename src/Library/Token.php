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

/**
 * Token
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class Token
{
    protected Encrypter $encrypter;
    
    /**
     * Method __construct
     *
     * @param Encrypter $encrypter encrypter
     */
    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }
    
    /**
     * Method generateToken
     *
     * @return string
     */
    public function generate(): string
    {
        return urlencode(
            base64_encode($this->encrypter->encrypt(md5((string)rand(1, 1000000))))
        );
    }
}
