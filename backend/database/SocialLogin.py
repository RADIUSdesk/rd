from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class SocialLoginUserRealm(Base):
    __tablename__ = 'social_login_user_realms'

    id = Column(Integer, primary_key=True)
    social_login_user_id = Column(Integer)
    realm_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class SocialLoginUser(Base):
    __tablename__ = 'social_login_users'

    id = Column(Integer, primary_key=True)
    provider = Column(
        ENUM('Facebook', 'Google', 'Twitter'),
        server_default=text("'Facebook'"))
    uid = Column(String(100), nullable=False, server_default=text("''"))
    name = Column(String(100), nullable=False, server_default=text("''"))
    first_name = Column(String(100), nullable=False, server_default=text("''"))
    last_name = Column(String(100), nullable=False, server_default=text("''"))
    email = Column(String(100), nullable=False, server_default=text("''"))
    image = Column(String(100), nullable=False, server_default=text("''"))
    locale = Column(String(5), nullable=False, server_default=text("''"))
    timezone = Column(Integer, nullable=False, server_default=text("0"))
    date_of_birth = Column(Date)
    gender = Column(ENUM('male', 'female'), server_default=text("'male'"))
    last_connect_time = Column(DateTime)
    extra_name = Column(String(100), nullable=False, server_default=text("''"))
    extra_value = Column(
        String(100), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
