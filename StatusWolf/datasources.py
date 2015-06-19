import imp
import os

from StatusWolf import constants

datasources_dir = os.path.join(constants.APP_BASE, 'datasources')


def get_datasources():
    datasources = []
    candidates = os.listdir(datasources_dir)
    for candidate in candidates:
        location = os.path.join(datasources_dir, candidate)
        if os.path.isdir(location):
            if '__init__.py' in os.listdir(location):
                info = imp.find_module('__init__', [location])
        else:
            candidate_module = candidate.split('.')[0]
            info = imp.find_module(candidate_module, [datasources_dir])
        datasources.append({'name': candidate_module, 'info': info})

    return datasources


def load_datasource(datasource):
    return imp.load_module(datasource['name'], *datasource['info'])