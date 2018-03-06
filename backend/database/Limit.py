from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class Limit(Base):
    __tablename__ = 'limits'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer, nullable=False)
    alias = Column(String(100), nullable=False, server_default=text("''"))
    active = Column(Integer, nullable=False, server_default=text("0"))
    count = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
