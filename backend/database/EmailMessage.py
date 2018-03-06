from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class EmailMessage(Base):
    __tablename__ = 'email_messages'

    id = Column(Integer, primary_key=True)
    name = Column(String(64), nullable=False, server_default=text("''"))
    title = Column(String(64), nullable=False, server_default=text("''"))
    message = Column(String(255), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
