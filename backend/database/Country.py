from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class Country(Base):
    __tablename__ = 'countries'

    id = Column(Integer, primary_key=True)
    name = Column(String(50))
    iso_code = Column(String(2))
    icon_file = Column(String(100))
    created = Column(DateTime)
    modified = Column(DateTime)
