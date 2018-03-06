from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class Note(Base):
    __tablename__ = 'notes'

    id = Column(Integer, primary_key=True)
    note = Column(Text, nullable=False)
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("1"))
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
