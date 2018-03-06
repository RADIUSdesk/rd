from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM

from . import Base


class Ssid(Base):
    __tablename__ = 'ssids'

    id = Column(Integer, primary_key=True)
    name = Column(String(64), nullable=False, server_default=text("''"))
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("1"))
    user_id = Column(Integer)
    extra_name = Column(String(100), nullable=False, server_default=text("''"))
    extra_value = Column(
        String(100), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
