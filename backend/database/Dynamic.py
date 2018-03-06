from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class DynamicClientNote(Base):
    __tablename__ = 'dynamic_client_notes'

    id = Column(Integer, primary_key=True)
    dynamic_client_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class UnknownDynamicClient(Base):
    __tablename__ = 'unknown_dynamic_clients'

    id = Column(Integer, primary_key=True)
    nasidentifier = Column(
        String(128), nullable=False, unique=True, server_default=text("''"))
    calledstationid = Column(
        String(128), nullable=False, unique=True, server_default=text("''"))
    last_contact = Column(DateTime)
    last_contact_ip = Column(
        String(128), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class DynamicClientRealm(Base):
    __tablename__ = 'dynamic_client_realms'

    id = Column(Integer, primary_key=True)
    dynamic_client_id = Column(Integer, nullable=False)
    realm_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class DynamicClientState(Base):
    __tablename__ = 'dynamic_client_states'

    id = Column(Integer, primary_key=True)
    dynamic_client_id = Column(String(36), nullable=False)
    state = Column(Integer, nullable=False, server_default=text("0"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class DynamicClient(Base):
    __tablename__ = 'dynamic_clients'

    id = Column(Integer, primary_key=True)
    name = Column(String(64), nullable=False, server_default=text("''"))
    nasidentifier = Column(
        String(128), nullable=False, server_default=text("''"))
    calledstationid = Column(
        String(128), nullable=False, server_default=text("''"))
    last_contact = Column(DateTime)
    last_contact_ip = Column(
        String(128), nullable=False, server_default=text("''"))
    timezone = Column(String(255), nullable=False, server_default=text("''"))
    monitor = Column(
        ENUM('off', 'heartbeat', 'socket'), server_default=text("'off'"))
    session_auto_close = Column(
        Integer, nullable=False, server_default=text("0"))
    session_dead_time = Column(
        Integer, nullable=False, server_default=text("3600"))
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("1"))
    active = Column(Integer, nullable=False, server_default=text("1"))
    on_public_maps = Column(Integer, nullable=False, server_default=text("0"))
    lat = Column(Float(asdecimal=True))
    lon = Column(Float(asdecimal=True))
    photo_file_name = Column(
        String(128), nullable=False, server_default=text("'logo.jpg'"))
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class DynamicDetailNote(Base):
    __tablename__ = 'dynamic_detail_notes'

    id = Column(Integer, primary_key=True)
    dynamic_detail_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class DynamicDetailSocialLogin(Base):
    __tablename__ = 'dynamic_detail_social_logins'

    id = Column(Integer, primary_key=True)
    dynamic_detail_id = Column(Integer, nullable=False)
    profile_id = Column(Integer, nullable=False)
    realm_id = Column(Integer, nullable=False)
    name = Column(String(50), nullable=False)
    enable = Column(Integer, nullable=False, server_default=text("0"))
    record_info = Column(Integer, nullable=False, server_default=text("0"))
    special_key = Column(
        String(100), nullable=False, server_default=text("''"))
    secret = Column(String(100), nullable=False, server_default=text("''"))
    type = Column(ENUM('voucher', 'user'), server_default=text("'voucher'"))
    extra_name = Column(String(100), nullable=False, server_default=text("''"))
    extra_value = Column(
        String(100), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class DynamicDetail(Base):
    __tablename__ = 'dynamic_details'

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
    t_c_check = Column(Integer, nullable=False, server_default=text("0"))
    t_c_url = Column(String(50), nullable=False, server_default=text("''"))
    redirect_check = Column(Integer, nullable=False, server_default=text("0"))
    redirect_url = Column(
        String(200), nullable=False, server_default=text("''"))
    slideshow_check = Column(Integer, nullable=False, server_default=text("0"))
    seconds_per_slide = Column(
        Integer, nullable=False, server_default=text("30"))
    connect_check = Column(Integer, nullable=False, server_default=text("0"))
    connect_username = Column(
        String(50), nullable=False, server_default=text("''"))
    connect_suffix = Column(
        String(50), nullable=False, server_default=text("'nasid'"))
    connect_delay = Column(Integer, nullable=False, server_default=text("0"))
    connect_only = Column(Integer, nullable=False, server_default=text("0"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    user_login_check = Column(
        Integer, nullable=False, server_default=text("1"))
    voucher_login_check = Column(
        Integer, nullable=False, server_default=text("0"))
    auto_suffix_check = Column(
        Integer, nullable=False, server_default=text("0"))
    auto_suffix = Column(
        String(200), nullable=False, server_default=text("''"))
    usage_show_check = Column(
        Integer, nullable=False, server_default=text("1"))
    usage_refresh_interval = Column(
        Integer, nullable=False, server_default=text("120"))
    theme = Column(
        String(200), nullable=False, server_default=text("'Default'"))
    register_users = Column(Integer, nullable=False, server_default=text("0"))
    lost_password = Column(Integer, nullable=False, server_default=text("0"))
    social_enable = Column(Integer, nullable=False, server_default=text("0"))
    social_temp_permanent_user_id = Column(Integer)
    coova_desktop_url = Column(
        String(255), nullable=False, server_default=text("''"))
    coova_mobile_url = Column(
        String(255), nullable=False, server_default=text("''"))
    mikrotik_desktop_url = Column(
        String(255), nullable=False, server_default=text("''"))
    mikrotik_mobile_url = Column(
        String(255), nullable=False, server_default=text("''"))
    default_language = Column(
        String(255), nullable=False, server_default=text("''"))
    realm_id = Column(Integer)
    profile_id = Column(Integer)
    reg_auto_suffix_check = Column(
        Integer, nullable=False, server_default=text("0"))
    reg_auto_suffix = Column(
        String(200), nullable=False, server_default=text("''"))
    reg_mac_check = Column(Integer, nullable=False, server_default=text("0"))
    reg_auto_add = Column(Integer, nullable=False, server_default=text("0"))
    reg_email = Column(Integer, nullable=False, server_default=text("0"))
    slideshow_enforce_watching = Column(
        Integer, nullable=False, server_default=text("1"))
    slideshow_enforce_seconds = Column(
        Integer, nullable=False, server_default=text("10"))
    available_languages = Column(
        String(255), nullable=False, server_default=text("''"))


class DynamicPage(Base):
    __tablename__ = 'dynamic_pages'

    id = Column(Integer, primary_key=True)
    dynamic_detail_id = Column(Integer, nullable=False)
    name = Column(String(128), nullable=False, server_default=text("''"))
    content = Column(Text, nullable=False)
    created = Column(DateTime)
    modified = Column(DateTime)


class DynamicPair(Base):
    __tablename__ = 'dynamic_pairs'

    id = Column(Integer, primary_key=True)
    name = Column(String(64), nullable=False, server_default=text("''"))
    value = Column(String(64), nullable=False, server_default=text("''"))
    priority = Column(Integer, nullable=False, server_default=text("1"))
    dynamic_detail_id = Column(Integer)
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class DynamicPhoto(Base):
    __tablename__ = 'dynamic_photos'

    id = Column(Integer, primary_key=True)
    dynamic_detail_id = Column(Integer, nullable=False)
    title = Column(
        String(128), nullable=False, index=True, server_default=text("''"))
    description = Column(
        String(250), nullable=False, server_default=text("''"))
    url = Column(String(250), nullable=False, server_default=text("''"))
    file_name = Column(
        String(128), nullable=False, server_default=text("'logo.jpg'"))
    created = Column(DateTime)
    modified = Column(DateTime)
    active = Column(Integer, nullable=False, server_default=text("1"))
    fit = Column(
        ENUM('stretch_to_fit', 'horizontal', 'vertical', 'original',
             'dynamic'),
        server_default=text("'stretch_to_fit'"))
    background_color = Column(
        String(7), nullable=False, server_default=text("'ffffff'"))
    slide_duration = Column(Integer, nullable=False, server_default=text("10"))
    include_title = Column(Integer, nullable=False, server_default=text("1"))
    include_description = Column(
        Integer, nullable=False, server_default=text("1"))
