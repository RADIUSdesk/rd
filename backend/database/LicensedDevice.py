from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class LicensedDevice(Base):
    __tablename__ = 'licensed_devices'

    id = Column(Integer, primary_key=True)
    name = Column(String(64), nullable=False, server_default=text("''"))
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("1"))
    master_key = Column(Integer, nullable=False, server_default=text("1"))
    provider_key = Column(Integer, nullable=False, server_default=text("0"))
    user_id = Column(Integer)
    extra_name = Column(String(100), nullable=False, server_default=text("''"))
    extra_value = Column(
        String(100), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
