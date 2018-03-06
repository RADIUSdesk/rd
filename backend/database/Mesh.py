from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class MeshEntry(Base):
    __tablename__ = 'mesh_entries'

    id = Column(Integer, primary_key=True)
    mesh_id = Column(Integer)
    name = Column(String(128), nullable=False)
    hidden = Column(Integer, nullable=False, server_default=text("0"))
    isolate = Column(Integer, nullable=False, server_default=text("0"))
    apply_to_all = Column(Integer, nullable=False, server_default=text("0"))
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
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    chk_maxassoc = Column(Integer, nullable=False, server_default=text("0"))
    maxassoc = Column(Integer, server_default=text("100"))
    macfilter = Column(
        ENUM('disable', 'allow', 'deny'), server_default=text("'disable'"))
    permanent_user_id = Column(Integer, nullable=False)


class MeshExitCaptivePortal(Base):
    __tablename__ = 'mesh_exit_captive_portals'

    id = Column(Integer, primary_key=True)
    mesh_exit_id = Column(Integer, nullable=False)
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


class MeshExitMeshEntry(Base):
    __tablename__ = 'mesh_exit_mesh_entries'

    id = Column(Integer, primary_key=True)
    mesh_exit_id = Column(Integer, nullable=False)
    mesh_entry_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class MeshExit(Base):
    __tablename__ = 'mesh_exits'

    id = Column(Integer, primary_key=True)
    mesh_id = Column(Integer)
    name = Column(String(128), nullable=False)
    type = Column(
        ENUM('bridge', 'tagged_bridge', 'nat', 'captive_portal',
             'openvpn_bridge'))
    auto_detect = Column(Integer, nullable=False, server_default=text("0"))
    vlan = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    openvpn_server_id = Column(Integer)


class MeshNote(Base):
    __tablename__ = 'mesh_notes'

    id = Column(Integer, primary_key=True)
    mesh_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class MeshSetting(Base):
    __tablename__ = 'mesh_settings'

    id = Column(Integer, primary_key=True)
    mesh_id = Column(Integer)
    aggregated_ogms = Column(Integer, nullable=False, server_default=text("1"))
    ap_isolation = Column(Integer, nullable=False, server_default=text("0"))
    bonding = Column(Integer, nullable=False, server_default=text("0"))
    bridge_loop_avoidance = Column(
        Integer, nullable=False, server_default=text("0"))
    fragmentation = Column(Integer, nullable=False, server_default=text("1"))
    distributed_arp_table = Column(
        Integer, nullable=False, server_default=text("1"))
    orig_interval = Column(
        Integer, nullable=False, server_default=text("1000"))
    gw_sel_class = Column(Integer, nullable=False, server_default=text("20"))
    connectivity = Column(
        ENUM('IBSS', 'mesh_point'), server_default=text("'mesh_point'"))
    encryption = Column(Integer, nullable=False, server_default=text("0"))
    encryption_key = Column(
        String(63), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class MeshSpecific(Base):
    __tablename__ = 'mesh_specifics'

    id = Column(Integer, primary_key=True)
    mesh_id = Column(Integer, nullable=False)
    name = Column(String(255), nullable=False)
    value = Column(String(255), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class Mesh(Base):
    __tablename__ = 'meshes'

    id = Column(Integer, primary_key=True)
    name = Column(String(128), nullable=False)
    ssid = Column(String(32), nullable=False)
    bssid = Column(String(32), nullable=False)
    user_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("0"))
