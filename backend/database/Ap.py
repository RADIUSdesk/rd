from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class Ap(Base):
    __tablename__ = 'aps'

    id = Column(Integer, primary_key=True)
    ap_profile_id = Column(Integer)
    name = Column(String(255), nullable=False)
    description = Column(String(255), nullable=False)
    mac = Column(String(255), nullable=False)
    hardware = Column(String(255))
    last_contact_from_ip = Column(String(255))
    last_contact = Column(DateTime)
    on_public_maps = Column(Integer, nullable=False, server_default=text("0"))
    lat = Column(Float(asdecimal=True))
    lon = Column(Float(asdecimal=True))
    photo_file_name = Column(
        String(128), nullable=False, server_default=text("'logo.jpg'"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class UnknownAp(Base):
    __tablename__ = 'unknown_aps'

    id = Column(Integer, primary_key=True)
    mac = Column(String(255), nullable=False)
    vendor = Column(String(255))
    last_contact_from_ip = Column(String(255))
    last_contact = Column(DateTime)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    new_server = Column(String(255), nullable=False, server_default=text("''"))
    new_server_status = Column(
        ENUM('awaiting', 'fetched', 'replied'),
        server_default=text("'awaiting'"))


class ApAction(Base):
    __tablename__ = 'ap_actions'

    id = Column(Integer, primary_key=True)
    ap_id = Column(Integer, nullable=False)
    action = Column(ENUM('execute'), server_default=text("'execute'"))
    command = Column(String(500), server_default=text("''"))
    status = Column(
        ENUM('awaiting', 'fetched', 'replied'),
        server_default=text("'awaiting'"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class ApLoad(Base):
    __tablename__ = 'ap_loads'

    id = Column(Integer, primary_key=True)
    ap_id = Column(Integer)
    mem_total = Column(Integer)
    mem_free = Column(Integer)
    uptime = Column(String(255))
    system_time = Column(String(255), nullable=False)
    load_1 = Column(Float(2), nullable=False)
    load_2 = Column(Float(2), nullable=False)
    load_3 = Column(Float(2), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class ApProfileEntry(Base):
    __tablename__ = 'ap_profile_entries'

    id = Column(Integer, primary_key=True)
    ap_profile_id = Column(Integer)
    name = Column(String(128), nullable=False)
    hidden = Column(Integer, nullable=False, server_default=text("0"))
    isolate = Column(Integer, nullable=False, server_default=text("0"))
    encryption = Column(
        ENUM('none', 'wep', 'psk', 'psk2', 'wpa', 'wpa2'),
        server_default=text("'none'"))
    special_key = Column(
        String(100), nullable=False, server_default=text("''"))
    auth_server = Column(
        String(255), nullable=False, server_default=text("''"))
    auth_secret = Column(
        String(255), nullable=False, server_default=text("''"))
    dynamic_vlan = Column(Integer, nullable=False, server_default=text("0"))
    frequency_band = Column(
        ENUM('both', 'two', 'five'), server_default=text("'both'"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    chk_maxassoc = Column(Integer, nullable=False, server_default=text("0"))
    maxassoc = Column(Integer, server_default=text("100"))
    macfilter = Column(
        ENUM('disable', 'allow', 'deny'), server_default=text("'disable'"))
    permanent_user_id = Column(Integer, nullable=False)


class ApProfileExitApProfileEntry(Base):
    __tablename__ = 'ap_profile_exit_ap_profile_entries'

    id = Column(Integer, primary_key=True)
    ap_profile_exit_id = Column(Integer, nullable=False)
    ap_profile_entry_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class ApProfileExitCaptivePortal(Base):
    __tablename__ = 'ap_profile_exit_captive_portals'

    id = Column(Integer, primary_key=True)
    ap_profile_exit_id = Column(Integer, nullable=False)
    radius_1 = Column(String(128), nullable=False)
    radius_2 = Column(String(128), nullable=False, server_default=text("''"))
    radius_secret = Column(String(128), nullable=False)
    radius_nasid = Column(String(128), nullable=False)
    uam_url = Column(String(255), nullable=False)
    uam_secret = Column(String(255), nullable=False)
    walled_garden = Column(String(255), nullable=False)
    swap_octets = Column(Integer, nullable=False, server_default=text("0"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    mac_auth = Column(Integer, nullable=False, server_default=text("0"))
    proxy_enable = Column(Integer, nullable=False, server_default=text("0"))
    proxy_ip = Column(String(128), nullable=False, server_default=text("''"))
    proxy_port = Column(Integer, nullable=False, server_default=text("3128"))
    proxy_auth_username = Column(
        String(128), nullable=False, server_default=text("''"))
    proxy_auth_password = Column(
        String(128), nullable=False, server_default=text("''"))
    coova_optional = Column(
        String(255), nullable=False, server_default=text("''"))
    dns_manual = Column(Integer, nullable=False, server_default=text("0"))
    dns1 = Column(String(128), nullable=False, server_default=text("''"))
    dns2 = Column(String(128), nullable=False, server_default=text("''"))
    uamanydns = Column(Integer, nullable=False, server_default=text("0"))
    dnsparanoia = Column(Integer, nullable=False, server_default=text("0"))
    dnsdesk = Column(Integer, nullable=False, server_default=text("0"))


class ApProfileExit(Base):
    __tablename__ = 'ap_profile_exits'

    id = Column(Integer, primary_key=True)
    ap_profile_id = Column(Integer)
    type = Column(
        ENUM('bridge', 'tagged_bridge', 'nat', 'captive_portal',
             'openvpn_bridge'))
    vlan = Column(Integer)
    auto_dynamic_client = Column(
        Integer, nullable=False, server_default=text("0"))
    realm_list = Column(String(128), nullable=False, server_default=text("''"))
    auto_login_page = Column(Integer, nullable=False, server_default=text("0"))
    dynamic_detail_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    openvpn_server_id = Column(Integer)


class ApProfileNote(Base):
    __tablename__ = 'ap_profile_notes'

    id = Column(Integer, primary_key=True)
    ap_profile_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class ApProfileSetting(Base):
    __tablename__ = 'ap_profile_settings'

    id = Column(Integer, primary_key=True)
    ap_profile_id = Column(Integer)
    password = Column(String(128), nullable=False)
    heartbeat_interval = Column(
        Integer, nullable=False, server_default=text("60"))
    heartbeat_dead_after = Column(
        Integer, nullable=False, server_default=text("600"))
    password_hash = Column(
        String(100), nullable=False, server_default=text("''"))
    tz_name = Column(
        String(128), nullable=False, server_default=text("'America/New York'"))
    tz_value = Column(
        String(128),
        nullable=False,
        server_default=text("'EST5EDT,M3.2.0,M11.1.0'"))
    country = Column(String(5), nullable=False, server_default=text("'US'"))
    gw_dhcp_timeout = Column(
        Integer, nullable=False, server_default=text("120"))
    gw_use_previous = Column(Integer, nullable=False, server_default=text("1"))
    gw_auto_reboot = Column(Integer, nullable=False, server_default=text("1"))
    gw_auto_reboot_time = Column(
        Integer, nullable=False, server_default=text("600"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class ApProfileSpecific(Base):
    __tablename__ = 'ap_profile_specifics'

    id = Column(Integer, primary_key=True)
    ap_profile_id = Column(Integer, nullable=False)
    name = Column(String(255), nullable=False)
    value = Column(String(255), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class ApProfile(Base):
    __tablename__ = 'ap_profiles'

    id = Column(Integer, primary_key=True)
    name = Column(String(128), nullable=False)
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("0"))


class ApStation(Base):
    __tablename__ = 'ap_stations'

    id = Column(Integer, primary_key=True)
    ap_id = Column(Integer)
    ap_profile_entry_id = Column(Integer)
    vendor = Column(String(255))
    mac = Column(String(17), nullable=False)
    tx_bytes = Column(BigInteger, nullable=False)
    rx_bytes = Column(BigInteger, nullable=False)
    tx_packets = Column(Integer, nullable=False)
    rx_packets = Column(Integer, nullable=False)
    tx_bitrate = Column(Integer, nullable=False)
    rx_bitrate = Column(Integer, nullable=False)
    tx_extra_info = Column(String(255), nullable=False)
    rx_extra_info = Column(String(255), nullable=False)
    authenticated = Column(ENUM('yes', 'no'), server_default=text("'no'"))
    authorized = Column(ENUM('yes', 'no'), server_default=text("'no'"))
    tdls_peer = Column(String(255), nullable=False)
    preamble = Column(ENUM('long', 'short'), server_default=text("'long'"))
    tx_failed = Column(Integer, nullable=False)
    inactive_time = Column(Integer, nullable=False)
    WMM_WME = Column(ENUM('yes', 'no'), server_default=text("'no'"))
    tx_retries = Column(Integer, nullable=False)
    MFP = Column(ENUM('yes', 'no'), server_default=text("'no'"))
    signal = Column(Integer, nullable=False)
    signal_avg = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class ApSystem(Base):
    __tablename__ = 'ap_systems'

    id = Column(Integer, primary_key=True)
    ap_id = Column(Integer)
    name = Column(String(255), nullable=False)
    value = Column(String(255), nullable=False)
    group = Column(String(255), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class ApWifiSetting(Base):
    __tablename__ = 'ap_wifi_settings'

    id = Column(Integer, primary_key=True)
    ap_id = Column(Integer)
    name = Column(String(50))
    value = Column(String(255))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
