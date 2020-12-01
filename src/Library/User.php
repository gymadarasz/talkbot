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
 * User
 *
 * @category  PHP
 * @package   Madsoft\Library
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) All rights reserved.
 * @link      this
 */
class User
{
    protected Session $session;
    
    /**
     * Method __construct
     *
     * @param Session $session session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
        //        $this->session->destroy();
    }
    
    /**
     * Method getId
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->session->get('user.id', 0);
    }
    
    /**
     * Method setId
     *
     * @param int $uid uid
     *
     * @return self
     */
    public function setId(int $uid): self
    {
        $this->session->set('user.id', $uid);
        return $this;
    }
    
    /**
     * Method getGroup
     *
     * @return string
     */
    public function getGroup(): string
    {
        return $this->session->get('user.group', '');
    }
    
    /**
     * Method setGroup
     *
     * @param string $group group
     *
     * @return self
     */
    public function setGroup(string $group): self
    {
        $this->session->set('user.group', $group);
        return $this;
    }
    
    /**
     * Method isVisitor
     *
     * @return bool
     */
    public function isVisitor(): bool
    {
        return !$this->getId();
    }
    
    /**
     * Method isAdmin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return !$this->isVisitor() && $this->getGroup() === 'admin';
    }
    
    /**
     * Method login
     *
     * @param int    $uid   uid
     * @param string $group group
     *
     * @return self
     */
    public function login(int $uid, string $group): self
    {
        return $this->setId($uid)->setGroup($group);
    }
    
    /**
     * Method logout
     *
     * @return self
     */
    public function logout(): self
    {
        return $this->setId(0)->setGroup('');
    }
}
