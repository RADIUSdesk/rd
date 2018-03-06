from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class TagNote(Base):
    __tablename__ = 'tag_notes'

    id = Column(Integer, primary_key=True)
    tag_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class Tag(Base):
    __tablename__ = 'tags'

    id = Column(Integer, primary_key=True)
    name = Column(String(128), nullable=False)
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("1"))
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
