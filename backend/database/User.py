from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM

from . import Base

class User(Base):
    __tablename__ = 'users'

    id = Column(Integer, primary_key=True)
    username = Column(String(255), nullable=False)
    password = Column(String(50), nullable=False)
    token = Column(String(36))
    name = Column(String(50), nullable=False)
    surname = Column(String(50), nullable=False)
    address = Column(String(255), nullable=False)
    phone = Column(String(50), nullable=False)
    email = Column(String(100), nullable=False)
    active = Column(Integer, nullable=False, server_default=text("0"))
    monitor = Column(Integer, nullable=False, server_default=text("0"))
    country_id = Column(Integer)
    group_id = Column(Integer, nullable=False)
    language_id = Column(Integer)
    parent_id = Column(Integer)
    lft = Column(Integer)
    rght = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class UserNote(Base):
    __tablename__ = 'user_notes'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class UserSetting(Base):
    __tablename__ = 'user_settings'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer, nullable=False)
    name = Column(String(255), nullable=False)
    value = Column(String(255), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class UserStat(Base):
    __tablename__ = 'user_stats'
    __table_args__ = (Index('user_stats_index', 'radacct_id', 'username',
                            'realm', 'nasipaddress', 'nasidentifier',
                            'callingstationid'), )

    id = Column(Integer, primary_key=True)
    radacct_id = Column(Integer, nullable=False)
    username = Column(String(64), nullable=False, server_default=text("''"))
    realm = Column(String(64), server_default=text("''"))
    nasipaddress = Column(
        String(15), nullable=False, server_default=text("''"))
    nasidentifier = Column(
        String(64), nullable=False, server_default=text("''"))
    framedipaddress = Column(
        String(15), nullable=False, server_default=text("''"))
    callingstationid = Column(
        String(50), nullable=False, server_default=text("''"))
    timestamp = Column(
        DateTime,
        nullable=False,
        server_default=text(
            "current_timestamp() ON UPDATE current_timestamp()"))
    acctinputoctets = Column(BigInteger, nullable=False)
    acctoutputoctets = Column(BigInteger, nullable=False)


class UserSsid(Base):
    __tablename__ = 'user_ssids'

    id = Column(Integer, primary_key=True)
    username = Column(
        String(64), nullable=False, index=True, server_default=text("''"))
    ssidname = Column(String(64), nullable=False, server_default=text("''"))
    priority = Column(Integer, nullable=False, server_default=text("1"))
