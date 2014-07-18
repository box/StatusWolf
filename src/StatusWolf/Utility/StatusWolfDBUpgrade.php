<?php
/**
 * StatusWolfDBUpgrade
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 19 March 2014
 *
 */

namespace StatusWolf\Utility;

use Silex\Application;
use Doctrine\DBAL\Connection;

class StatusWolfDBUpgrade {

    public function upgrade_users_table(Connection $db) {
        $user_map_sql = "SELECT * from user_map";
        $users = array();
        $sources = array(
            'MDB2' => 'mysql',
            'LDAP' => 'ldap'
        );
        $user_map_query = $db->prepare($user_map_sql);
        $user_map_query->execute();
        while ($user_entry = $user_map_query->fetch()) {
            $user_entry['source'] = $sources[$user_entry['source']];
            array_push($users, $user_entry);
        }

        $usertable_sql = "CREATE TABLE users ( " .
            "id int(11) unsigned NOT NULL AUTO_INCREMENT, " .
            "username varchar(32) NOT NULL DEFAULT '', " .
            "roles varchar(255) NOT NULL DEFAULT '', " .
            "auth_source varchar(32) NOT NULL DEFAULT '', " .
            "PRIMARY KEY (id), " .
            "KEY username (username)) " .
            "ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $create_usertable = $db->prepare($usertable_sql);
        $create_usertable->execute();
        $db->insert('users', array('username' => 'swadmin', 'roles' => 'ROLE_SUPER_USER', 'auth_source' => 'mysql'));
        foreach ($users as $user) {
            $db->insert('users', array('username' => $user['username'], 'roles' => 'ROLE_USER', 'auth_source' => $user['source']));
        }

        $new_user_sql = "SELECT u.username, u.id, um.id as old_id FROM users u, user_map um WHERE u.username=um.username";
        $new_user_query = $db->prepare($new_user_sql);
        $new_user_query->execute();
        $user_id_changes = array();
        while($uid_map = $new_user_query->fetch()) {
            $user_id_changes[$uid_map['old_id']] = $uid_map['id'];
        }

        $db->executeQuery("RENAME TABLE user_map TO user_map_delete");

//        $db->executeQuery("DROP TABLE user_map");

        return $user_id_changes;
    }

    public function migrate_saved_dashboards(Connection $db, $uid_map) {

        $new_dashboard_query = "CREATE TABLE saved_dashboards_new ( " .
            "id varchar(32) NOT NULL DEFAULT '', " .
            "title varchar(255) NOT NULL DEFAULT '', " .
            "columns int(2) DEFAULT NULL, " .
            "user_id int(11) NOT NULL, " .
            "shared tinyint(1) NOT NULL, " .
            "widgets mediumtext NOT NULL, " .
            "PRIMARY KEY (id)) " .
            "ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $db->executeQuery($new_dashboard_query);

        $get_old_sql = "SELECT * FROM saved_dashboards";
        $get_old = $db->prepare($get_old_sql);
        $get_old->execute();
        while($dashboard = $get_old->fetch()) {
            if (empty($dashboard['columns']) || is_null($dashboard['columns'])) {
                $dashboard['columns'] = 2;
            }
            $dashboard['user_id'] = $uid_map[$dashboard['user_id']];
            $widgets = unserialize($dashboard['widgets']);
            foreach ($widgets as $widget => $widget_config) {
                $old_options = false;
                if (array_key_exists('cache_key', $widget_config)) {
                    unset($widget_config['cache_key']);
                }
                $widget_config['widget_type'] = 'opentsdbwidget';
                if (array_key_exists('options', $widget_config)) {
                    $old_options = $widget_config['options'];
                }
                $widget_config['options'] = array();
                if ($old_options) {
                    $widget_config['options']['disabled'] = $old_options['disabled'] ?: '';
                    $widget_config['options']['label'] = $old_options['label'] ?: '';
                    $widget_config['options']['legend'] = $old_options['legend'] ?: 'on';
                    $widget_config['options']['nointerpolation'] = $old_options['nointerpolation'] ?: true;
                }
                $widgets[$widget] = $widget_config;
            }
            $dashboard['widgets'] = serialize($widgets);

            $db->insert('saved_dashboards_new', array(
                'id' => $dashboard['id'],
                'title' => $dashboard['title'],
                'columns' => $dashboard['columns'],
                'user_id' => $dashboard['user_id'],
                'shared' => $dashboard['shared'],
                'widgets' => $dashboard['widgets']
            ));
        }

        $db->executeQuery("RENAME TABLE saved_dashboards TO saved_dashboards_delete");
        $db->executeQuery("RENAME TABLE saved_dashboards_new TO saved_dashboards");

    }

    public function migrate_saved_searches(Connection $db, $uid_map) {

        $new_searches_query = "CREATE TABLE saved_searches_new ( " .
            "id varchar(32) NOT NULL, " .
            "title varchar(255) NOT NULL, " .
            "user_id int(11) NOT NULL, " .
            "shared tinyint(1) NOT NULL DEFAULT 0, " .
            "search_params mediumtext NOT NULL, " .
            "data_source varchar(48) NOT NULL DEFAULT '', " .
            "PRIMARY KEY (id)) " .
            "ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $db->executeQuery($new_searches_query);

        $get_old = $db->prepare("SELECT * FROM saved_searches");
        $get_old->execute();
        while($old_search = $get_old->fetch()) {
            $params = unserialize($old_search['search_params']);
            if (array_key_exists('datasource',$params)) {
                unset($params['datasource']);
            }
            $old_search['search_params'] = serialize($params);

            $db->insert('saved_searches_new', array(
                'id' => $old_search['id'],
                'title' => $old_search['title'],
                'user_id' => $uid_map[$old_search['user_id']],
                'shared' => abs($old_search['private'] - 1),
                'search_params' => $old_search['search_params'],
                'data_source' => 'OpenTSDB'
            ));
        }

        $db->executeQuery("RENAME TABLE saved_searches TO saved_searches_delete");
        $db->executeQuery("RENAME TABLE saved_searches_new TO saved_searches");

    }

}
