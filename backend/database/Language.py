from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class Language(Base):
    __tablename__ = 'languages'

    id = Column(Integer, primary_key=True)
    name = Column(String(50))
    iso_code = Column(String(2))
    rtl = Column(Integer, nullable=False, server_default=text("0"))
    created = Column(DateTime)
    modified = Column(DateTime)
