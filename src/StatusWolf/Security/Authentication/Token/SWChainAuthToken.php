<?php
/**
 * LdapToken
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 27 February 2014
 *
 */

namespace StatusWolf\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SWChainAuthToken extends AbstractToken {
    private $_user_credentials;
    private $_provider_key;

    public function __construct($username, $user_credentials, $provider_key, array $roles = array()) {

        parent::__construct($roles);

        if (empty($provider_key)) {
            throw new \InvalidArgumentException('provider_key must not be empty');
        }

        $this->setUser($username);
        $this->_user_credentials = $user_credentials;
        $this->_provider_key = $provider_key;

        parent::setAuthenticated(count($roles) > 0);
    }

    public function setAuthenticated($authenticated) {
        if ($authenticated) {
            throw new \LogicException('Cannot set token to trusted after instantiation');
        }
        parent::setAuthenticated(false);
    }

    public function getCredentials() {
        return $this->_user_credentials;
    }

    public function getProviderKey() {
        return $this->_provider_key;
    }

    public function eraseCredentials() {
        parent::eraseCredentials();
        $this->_user_credentials = null;
    }

    public function serialize() {
        return serialize(array($this->_user_credentials, $this->_provider_key, parent::serialize()));
    }

    public function unserialize($serialized) {
        list ($this->_user_credentials, $this->_provider_key, $parent_string) = unserialize($serialized);
        parent::unserialize($parent_string);
    }

}
