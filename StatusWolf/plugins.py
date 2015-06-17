import imp
import os
from StatusWolf.config import config

plugin_dir = config.app['PLUGIN_DIR']


def get_plugin_types():
    """
    Reads the plugins directory and returns the available plugin types

    Returns:
        (list): The available plugin types

    """
    types = []
    type_candidates = os.listdir(plugin_dir)
    for type in type_candidates:
        if os.path.isdir(os.path.join(plugin_dir, type)):
            types.append(type)

    return types


def get_plugins(type):
    """
    Finds the list of all plugins of the given type. Plugin types are
    grouped by directory under the top-level plugins directory.

    Args:
        type (str): The type of plugins to load

    Returns:
        (list): The available plugins of the given type.

    """
    plugins = []
    candidates = os.listdir(os.path.join(plugin_dir, type))
    for candidate in candidates:
        location = os.path.join(plugin_dir, type, candidate)
        if not os.path.isdir(location) or not '__init__.py' in os.listdir(location):
            continue
        info = imp.find_module('__init__', [location])
        plugins.append({'name': candidate, 'info': info})

    return plugins


def load_plugin(plugin):
    return imp.load_module('__init__', *plugin['info'])