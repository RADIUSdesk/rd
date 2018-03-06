from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class DeviceNote(Base):
    __tablename__ = 'device_notes'

    id = Column(Integer, primary_key=True)
    device_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class Device(Base):
    __tablename__ = 'devices'

    id = Column(Integer, primary_key=True)
    name = Column(String(128), nullable=False)
    description = Column(String(255), nullable=False)
    active = Column(Integer, nullable=False, server_default=text("0"))
    last_accept_time = Column(DateTime)
    last_reject_time = Column(DateTime)
    last_accept_nas = Column(String(128))
    last_reject_nas = Column(String(128))
    last_reject_message = Column(String(255))
    permanent_user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    perc_time_used = Column(Integer)
    perc_data_used = Column(Integer)
    data_used = Column(BigInteger)
    data_cap = Column(BigInteger)
    time_used = Column(Integer)
    time_cap = Column(Integer)
    time_cap_type = Column(ENUM('hard', 'soft'), server_default=text("'soft'"))
    data_cap_type = Column(ENUM('hard', 'soft'), server_default=text("'soft'"))
    realm = Column(String(100), nullable=False, server_default=text("''"))
    realm_id = Column(Integer)
    profile = Column(String(100), nullable=False, server_default=text("''"))
    profile_id = Column(Integer)
    from_date = Column(DateTime)
    to_date = Column(DateTime)
