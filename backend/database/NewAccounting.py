from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class NewAccounting(Base):
    __tablename__ = 'new_accountings'

    mac = Column(String(17), primary_key=True)
    username = Column(String(255), nullable=False, server_default=text("''"))
