from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class RealmNote(Base):
    __tablename__ = 'realm_notes'

    id = Column(Integer, primary_key=True)
    realm_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class Realm(Base):
    __tablename__ = 'realms'

    id = Column(Integer, primary_key=True)
    name = Column(String(64), nullable=False, server_default=text("''"))
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("1"))
    icon_file_name = Column(
        String(128), nullable=False, server_default=text("'logo.jpg'"))
    phone = Column(String(14), nullable=False, server_default=text("''"))
    fax = Column(String(14), nullable=False, server_default=text("''"))
    cell = Column(String(14), nullable=False, server_default=text("''"))
    email = Column(String(128), nullable=False, server_default=text("''"))
    url = Column(String(128), nullable=False, server_default=text("''"))
    street_no = Column(String(10), nullable=False, server_default=text("''"))
    street = Column(String(50), nullable=False, server_default=text("''"))
    town_suburb = Column(String(50), nullable=False, server_default=text("''"))
    city = Column(String(50), nullable=False, server_default=text("''"))
    country = Column(String(50), nullable=False, server_default=text("''"))
    lat = Column(Float(asdecimal=True))
    lon = Column(Float(asdecimal=True))
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    twitter = Column(String(255), nullable=False, server_default=text("''"))
    facebook = Column(String(255), nullable=False, server_default=text("''"))
    youtube = Column(String(255), nullable=False, server_default=text("''"))
    google_plus = Column(
        String(255), nullable=False, server_default=text("''"))
    linkedin = Column(String(255), nullable=False, server_default=text("''"))
    t_c_title = Column(String(255), nullable=False, server_default=text("''"))
    t_c_content = Column(Text, nullable=False)
    suffix = Column(String(200), nullable=False, server_default=text("''"))
    suffix_permanent_users = Column(
        Integer, nullable=False, server_default=text("0"))
    suffix_vouchers = Column(Integer, nullable=False, server_default=text("0"))
    suffix_devices = Column(Integer, nullable=False, server_default=text("0"))
