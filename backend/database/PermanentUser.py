from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class PermanentUser(Base):
    __tablename__ = 'permanent_users'

    id = Column(Integer, primary_key=True)
    username = Column(String(255), nullable=False)
    password = Column(String(50), nullable=False)
    token = Column(String(36))
    name = Column(String(50), nullable=False)
    surname = Column(String(50), nullable=False)
    address = Column(String(255), nullable=False)
    phone = Column(String(50), nullable=False)
    email = Column(String(100), nullable=False)
    auth_type = Column(
        String(128), nullable=False, server_default=text("'sql'"))
    active = Column(Integer, nullable=False, server_default=text("0"))
    last_accept_time = Column(DateTime)
    last_reject_time = Column(DateTime)
    last_accept_nas = Column(String(128))
    last_reject_nas = Column(String(128))
    last_reject_message = Column(String(255))
    perc_time_used = Column(Integer)
    perc_data_used = Column(Integer)
    data_used = Column(BigInteger)
    data_cap = Column(BigInteger)
    time_used = Column(Integer)
    time_cap = Column(Integer)
    time_cap_type = Column(ENUM('hard', 'soft'), server_default=text("'soft'"))
    data_cap_type = Column(ENUM('hard', 'soft'), server_default=text("'soft'"))
    realm = Column(String(50), nullable=False, server_default=text("''"))
    realm_id = Column(Integer)
    profile = Column(String(50), nullable=False, server_default=text("''"))
    profile_id = Column(Integer)
    from_date = Column(DateTime)
    to_date = Column(DateTime)
    track_auth = Column(Integer, nullable=False, server_default=text("0"))
    track_acct = Column(Integer, nullable=False, server_default=text("1"))
    static_ip = Column(String(50), nullable=False, server_default=text("''"))
    extra_name = Column(String(100), nullable=False, server_default=text("''"))
    extra_value = Column(
        String(100), nullable=False, server_default=text("''"))
    country_id = Column(Integer)
    language_id = Column(Integer)
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class PermanentUserNote(Base):
    __tablename__ = 'permanent_user_notes'

    id = Column(Integer, primary_key=True)
    permanent_user_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class PermanentUserNotification(Base):
    __tablename__ = 'permanent_user_notifications'

    id = Column(Integer, primary_key=True)
    permanent_user_id = Column(Integer)
    active = Column(Integer, nullable=False, server_default=text("1"))
    method = Column(
        ENUM('whatsapp', 'email', 'sms'), server_default=text("'email'"))
    type = Column(ENUM('daily', 'usage'), server_default=text("'daily'"))
    address_1 = Column(String(255))
    address_2 = Column(String(255))
    start = Column(Integer, server_default=text("80"))
    increment = Column(Integer, server_default=text("10"))
    last_value = Column(Integer)
    last_notification = Column(DateTime)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class PermanentUserSetting(Base):
    __tablename__ = 'permanent_user_settings'

    id = Column(Integer, primary_key=True)
    permanent_user_id = Column(Integer, nullable=False)
    name = Column(String(255), nullable=False)
    value = Column(String(255), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
