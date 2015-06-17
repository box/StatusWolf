import json
import os

from StatusWolf import constants

class config(object):

    app = {
        'SECRET_KEY': 'Art3mis',
        'API_ACCESS_DOMAINS': False,
        'APP_BASE_DIR': '',
        'CLUSTERED': False,
        'CLUSTER_NAME': '',
        'CSRF_ENABLED': True,
        'EMAIL_DOMAIN': '',
        'JOB_MASTER': True,
        'PLUGIN_DIR': 'plugins',
        'TIMEZONE': 'US/Pacific',
    }

    auth = {
        'AUTH_LIB': 'ldap',
    }

    database = {
        'TYPE': 'mysql',
        'DRIVER': None,
        'DBHOST': '',
        'DBPORT': '',
        'DBNAME': 'statuswolf',
        'DBUSER': '',
        'DBPASS': '',
    }

    datasource = {}

    logging = {
        'LOGLEVEL': 'INFO',
        'LOG_FORMAT': '%(asctime)s %(name)s [%(levelname)s]: %(message)s:',
        'APP_LOG_FILE': 'sw_app.log',
    }


loaded_config = {}
for config_file in os.listdir(os.path.join(constants.APP_BASE, 'config')):
    if '.json' in config_file and 'example' not in config_file:
        cf = open(os.path.join(constants.APP_BASE, 'config', config_file), 'r')
        loaded_config.update(json.load(cf))

for conf_attr in loaded_config:
    if hasattr(config, conf_attr):
        master_object = getattr(config, conf_attr)
        master_object.update(loaded_config[conf_attr])
    else:
        setattr(config, conf_attr, loaded_config[conf_attr])