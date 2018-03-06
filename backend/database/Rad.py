from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class Radacct(Base):
    __tablename__ = 'radacct'

    radacctid = Column(BigInteger, primary_key=True)
    acctsessionid = Column(
        String(64), nullable=False, index=True, server_default=text("''"))
    acctuniqueid = Column(
        String(32), nullable=False, unique=True, server_default=text("''"))
    username = Column(
        String(64), nullable=False, index=True, server_default=text("''"))
    groupname = Column(String(64), nullable=False, server_default=text("''"))
    realm = Column(String(64), server_default=text("''"))
    nasipaddress = Column(
        String(15), nullable=False, index=True, server_default=text("''"))
    nasidentifier = Column(
        String(64), nullable=False, index=True, server_default=text("''"))
    nasportid = Column(String(15))
    nasporttype = Column(String(32))
    acctstarttime = Column(DateTime, index=True)
    acctupdatetime = Column(DateTime)
    acctstoptime = Column(DateTime, index=True)
    acctinterval = Column(Integer, index=True)
    acctsessiontime = Column(Integer, index=True)
    acctauthentic = Column(String(32))
    connectinfo_start = Column(String(50))
    connectinfo_stop = Column(String(50))
    acctinputoctets = Column(BigInteger)
    acctoutputoctets = Column(BigInteger)
    calledstationid = Column(
        String(50), nullable=False, server_default=text("''"))
    callingstationid = Column(
        String(50), nullable=False, server_default=text("''"))
    acctterminatecause = Column(
        String(32), nullable=False, server_default=text("''"))
    servicetype = Column(String(32))
    framedprotocol = Column(String(32))
    framedipaddress = Column(
        String(15), nullable=False, index=True, server_default=text("''"))
    acctstartdelay = Column(Integer)
    acctstopdelay = Column(Integer)
    xascendsessionsvrkey = Column(String(20))


class Radcheck(Base):
    __tablename__ = 'radcheck'

    id = Column(Integer, primary_key=True)
    username = Column(
        String(64), nullable=False, index=True, server_default=text("''"))
    attribute = Column(String(64), nullable=False, server_default=text("''"))
    op = Column(String(2), nullable=False, server_default=text("'=='"))
    value = Column(String(253), nullable=False, server_default=text("''"))


class Radgroupcheck(Base):
    __tablename__ = 'radgroupcheck'

    id = Column(Integer, primary_key=True)
    groupname = Column(
        String(64), nullable=False, index=True, server_default=text("''"))
    attribute = Column(String(64), nullable=False, server_default=text("''"))
    op = Column(String(2), nullable=False, server_default=text("'=='"))
    value = Column(String(253), nullable=False, server_default=text("''"))
    comment = Column(String(253), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class Radgroupreply(Base):
    __tablename__ = 'radgroupreply'

    id = Column(Integer, primary_key=True)
    groupname = Column(
        String(64), nullable=False, index=True, server_default=text("''"))
    attribute = Column(String(64), nullable=False, server_default=text("''"))
    op = Column(String(2), nullable=False, server_default=text("'='"))
    value = Column(String(253), nullable=False, server_default=text("''"))
    comment = Column(String(253), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class Radippool(Base):
    __tablename__ = 'radippool'
    __table_args__ = (Index('radippool_nasip_poolkey_ipaddress',
                            'nasipaddress', 'pool_key', 'framedipaddress'),
                      Index('radippool_poolname_expire', 'pool_name',
                            'expiry_time'))

    id = Column(Integer, primary_key=True)
    pool_name = Column(String(30), nullable=False)
    framedipaddress = Column(
        String(15), nullable=False, index=True, server_default=text("''"))
    nasipaddress = Column(
        String(15), nullable=False, server_default=text("''"))
    calledstationid = Column(String(30), nullable=False)
    callingstationid = Column(String(30), nullable=False)
    expiry_time = Column(DateTime)
    username = Column(String(64), nullable=False, server_default=text("''"))
    pool_key = Column(String(30), nullable=False, server_default=text("''"))
    nasidentifier = Column(
        String(64), nullable=False, server_default=text("''"))
    extra_name = Column(String(100), nullable=False, server_default=text("''"))
    extra_value = Column(
        String(100), nullable=False, server_default=text("''"))
    active = Column(Integer, nullable=False, server_default=text("1"))
    permanent_user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class Radpostauth(Base):
    __tablename__ = 'radpostauth'

    id = Column(Integer, primary_key=True)
    username = Column(String(64), nullable=False, server_default=text("''"))
    realm = Column(String(64))
    _pass = Column(
        'pass', String(64), nullable=False, server_default=text("''"))
    reply = Column(String(32), nullable=False, server_default=text("''"))
    nasname = Column(String(128), nullable=False, server_default=text("''"))
    authdate = Column(
        DateTime,
        nullable=False,
        server_default=text(
            "current_timestamp() ON UPDATE current_timestamp()"))


class Radreply(Base):
    __tablename__ = 'radreply'

    id = Column(Integer, primary_key=True)
    username = Column(
        String(64), nullable=False, index=True, server_default=text("''"))
    attribute = Column(String(64), nullable=False, server_default=text("''"))
    op = Column(String(2), nullable=False, server_default=text("'='"))
    value = Column(String(253), nullable=False, server_default=text("''"))


class Radusergroup(Base):
    __tablename__ = 'radusergroup'

    id = Column(Integer, primary_key=True)
    username = Column(
        String(64), nullable=False, index=True, server_default=text("''"))
    groupname = Column(String(64), nullable=False, server_default=text("''"))
    priority = Column(Integer, nullable=False, server_default=text("1"))
