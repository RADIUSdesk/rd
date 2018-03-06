from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class Voucher(Base):
    __tablename__ = 'vouchers'

    id = Column(Integer, primary_key=True)
    name = Column(String(64), unique=True)
    batch = Column(String(128), nullable=False, server_default=text("''"))
    status = Column(
        ENUM('new', 'used', 'depleted', 'expired'),
        server_default=text("'new'"))
    perc_time_used = Column(Integer)
    perc_data_used = Column(Integer)
    last_accept_time = Column(DateTime)
    last_reject_time = Column(DateTime)
    last_accept_nas = Column(String(128))
    last_reject_nas = Column(String(128))
    last_reject_message = Column(String(255))
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    extra_name = Column(String(100), nullable=False, server_default=text("''"))
    extra_value = Column(
        String(100), nullable=False, server_default=text("''"))
    password = Column(String(30), nullable=False, server_default=text("''"))
    realm = Column(String(50), nullable=False, server_default=text("''"))
    realm_id = Column(Integer)
    profile = Column(String(50), nullable=False, server_default=text("''"))
    profile_id = Column(Integer)
    expire = Column(String(10), nullable=False, server_default=text("''"))
    time_valid = Column(String(10), nullable=False, server_default=text("''"))
    data_used = Column(BigInteger)
    data_cap = Column(BigInteger)
    time_used = Column(Integer)
    time_cap = Column(Integer)
