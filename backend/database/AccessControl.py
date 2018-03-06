from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class Aro(Base):
    __tablename__ = 'aros'

    id = Column(Integer, primary_key=True)
    parent_id = Column(Integer)
    model = Column(String(255))
    foreign_key = Column(Integer)
    alias = Column(String(255))
    lft = Column(Integer)
    rght = Column(Integer)


class ArosAco(Base):
    __tablename__ = 'aros_acos'
    __table_args__ = (Index('ARO_ACO_KEY', 'aro_id', 'aco_id', unique=True), )

    id = Column(Integer, primary_key=True)
    aro_id = Column(Integer, nullable=False)
    aco_id = Column(Integer, nullable=False)
    _create = Column(String(2), nullable=False, server_default=text("'0'"))
    _read = Column(String(2), nullable=False, server_default=text("'0'"))
    _update = Column(String(2), nullable=False, server_default=text("'0'"))
    _delete = Column(String(2), nullable=False, server_default=text("'0'"))


class Aco(Base):
    __tablename__ = 'acos'

    id = Column(Integer, primary_key=True)
    parent_id = Column(Integer)
    model = Column(String(255))
    foreign_key = Column(Integer)
    alias = Column(String(255))
    comment = Column(String(255))
    lft = Column(Integer)
    rght = Column(Integer)
