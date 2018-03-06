from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class Category(Base):
    __tablename__ = 'categories'

    id = Column(String(36), primary_key=True)
    parent_id = Column(String(36))
    lft = Column(String(36))
    rght = Column(String(36))
    name = Column(String(255), server_default=text("''"))
