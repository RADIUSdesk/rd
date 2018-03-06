from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class NaNote(Base):
    __tablename__ = 'na_notes'

    id = Column(Integer, primary_key=True)
    na_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class NaRealm(Base):
    __tablename__ = 'na_realms'

    id = Column(Integer, primary_key=True)
    na_id = Column(Integer, nullable=False)
    realm_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class NaState(Base):
    __tablename__ = 'na_states'

    id = Column(Integer, primary_key=True)
    na_id = Column(String(36), nullable=False)
    state = Column(Integer, nullable=False, server_default=text("0"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class NaTag(Base):
    __tablename__ = 'na_tags'

    id = Column(Integer, primary_key=True)
    na_id = Column(Integer, nullable=False)
    tag_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class Na(Base):
    __tablename__ = 'nas'

    id = Column(Integer, primary_key=True)
    nasname = Column(String(128), nullable=False, index=True)
    shortname = Column(String(32))
    nasidentifier = Column(
        String(64), nullable=False, server_default=text("''"))
    type = Column(String(30), server_default=text("'other'"))
    ports = Column(Integer)
    secret = Column(
        String(60), nullable=False, server_default=text("'secret'"))
    server = Column(String(64))
    community = Column(String(50))
    description = Column(String(200), server_default=text("'RADIUS Client'"))
    connection_type = Column(
        ENUM('direct', 'openvpn', 'pptp', 'dynamic'),
        server_default=text("'direct'"))
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("1"))
    record_auth = Column(Integer, nullable=False, server_default=text("0"))
    ignore_acct = Column(Integer, nullable=False, server_default=text("0"))
    dynamic_attribute = Column(
        String(50), nullable=False, server_default=text("''"))
    dynamic_value = Column(
        String(50), nullable=False, server_default=text("''"))
    monitor = Column(
        ENUM('off', 'ping', 'heartbeat'), server_default=text("'off'"))
    ping_interval = Column(Integer, nullable=False, server_default=text("600"))
    heartbeat_dead_after = Column(
        Integer, nullable=False, server_default=text("600"))
    last_contact = Column(DateTime)
    session_auto_close = Column(
        Integer, nullable=False, server_default=text("0"))
    session_dead_time = Column(
        Integer, nullable=False, server_default=text("3600"))
    on_public_maps = Column(Integer, nullable=False, server_default=text("0"))
    lat = Column(Float(asdecimal=True))
    lon = Column(Float(asdecimal=True))
    photo_file_name = Column(
        String(128), nullable=False, server_default=text("'logo.jpg'"))
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
