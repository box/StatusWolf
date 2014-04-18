<?php
/**
 * StatusWolfUser
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 27 February 2014
 *
 */

namespace StatusWolf\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class SWUser implements UserInterface, \Serializable {

    private $_id;
    private $_username;
    private $_password;
    private $_full_name;
    private $_roles;
    private $_auth_source;

    public function __construct(array $user) {
        $this->_id = $user['id'];
        $this->_username = $user['username'];
        $this->_password = $user['password'];
        $this->_full_name = $user['full_name'];
        $this->_roles = $user['roles'];
        $this->_auth_source = $user['auth_source'];
    }

    /**
     * @inheritDoc
     */
    public function getUsername() {
        return $this->_username;
    }

    /**
     * @inheritDoc
     */
    public function getSalt() {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getPassword() {
        return $this->_password;
    }

    /**
     * @inheritDoc
     */
    public function getRoles() {
        return $this->_roles;
    }

    public function getFullName() {
        return $this->_full_name;
    }

    public function getId() {
        return $this->_id;
    }

    public function getAuthSource() {
        return $this->_auth_source;
    }

    public function setPassword($password) {
        $this->_password = $password;
    }

    public function setFullName($full_name) {
        $this->_full_name = $full_name;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials() {}

    public function serialize() {
        return serialize(array(
            $this->_id,
            $this->_username,
            $this->_password,
            $this->_full_name,
            $this->_auth_source
        ));
    }

    public function unserialize($serialized_user) {
        list (
            $this->_id,
            $this->_username,
            $this->_password,
            $this->_full_name,
            $this->_auth_source
        ) = unserialize($serialized_user);
    }

}
