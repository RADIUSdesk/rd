from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class Action(Base):
    __tablename__ = 'actions'

    id = Column(Integer, primary_key=True)
    na_id = Column(Integer, nullable=False)
    action = Column(ENUM('execute'), server_default=text("'execute'"))
    command = Column(String(500), server_default=text("''"))
    status = Column(
        ENUM('awaiting', 'fetched', 'replied'),
        server_default=text("'awaiting'"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
