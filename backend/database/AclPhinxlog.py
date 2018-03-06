from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class AclPhinxlog(Base):
    __tablename__ = 'acl_phinxlog'

    version = Column(BigInteger, primary_key=True)
    migration_name = Column(String(100))
    start_time = Column(
        DateTime, nullable=False, server_default=text("current_timestamp()"))
    end_time = Column(
        DateTime, nullable=False, server_default=text("current_timestamp()"))
