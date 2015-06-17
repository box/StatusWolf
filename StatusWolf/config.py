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

    logging = {
        'LOGLEVEL': 'INFO',
        'LOG_FORMAT': '%(asctime)s %(name)s [%(levelname)s]: %(message)s:',
        'APP_LOG_FILE': '/var/tmp/sw_app.log',
    }