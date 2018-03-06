from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class Check(Base):
    __tablename__ = 'checks'

    id = Column(Integer, primary_key=True)
    name = Column(String(40), nullable=False)
    value = Column(String(40), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
