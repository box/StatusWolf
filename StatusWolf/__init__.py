import logging
from StatusWolf.config import config

# Init logging for the app
logger = logging.getLogger('statuswolf')
default_log_handler = logging.FileHandler(config.logging['APP_LOG_FILE'])
default_log_formatter = logging.Formatter(config.logging['LOG_FORMAT'])
default_log_handler.setFormatter(default_log_formatter)
logger.setLevel(getattr(logging, config.logging['LOGLEVEL']))
logger.addHandler(default_log_handler)

# Constant values

# Times
SECOND = 1
MINUTE = 60
HOUR = 3600
DAY = 86400
WEEK = 604800
MONTH = 2592000
YEAR = 31536000

