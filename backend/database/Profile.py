from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from . import Base


class Profile(Base):
    __tablename__ = 'profiles'

    id = Column(Integer, primary_key=True)
    name = Column(String(128), nullable=False)
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("1"))
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class ProfileComponentNote(Base):
    __tablename__ = 'profile_component_notes'

    id = Column(Integer, primary_key=True)
    profile_component_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class ProfileComponent(Base):
    __tablename__ = 'profile_components'

    id = Column(Integer, primary_key=True)
    name = Column(String(128), nullable=False)
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("1"))
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class ProfileNote(Base):
    __tablename__ = 'profile_notes'

    id = Column(Integer, primary_key=True)
    profile_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
