import json
import os

class config(object):

    app = {
        'SECRET_KEY': 'Art3mis',
        'APP_BASE_DIR': '',
        'CLUSTERED': False,
        'CLUSTER_NAME': '',
        'JOB_MASTER': True,
        'EMAIL_DOMAIN': '',
        'CSRF_ENABLED': True,
        'API_ACCESS_DOMAINS': False,
        'TIMEZONE': 'US/Pacific',
        'PLUGIN_DIR': 'plugins',
    }

    auth = {
        'AUTH_LIB': 'ldap',
    }

    datasource = {}

    logging = {
        'LOGLEVEL': 'INFO',
        'LOG_FORMAT': '%(asctime)s %(name)s [%(levelname)s]: %(message)s:',
        'APP_LOG_FILE': 'sw_app.log',
    }


loaded_config = {}
for config_file in os.listdir('config'):
    if '.json' in config_file and 'example' not in config_file:
        cf = open(os.path.join('config', config_file), 'r')
        loaded_config.update(json.load(cf))

for conf_attr in loaded_config:
    if hasattr(config, conf_attr):
        master_object = getattr(config, conf_attr)
        master_object.update(loaded_config[conf_attr])
    else:
        setattr(config, conf_attr, loaded_config[conf_attr])