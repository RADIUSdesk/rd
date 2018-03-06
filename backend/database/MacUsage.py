from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class MacUsage(Base):
    __tablename__ = 'mac_usages'

    id = Column(Integer, primary_key=True)
    mac = Column(String(17), nullable=False)
    username = Column(String(255), nullable=False, server_default=text("''"))
    data_used = Column(BigInteger)
    data_cap = Column(BigInteger)
    time_used = Column(Integer)
    time_cap = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
