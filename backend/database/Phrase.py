from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class PhraseKey(Base):
    __tablename__ = 'phrase_keys'

    id = Column(Integer, primary_key=True)
    name = Column(String(100))
    comment = Column(String(255))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class PhraseValue(Base):
    __tablename__ = 'phrase_values'

    id = Column(Integer, primary_key=True)
    country_id = Column(Integer)
    language_id = Column(Integer)
    phrase_key_id = Column(Integer)
    name = Column(String(100))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
