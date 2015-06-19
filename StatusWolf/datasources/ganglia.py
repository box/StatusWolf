import logging
from StatusWolf.config import config


datasource_info = {
    'name': 'Ganglia',
    'description': 'Metrics data from Ganglia'
}

logger = logging.getLogger('statuswolf')


def config_help():
    config_options = """
To configure the Ganglia datasource plugin, add the following to the
"datasource" block in the sw_datasource.json file in the StatusWolf
config directory, with appropriate values filled in:


"ganglia": {
    "enabled": true,
    "gmetad_host": "<hostname>",
    "gmetad_xml_port": "8651",
    "gmetad_interactive_port": "8652",
    "proxy": false
}
"""
    return config_options


