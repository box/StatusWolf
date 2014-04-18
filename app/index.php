<?php

/**
 * Index page stub for StatusWolf, validates version and hands off
 * to the application. For the first run after installing this new
 * version it will check and convert the config files and the
 * database tables
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 7 April 2014
 *
 */

// For this version, if the new config file format is in place
// we'll assume the upgrade is complete.
if (file_exists(__DIR__ . '/../conf/sw_config.json')) {
    $sw = require_once __DIR__ . '/sw.php';
    $sw->run();
} else {
    require_once __DIR__ . '/static/constants/Constants.php';
    require_once __DIR__ . '/../vendor/autoload.php';

    // Initialize the application object
    $sw = new Silex\Application();
    $sw->register(new \Silex\Provider\MonologServiceProvider(), array(
        'monolog.logfile' => __DIR__ . '/log/sw_log.log',
        'monolog.level' => 'debug',
    ));
    $sw->register(new Silex\Provider\UrlGeneratorServiceProvider());
    $sw->register(new Silex\Provider\TwigServiceProvider(), array(
        'twig.path' => __DIR__ . '/templates',
    ));

    // Verify that the legacy configuration files exist
    if (!file_exists(__DIR__ . '/../conf/statuswolf.conf') ||
            !file_exists(__DIR__ . '/../conf/auth.conf') ||
            !file_exists(__DIR__ . '/../conf/datasource.conf')) {
        $sw->get('/', function() use ($sw) {
            return $sw['twig']->render('no_upgrade.html', array(
                'error_msg' => 'No previous StatusWolf config file(s) found, please create a new sw_config.json file to complete installation.'
            ));
        })->bind('home');
    } elseif (!is_writable(__DIR__ . '/../conf/')) {
        $sw->get('/', function() use ($sw) {
            return $sw['twig']->render('no_upgrade.html', array(
                'error_msg' => 'StatusWolf configuration directory is not writable by the web server user, please change the ownership and try again.'
            ));
        })->bind('home');
    } else {
        $legacy_config = new \StatusWolf\Utility\SWLegacyConfig($sw);
        foreach (array('auth.conf', 'datasource.conf', 'statuswolf.conf') as $conf_file) {
            $legacy_config->load_legacy_config($conf_file);
        }
        $legacy_config_values = $legacy_config->read_values();

        $db_options = array(
            'driver'    => 'pdo_mysql',
            'host'      => $legacy_config_values['statuswolf']['session_handler']['db_host'],
            'dbname'    => $legacy_config_values['statuswolf']['session_handler']['database'],
            'user'      => $legacy_config_values['statuswolf']['session_handler']['db_user'],
            'password'  => $legacy_config_values['statuswolf']['session_handler']['db_password']
        );
        if (array_key_exists('db_socket', $legacy_config_values['statuswolf']['session_handler'])) {
            $db_options['unix_socket'] = $legacy_config_values['statuswolf']['session_handler']['db_socket'];
        }
        $sw->register(new Silex\Provider\DoctrineServiceProvider(), array(
            'db.options' => $db_options,
        ));

        $sw['db_options'] = $db_options;
        $sw['legacy_config'] = $legacy_config_values;

        $sw['db_upgrade'] = new \StatusWolf\Utility\StatusWolfDBUpgrade();

        $sw->get('/', function() use ($sw) {
            $upgrade_js = file_get_contents(__DIR__ . '/static/js/sw_upgrade_0.9.js');
            return $sw['twig']->render('upgrade.html', array(
                'upgrade_script' => $upgrade_js
            ));
        })->bind('home');
        $sw->post('/update_auth_table', function() use ($sw) {
            $pass_hash = md5($_POST['password']);
            try {
                $sw['db']->insert('auth', array('username' => 'swadmin', 'password' => $pass_hash, 'full_name' => 'StatusWolf Admin'));
            } catch(\Exception $e) {
                return $sw->json($e->getMessage(), 500);
            }
            return $sw->json('Success');
        });
        $sw->get('/migrate_user_map', function() use($sw) {
            try {
                $user_id_changes = $sw['db_upgrade']->upgrade_users_table($sw['db']);
            } catch(\Exception $e) {
                return $sw->json($e->getMessage(), 500);
            }
            return $sw->json($user_id_changes);
        });
        $sw->post('/migrate_saved_dashboards', function() use ($sw) {
            try {
                $sw['db_upgrade']->migrate_saved_dashboards($sw['db'], $_POST['uid_map']);
            } catch(\Exception $e) {
                return $sw->json($e->getMessage(), 500);
            }
            return $sw->json('Success');
        });
        $sw->post('/migrate_saved_searches', function() use ($sw) {
            try {
                $sw['db_upgrade']->migrate_saved_searches($sw['db'], $_POST['uid_map']);
            } catch(\Exception $e) {
                return $sw->json($e->getMessage(), 500);
            }
            return $sw->json('Success');
        });
        $sw->get('/create_new_configs', function() use ($sw) {
            $sw['logger']->addDebug('old config: ' . json_encode($sw['legacy_config']));
            $sw_config = $sw['twig']->render('sw_config.twig', array(
                'debug'         => $sw['legacy_config']['statuswolf']['debug'] ? 'true': 'false',
                'db_options'    => $sw['db_options'],
                'default_auth'  => in_array('LDAP', $sw['legacy_config']['auth']['method']) ? 'ldap' : 'mysql',
                'autocreate'    => in_array('LDAP', $sw['legacy_config']['auth']['method']) ? 'true' : 'false',
                'ldap_options'  => in_array('LDAP', $sw['legacy_config']['auth']['method']) ? $sw['legacy_config']['auth']['LDAP'] : false,
                'd3'            => preg_match('/^http/', $sw['legacy_config']['statuswolf']['graphing']['d3_location'], $match) ? $match[0] : 'local'
            ));
            try {
                file_put_contents(__DIR__ . '/../conf/sw_config.json', $sw_config);
            } catch(\Exception $e) {
                return $sw->json($e->getMessage(), 500);
            }
            $datasources = array();
            foreach ($sw['legacy_config']['datasource'] as $datasource => $config) {
                if (is_array($config)) {
                    $ds_string = '        "' . $datasource . '": {';
                    $ds_string .= "\n            \"enabled\": true";
                    foreach ($config as $key => $value) {
                        $ds_string .= ",\n            \"$key\": ";
                        if (is_array($value)) {
                            $ds_string .= "[\n                \"";
                            $ds_string .= implode($value, "\",\n                \"");
                            $ds_string .= "\"\n            ]";
                        } elseif ($value === "true" || $value === "false") {
                            $ds_string .= "$value";
                        } else {
                            $ds_string .= "\"$value\"";
                        }
                    }
                    $ds_string .= "\n        }";
                    array_push($datasources, $ds_string);
                }
            }
            try {
                file_put_contents(__DIR__ . '/../conf/sw_datasource.json', "{\n    \"datasource\": {\n" . implode($datasources, ",\n") . "\n    }\n}\n");
                $sw['db']->executeQuery("CREATE TABLE sw_version ( version varchar(11) DEFAULT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
                $sw['db']->insert('sw_version', array('version' => '0.9'));
            } catch(\Exception $e) {
                return $sw->json($e->getMessage(), 500);
            }
            return $sw->json('Success');
        });
    }

    $sw->run();

}
