from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class OpenvpnClient(Base):
    __tablename__ = 'openvpn_clients'

    id = Column(Integer, primary_key=True)
    username = Column(String(255), nullable=False)
    password = Column(String(255))
    subnet = Column(Integer)
    peer1 = Column(Integer)
    peer2 = Column(Integer)
    na_id = Column(Integer)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class OpenvpnServerClient(Base):
    __tablename__ = 'openvpn_server_clients'

    id = Column(Integer, primary_key=True)
    mesh_ap_profile = Column(
        ENUM('mesh', 'ap_profile'), server_default=text("'mesh'"))
    openvpn_server_id = Column(Integer)
    mesh_id = Column(Integer)
    mesh_exit_id = Column(Integer)
    ap_profile_id = Column(Integer)
    ap_profile_exit_id = Column(Integer)
    ap_id = Column(Integer)
    ip_address = Column(String(40), nullable=False)
    last_contact_to_server = Column(DateTime)
    state = Column(Integer, nullable=False, server_default=text("0"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class OpenvpnServer(Base):
    __tablename__ = 'openvpn_servers'

    id = Column(Integer, primary_key=True)
    name = Column(String(64), nullable=False, server_default=text("''"))
    description = Column(
        String(255), nullable=False, server_default=text("''"))
    available_to_siblings = Column(
        Integer, nullable=False, server_default=text("1"))
    user_id = Column(Integer)
    local_remote = Column(
        ENUM('local', 'remote'), server_default=text("'local'"))
    protocol = Column(ENUM('udp', 'tcp'), server_default=text("'udp'"))
    ip_address = Column(String(40), nullable=False)
    port = Column(Integer, nullable=False)
    vpn_gateway_address = Column(String(40), nullable=False)
    vpn_bridge_start_address = Column(String(40), nullable=False)
    vpn_mask = Column(String(40), nullable=False)
    config_preset = Column(
        String(100), nullable=False, server_default=text("'default'"))
    ca_crt = Column(Text, nullable=False)
    extra_name = Column(String(100), nullable=False, server_default=text("''"))
    extra_value = Column(
        String(100), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
