from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class AutoContact(Base):
    __tablename__ = 'auto_contacts'

    id = Column(String(36), primary_key=True)
    auto_mac_id = Column(Integer, nullable=False)
    ip_address = Column(String(15), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class AutoDevice(Base):
    __tablename__ = 'auto_devices'

    mac = Column(String(17), primary_key=True)
    username = Column(String(255), nullable=False, server_default=text("''"))


class AutoGroup(Base):
    __tablename__ = 'auto_groups'

    id = Column(Integer, primary_key=True)
    name = Column(String(80), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class AutoMacNote(Base):
    __tablename__ = 'auto_mac_notes'

    id = Column(Integer, primary_key=True)
    auto_mac_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class AutoMac(Base):
    __tablename__ = 'auto_macs'

    id = Column(Integer, primary_key=True)
    name = Column(String(17), nullable=False)
    dns_name = Column(String(255), nullable=False, server_default=text("''"))
    contact_ip = Column(String(17), nullable=False, server_default=text("''"))
    last_contact = Column(DateTime)
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class AutoSetup(Base):
    __tablename__ = 'auto_setups'

    id = Column(Integer, primary_key=True)
    auto_group_id = Column(Integer, nullable=False)
    auto_mac_id = Column(Integer, nullable=False)
    description = Column(String(80), nullable=False)
    value = Column(String(2000), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
