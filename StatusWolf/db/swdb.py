import logging
from sqlalchemy import create_engine, MetaData, Table
from sqlalchemy import Boolean, Column, Integer, String, Text
from sqlalchemy.exc import NoSuchTableError
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker
from sqlalchemy.orm.exc import MultipleResultsFound

from StatusWolf.config import config

logger = logging.getLogger('statuswolf')

DBURL = config.database['TYPE']

if config.database['DRIVER'] is not None:
    DBURL += config.database['DRIVER']

if config.database['DBPORT'] is not None:
    db_host = config.database['DBHOST'] + ':' + config.database['DBPORT']
else:
    db_host = config.database['DBHOST']

DBURL = DBURL + '://{0}:{1}@{2}/{3}'.format(
    config.database['DBUSER'],
    config.database['DBPASS'],
    db_host,
    config.database['DBNAME'],
)

logger.debug('Connecting to database with URL {0}'.format(DBURL))
engine = create_engine(DBURL, echo=True)
Session = sessionmaker(bind=engine)
Base = declarative_base()


class SWDatabaseError(Exception):
    """
    Exception class for errors with the database

    """
    pass


class SWNoTablesError(Exception):
    """
    Exception class to catch when the database has not
    been constructed
    """
    pass


class Auth(Base):
    __tablename__ = 'auth'

    username = Column(String(50), primary_key=True, nullable=False, default='')
    password = Column(String(255), nullable=False, default='')
    full_name = Column(String(255), nullable=False, default='')

    def __repr__(self):
        return "<Auth(username='{0}', password='{1}', full_name='{2}')>".format(
            self.username,
            self.password,
            self.full_name,
        )


class Rank(Base):
    __tablename__ = 'dashboard_rank'

    id = Column(String(32), primary_key=True, nullable=False, default='')
    count = Column(Integer, default=0)

    def __repr__(self):
        return "<Rank(id='{0}', count='{1}')>".format(
            self.id,
            self.count
        )


class SavedDashboards(Base):
    __tablename__ = 'saved_dashboards'

    id = Column(String(32), primary_key=True, nullable=False, default='')
    title = Column(String(255), nullable=False, default='')
    columns = Column(Integer)
    user_id = Column(Integer, nullable=False)
    shared = Column(Boolean(create_constraint=True), nullable=False)
    widgets = Column(Text, nullable=False)

    def __repr__(self):
        return "<SavedDashboards(id='{0}', title='{1}', columns='{2}', user_id='{3}', shared='{4}', data_source='{5}'>".format(
            self.id,
            self.title,
            self.columns,
            self.user_id,
            self.shared,
            self.widgets,
        )


class SavedSearches(Base):
    __tablename__ = 'saved_searches'

    id = Column(String(32), primary_key=True, nullable=False)
    title = Column(String(255), nullable=False)
    user_id = Column(Integer, nullable=False)
    shared = Column(Boolean(create_constraint=True), default=0)
    search_params = Column(Text, nullable=False)
    data_source = Column(String(48), nullable=False, default='')

    def __repr__(self):
        return "<SavedSearches(id='{0}', title='{1}', user_id='{2}', shared='{3}', search_params='{4}', data_source='{5}')>".format(
            self.id,
            self.title,
            self.user_id,
            self.shared,
            self.search_params,
            self.data_source,
        )


class SWVersion(Base):
    __tablename__ = 'sw_version'

    version = Column(String(11), primary_key=True)

    def __repr__(self):
        return "<SWVersion(version='{0}')>".format(self.version)


class Users(Base):
    __tablename__ = 'users'

    id = Column(Integer, primary_key=True, nullable=False)
    username = Column(String(32), nullable=False, default='')
    roles = Column(String(255), nullable=False, default='')
    auth_source = Column(String(32), nullable=False, default='')

    def __repr__(self):
        return "<Users(id='{0}', username='{1}', roles-'{2}', auth_source='{3}')>".format(
            self.id,
            self.username,
            self.roles,
            self.auth_source,
        )


def verify_db():
    """
    Tries to connect to the database and verify that the tables exist, and the default
    entries are there.

    """
    meta = MetaData(bind=engine)
    try:
        version_table = Table('sw_version', meta, autoload=True, autoload_with=engine)
        users_table = Table('users', meta, autoload=True, autoload_with=engine)
        saved_searches_table = Table('saved_searches', meta, autoload=True, autoload_with=engine)
        saved_dashboards_table = Table('saved_dashboards', meta, autoload=True, autoload_with=engine)
        dashboard_rank_table = Table('dashboard_rank', meta, autoload=True, autoload_with=engine)
        auth_table = Table('auth', meta, autoload=True, autoload_with=engine)
    except NoSuchTableError as e:
        raise SWNoTablesError(e)

    session = Session()
    swadmin_auth = session.query(Auth).filter(Auth.username == 'swadmin').first()
    if swadmin_auth is None:
        swadmin_data = Auth(username='swadmin', password='806aab343b43ca141f89f996e58f8667', full_name='StatusWolf Admin')
        session.add(swadmin_data)
        session.commit()
    swadmin_user = session.query(Users).filter(Users.username == 'swadmin').first()
    if swadmin_user is None:
        swadmin_user_data = Users(username='swadmin', roles='ROLE_SUPER_USER', auth_source='{0}'.format(config.database['TYPE']))
        session.add(swadmin_user_data)
        session.commit()


def create_schema():
    Base.metadata.create_all(engine)
    verify_db()


def check_sw_version(version):
    """
    Simple check to validate the app version in the database vs. the
    version the application has

    """
    session = Session()
    query = session.query(SWVersion)
    try:
        db_version = query.one()
    except MultipleResultsFound as e:
        raise SWDatabaseError(e)
    if db_version != version:
        return False

    return True
