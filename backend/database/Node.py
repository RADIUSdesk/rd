from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class NodeAction(Base):
    __tablename__ = 'node_actions'

    id = Column(Integer, primary_key=True)
    node_id = Column(Integer, nullable=False)
    action = Column(ENUM('execute'), server_default=text("'execute'"))
    command = Column(String(500), server_default=text("''"))
    status = Column(
        ENUM('awaiting', 'fetched', 'replied'),
        server_default=text("'awaiting'"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class UnknownNode(Base):
    __tablename__ = 'unknown_nodes'

    id = Column(Integer, primary_key=True)
    mac = Column(String(255), nullable=False)
    vendor = Column(String(255))
    from_ip = Column(String(15), nullable=False, server_default=text("''"))
    gateway = Column(Integer, nullable=False, server_default=text("1"))
    last_contact = Column(DateTime)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    new_server = Column(String(255), nullable=False, server_default=text("''"))
    new_server_status = Column(
        ENUM('awaiting', 'fetched', 'replied'),
        server_default=text("'awaiting'"))


class NodeIbssConnection(Base):
    __tablename__ = 'node_ibss_connections'

    id = Column(Integer, primary_key=True)
    node_id = Column(Integer)
    station_node_id = Column(Integer)
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


class NodeLoad(Base):
    __tablename__ = 'node_loads'

    id = Column(Integer, primary_key=True)
    node_id = Column(Integer)
    mem_total = Column(Integer)
    mem_free = Column(Integer)
    uptime = Column(String(255))
    system_time = Column(String(255), nullable=False)
    load_1 = Column(Float(2), nullable=False)
    load_2 = Column(Float(2), nullable=False)
    load_3 = Column(Float(2), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class NodeMeshEntry(Base):
    __tablename__ = 'node_mesh_entries'

    id = Column(Integer, primary_key=True)
    node_id = Column(Integer, nullable=False)
    mesh_entry_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class NodeMeshExit(Base):
    __tablename__ = 'node_mesh_exits'

    id = Column(Integer, primary_key=True)
    node_id = Column(Integer, nullable=False)
    mesh_exit_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class NodeMpSetting(Base):
    __tablename__ = 'node_mp_settings'

    id = Column(Integer, primary_key=True)
    node_id = Column(Integer)
    name = Column(String(50))
    value = Column(String(255), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class NodeNeighbor(Base):
    __tablename__ = 'node_neighbors'

    id = Column(Integer, primary_key=True)
    node_id = Column(Integer)
    gateway = Column(ENUM('yes', 'no'), server_default=text("'no'"))
    neighbor_id = Column(Integer)
    metric = Column(Numeric(6, 4), nullable=False)
    hwmode = Column(String(5), server_default=text("'11g'"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class NodeSetting(Base):
    __tablename__ = 'node_settings'

    id = Column(Integer, primary_key=True)
    mesh_id = Column(Integer)
    password = Column(String(128), nullable=False)
    power = Column(Integer, nullable=False, server_default=text("100"))
    all_power = Column(Integer, nullable=False, server_default=text("1"))
    two_chan = Column(Integer, nullable=False, server_default=text("6"))
    five_chan = Column(Integer, nullable=False, server_default=text("44"))
    heartbeat_interval = Column(
        Integer, nullable=False, server_default=text("60"))
    heartbeat_dead_after = Column(
        Integer, nullable=False, server_default=text("600"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    password_hash = Column(
        String(100), nullable=False, server_default=text("''"))
    eth_br_chk = Column(Integer, nullable=False, server_default=text("0"))
    eth_br_with = Column(Integer, nullable=False, server_default=text("0"))
    eth_br_for_all = Column(Integer, nullable=False, server_default=text("1"))
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
    client_key = Column(
        String(255), nullable=False, server_default=text("'radiusdesk'"))


class NodeStation(Base):
    __tablename__ = 'node_stations'

    id = Column(Integer, primary_key=True)
    node_id = Column(Integer)
    mesh_entry_id = Column(Integer)
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


class NodeSystem(Base):
    __tablename__ = 'node_systems'

    id = Column(Integer, primary_key=True)
    node_id = Column(Integer)
    name = Column(String(255), nullable=False)
    value = Column(String(255), nullable=False)
    group = Column(String(255), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class NodeWifiSetting(Base):
    __tablename__ = 'node_wifi_settings'

    id = Column(Integer, primary_key=True)
    node_id = Column(Integer)
    name = Column(String(50))
    value = Column(String(255))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class Node(Base):
    __tablename__ = 'nodes'

    id = Column(Integer, primary_key=True)
    mesh_id = Column(Integer)
    name = Column(String(255), nullable=False)
    description = Column(String(255), nullable=False)
    mac = Column(String(255), nullable=False)
    hardware = Column(String(255))
    power = Column(Integer, nullable=False, server_default=text("100"))
    ip = Column(String(255))
    last_contact = Column(DateTime)
    on_public_maps = Column(Integer, nullable=False, server_default=text("0"))
    lat = Column(Float(asdecimal=True))
    lon = Column(Float(asdecimal=True))
    photo_file_name = Column(
        String(128), nullable=False, server_default=text("'logo.jpg'"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    radio0_enable = Column(Integer, nullable=False, server_default=text("1"))
    radio0_mesh = Column(Integer, nullable=False, server_default=text("1"))
    radio0_entry = Column(Integer, nullable=False, server_default=text("1"))
    radio0_band = Column(Integer, nullable=False, server_default=text("24"))
    radio0_two_chan = Column(Integer, nullable=False, server_default=text("1"))
    radio0_five_chan = Column(
        Integer, nullable=False, server_default=text("44"))
    radio1_enable = Column(Integer, nullable=False, server_default=text("1"))
    radio1_mesh = Column(Integer, nullable=False, server_default=text("1"))
    radio1_entry = Column(Integer, nullable=False, server_default=text("1"))
    radio1_band = Column(Integer, nullable=False, server_default=text("5"))
    radio1_two_chan = Column(Integer, nullable=False, server_default=text("1"))
    radio1_five_chan = Column(
        Integer, nullable=False, server_default=text("44"))
