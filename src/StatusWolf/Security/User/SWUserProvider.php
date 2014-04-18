<?php
/**
 * SWUserProvider
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 27 February 2014
 *
 */

namespace StatusWolf\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use StatusWolf\Security\User\SWUser;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\DBAL\Connection;

class SWUserProvider implements UserProviderInterface {

    private $_db;
    private $_auth_config;

    public function __construct(Connection $db, array $auth_config = array()) {
        $this->_db = $db;
        $this->_auth_config = $auth_config;
    }

    public function loadUserByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $user_query = $this->_db->prepare($sql);
        $user_query->bindValue(1, $username);
        $user_query->execute();
        if (!$user = $user_query->fetch()) {
            if ($this->_auth_config['users']['auto_create']) {
                $this->addNewUser($username, $this->_auth_config['users']['default_auth_type'], $this->_auth_config['users']['default_role']);
            } else {
                throw new UsernameNotFoundException(
                    sprintf("User %s not found", $username)
                );
            }
        }
        if ($user['auth_source'] === "mysql") {
            $auth_sql = "SELECT * FROM auth WHERE username = ?";
            $auth_query = $this->_db->prepare($auth_sql);
            $auth_query->bindValue(1, $username);
            $auth_query->execute();
            if (!$auth = $auth_query->fetch()) {
                throw new UsernameNotFoundException(sprintf("User %s not fount", $username));
            }
            $user['password'] = $auth['password'];
            $user['full_name'] = $auth['full_name'];
        } else {
            $user['password'] = '!!';
            $user['full_name'] = '';
        }
        $user['roles'] = explode(',', $user['roles']);

        return new SWUser($user);
    }

    public function refreshUser(UserInterface $user) {
        $user_class = get_class($user);
        if (!$this->supportsClass($user_class)) {
            throw new UnsupportedUserException(
                sprintf("Unsupported user class: %s", $user_class)
            );
        }
        if ($user->getAuthSource() === "ldap") {
            $full_name = $user->getFullName();
        } else {
            $full_name = false;
        }

        $reload_user = $this->loadUserByUsername($user->getUsername());
        if ($full_name) {
            $reload_user->setFullName($full_name);
        }

        return $reload_user;
    }

    public function supportsClass($class) {
        return $class === 'StatusWolf\Security\User\SWUser';
    }

    public function addNewUser($username, $auth_type, $role) {
        $this->_db->insert('users', array('username' => $username, 'roles' => $role, 'auth_source' => $auth_type));
    }

}
