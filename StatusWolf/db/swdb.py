from sqlalchemy import create_engine
from sqlalchemy import Boolean, Column, Integer, String, Text
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker

from StatusWolf.config import config

DBURL = config.database['TYPE']

if config.database['DRIVER'] is not None:
    DBURL += config.database['DRIVER']

DBURL = DBURL + '://{0}:{1}@{2}/{3}'.format(
    config.database['DBUSER'],
    config.database['DBPASS'],
    config.database['DBHOST'],
    config.database['DBNAME'],
)

engine = create_engine(DBURL, echo=True)
Session = sessionmaker(bind=engine)
Base = declarative_base()

class Auth(Base):
    __tablename__ = 'auth'

    username = Column(String(length=50), primary_key=True)
    password = Column(String(length=255), key=True)
    full_name = Column(String(length=255))

    def __repr__(self):
        return "<Auth(username='{0}', password='{1}', full_name='{2}')>".format(
            self.username,
            self.password,
            self.full_name,
        )


class Rank(Base):
    __tablename__ = 'dasbhoard_rank'

    id = Column(String(length=32), primary_key=True)
    count = Column(Integer(length=15), default=0)

    def __repr__(self):
        return "<Rank(id='{0}', count='{1}')>".format(
            self.id,
            self.count
        )


class SavedDashboards(Base):
    __tablename__ = 'saved_dashboards'

    id = Column(String(length=32), primary_key=True)
    title = Column(String(length=255))
    columns = Column(Integer(length=2))
    user_id = Column(Integer(length=11))
    shared = Column(Boolean(create_constraint=True))
    widgets = Column(Text)

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

    id = Column(String(length=32), primary_key=True)
    title = Column(String(length=255))
    user_id = Column(Integer(length=11))
    shared = Column(Boolean(create_constraint=True))
    search_params = Column(Text)
    data_source = Column(String(length=48))

    def __repr__(self):
        return "<SavedSearches(id='{0}', title='{1}', user_id='{2}', shared='{3}', search_params='{4}', data_source='{5}')>".format(
            self.id,
            self.title,
            self.user_id,
            self.shared,
            self.search_params,
            self.data_source,
        )


class Version(Base):
    __tablename__ = 'sw_version',

    version = Column(String(11))

    def __repr__(self):
        return "<Version(version-'{0}')>".format(self.version)


class Users(Base):
    __tablename__ = 'users'

    id = Column(Integer(length=11), primary_key=True)
    username = Column(String(length=32), key=True)
    roles = Column(String(length=255))
    auth_source = Column(String(length=32))

    def __repr__(self):
        return "<Users(id='{0}', username='{1}', roles-'{2}', auth_source='{3}')>".format(
            self.id,
            self.username,
            self.roles,
            self.auth_source,
        )
