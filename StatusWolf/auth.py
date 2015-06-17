import importlib
from logging import getLogger
from StatusWolf.config import config

auth_lib = importlib.import_module('{0}'.format(config.auth['AUTH_LIB']))

class User(object):
    id = ''
    active = '0'
    username = ''
    firstname = ''
    lastname = ''
    app_role = ''
    groups = []


    def __init__(self):
        self.logger = getLogger(__name__)


    @classmethod
    def get(cls, search_key=False, search_value=False):
        if search_value:
            user_info = auth_lib.get_user_info(search_key, search_value)
            for id in user_info:
                cls.id = unicode(id)
                cls.username = user_info[id]['username']
                cls.active = user_info[id]['active']
                cls.groups = user_info[id]['groups']
                cls.firstname = user_info[id]['firstname']
                cls.lastname = user_info[id]['lastname']
                cls.app_role = user_info[id]['app_role']

            return cls
        else:
            return None


    @classmethod
    def is_authenticated(cls):
        return True


    @classmethod
    def is_active(cls):
        if cls.active:
            return True
        else:
            return False


    @classmethod
    def is_anonymous(cls):
        return False


    @classmethod
    def get_id(cls):
        return unicode(cls.id)


    @classmethod
    def __repr__(cls):
        return '<User {0}>'.format(cls.username)
