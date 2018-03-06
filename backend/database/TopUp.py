from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class TopUpTransaction(Base):
    __tablename__ = 'top_up_transactions'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    permanent_user_id = Column(Integer)
    permanent_user = Column(String(255))
    top_up_id = Column(Integer)
    type = Column(
        ENUM('data', 'time', 'days_to_use'), server_default=text("'data'"))
    action = Column(
        ENUM('create', 'update', 'delete'), server_default=text("'create'"))
    radius_attribute = Column(
        String(30), nullable=False, server_default=text("''"))
    old_value = Column(String(30))
    new_value = Column(String(30))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class TopUp(Base):
    __tablename__ = 'top_ups'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    permanent_user_id = Column(Integer)
    data = Column(BigInteger)
    time = Column(Integer)
    days_to_use = Column(Integer)
    comment = Column(String(255), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    type = Column(
        ENUM('data', 'time', 'days_to_use'), server_default=text("'data'"))
