from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class TemplateAttribute(Base):
    __tablename__ = 'template_attributes'

    id = Column(Integer, primary_key=True)
    template_id = Column(Integer)
    attribute = Column(String(128), nullable=False)
    type = Column(ENUM('Check', 'Reply'), server_default=text("'Check'"))
    tooltip = Column(String(200), nullable=False)
    unit = Column(String(100), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class TemplateNote(Base):
    __tablename__ = 'template_notes'

    id = Column(Integer, primary_key=True)
    template_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class Template(Base):
    __tablename__ = 'templates'

    id = Column(Integer, primary_key=True)
    name = Column(String(128), nullable=False)
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("1"))
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
