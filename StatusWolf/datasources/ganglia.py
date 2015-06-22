import datetime
import logging
import re
import select
import socket
from urllib import quote
from xml.etree import ElementTree
from xml.parsers.expat import ExpatError

from StatusWolf.config import config


datasource_info = {
    'name': 'Ganglia',
    'description': 'Metrics data from Ganglia'
}

ganglia_config = config.datasource['ganglia']

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


class Elem:
    def __init__(self, elem):
        self.elem = elem

    def __getattr__(self, item):
        return self.elem.get(item.upper())


class NullElem:
    def __getattr__(self, item):
        return None


class ApiMetric:
    tag_re = re.compile("\s+")

    def id(self):
        group = self.group if self.group is not None else ""
        id_elements = [self.environment, self.grid.name, self.cluster.name, self.host.name, group, self.name]
        return str.lower(".".join(filter(lambda (e): e is not None, id_elements)))

    def api_dict(self):
        type, units = ApiMetric.metric_type(self.type, self.units, self.slope)
        metric = {
            'environment': self.environment,
            'service': self.grid.name,
            'cluster': self.cluster.name,
            'host': self.host.name,
            'id': self.id(),
            'metric': self.name,
            'instance': self.instance,
            'group': self.group,
            'title': self.title,
            'tags': ApiMetric.parse_tags(self.host.tags),
            'description': self.desc,
            'sum': self.sum,
            'num': self.num,
            'value': ApiMetric.is_num(self.val),
            'units': units,
            'type': type,
            'sampleTime': datetime.datetime.fromtimestamp(
                int(self.host.reported) + int(self.host.tn) - int(self.tn)
            ).isoformat() + ".000Z",
            'graphUrl': self.graph_url,
            'dataUrl': self.data_url,
        }
        return dict(filter(lambda (k,v): v is not None, metric.Items()))

    @staticmethod
    def parse_tags(tag_string):
        if tag_string is None:
            return None
        else:
            tags = ApiMetric.tag_re.split(tag_string)
            if "unspecified" in tags:
                return list()
            else:
                return tags

    @staticmethod
    def metric_type(type, units, slope):
        if units == "timestamp":
            return 'timestamp', 's'
        if 'int' in type or type == 'float' or type == 'double':
            return 'gauge', units
        if type == "string":
            return 'text', units
        return 'undefined', units

    @staticmethod
    def is_num(val):
        try:
            return int(val)
        except ValueError:
            pass
        try:
            return float(val)
        except Valueerror:
            return val

    def __str__(self):
        return "{0} {1} {2} {3} {4} {5}".format(
            self.environment,
            self.grid.name,
            self.cluster.name,
            self.host.name,
            self.group,
            self.name,
        )


class Metric(Elem, ApiMetric):
    def __init__(self, elem, host, cluster, grid, environment):
        self.host = host
        self.cluster = cluster
        self.grid = grid
        self.environment = environment
        Elem.__init__(self, elem)
        self.metadata = dict()
        for extra_data in elem.findall("EXTRA_DATA"):
            for extra_element in extra_data.findall("EXTRA_ELEMENT"):
                name = extra_element.get("NAME")
                if name:
                    self.metadata[name] = extra_element.get("VAL")

        original_metric_name = self.name

        try:
            self.metadata['NAME'], self.metadata['INSTANCE'] = self.name.split('-', 1)
        except ValueError:
            self.metadata['INSTANCE'] = ""

        if self.name in ['fs_util', 'inode_util']:
            if self.instance == "rootfs":
                self.metadata['INSTANCE'] = '/'
            else:
                self.metadata['INSTANCE'] = '/' + '/'.join(self.instance.split('-'))

        params = {
            'environment': self.environment,
            'service': self.grid.name,
            'cluster': self.cluster.name,
            'host': self.host.name,
            'metric': original_metric_name,
        }
        url = "{0}/ganglia/api/v1/metrics?".format(ganglia_config['gmetad_host'])
        for (k, v) in params.items():
            if v is not None:
                url += '&{0}={1}'.format(k, quote(v))
        self.graphUrl = url

        def __getattr__(self, item):
            try:
                if self.metadata.has_key(name.upper()):
                    return self.metadata[name.upper()]
                else:
                    return Elem.__getattr__(self, name)
            except AttributeError:
                return None

        def html_dir(self):
            return "ganglia-" + self.environment + "-" + self.grid.name


class HeartbeatMetric(ApiMetric):
    def __init__(self, host, cluster, grid, environment):
        self.host = host
        self.cluster = cluster
        self.grid = grid
        self.environment = environment
        self.val = int(host.tn)
        self.tn = 0
        self.tags = host.tags
        self.name = "heartbeat"
        self.group = "ganglia"
        self.title = "Ganglia Agent Heartbeat"
        self.desc = "Ganglia agent heartbeat in seconds"
        self.type = 'uint16'
        self.units = 'seconds'
        self.slope = 'both'


class Gmetad:

    def __init__(self, environment):
        self.environment = environment
        self.gmetad_host = ganglia_config['gmetad_host']
        self.gmetad_xml_port = int(ganglia_config['gmetad_xml_port'])
        self.getad_interactive_port = int(ganglia_config['gmetad_interactive_port'])


    def get_gmetad_data(self, host, port, send=None):
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(10)
        try:
            sock.connect((host, port))
            r, w, x = select.select([sock], [], [], 2)
            if not r:
                sock.close()
                return
        except socket.timeout as e:
            logger.warning('Unable to connect to gmetad host {0} on port {1}: {2}'.format(
                host,
                port,
                e,
            ))
            return

        buffer = ''
        while True:
            try:
                gmetad_data = sock.recv(8192)
            except socket.error as e:
                logger.warning('Unable to retrieve gmetad data from host {0} on port {1}: {2}'.format(
                    host,
                    port,
                    e,
                ))
            if not gmetad_data:
                break
            buffer += gmetad_data.decode("ISO-8859-1")

        sock.close()
        return buffer


    def get_gmetad_xml(self):
        return self.get_gmetad_data(self.gmetad_host, self.gmetad_xml_port)


    def get_gmetad_xml_metric(self):
        result = list()

        metrics_xml = self.get_gmetad_xml()
        if metrics_xml:
            try:
                ganglia_metrics = ElementTree.XML(metrics_xml)
            except UnicodeEncodeError as e:
                logger.error('Unable to parse XML data: {0}'.format(e))
        else:
            return result

        for grid_element in ganglia_metrics.findall("GRID"):
            grid = Elem(grid_element)
            for cluster_elem in grid_element.findall("CLUSTER"):
                cluster = Elem(cluster_elem)
                for host_elem in cluster_elem.findall("HOST"):
                    host = Elem(host_elem)
                    result.append(HeartbeatMetric(host, cluster, grid, self.environment))
                    for metric_element in host_elem.findall("METRIC"):
                        result.append(Metric(metric_element, host, cluster, grid, self.environment))

        return result