from apistar import typesystem
from database import Node as db_entry


class NodeSetting(typesystem.Object):
    db = db_entry.NodeSetting
    properties = {
        'id': typesystem.integer(),
        'mesh_id': typesystem.integer(),
        'password':typesystem.string(max_length=128),
        'power': typesystem.integer(default=100),
        'all_power': typesystem.integer(default=1),
        'two_chan': typesystem.integer(default=6),
        'five_chan': typesystem.integer(default=44),
        'heartbeat_interval': typesystem.integer(default=60),
        'heartbeat_dead_after': typesystem.integer(default=600),
        # TODO this needs to be proper dataTime
        'created': typesystem.string(max_length=100),
        'modified': typesystem.string(max_length=100),
        # TODO Maybe better Hash?
        'password_hash': typesystem.string(max_length=128),
        'eth_br_chk': typesystem.integer(default=0),
        'eth_br_with': typesystem.integer(default=0),
        'eth_br_for_all': typesystem.integer(default=1),
        'tz_name':  typesystem.string(max_length=128),
        'tz_value':  typesystem.string(max_length=128),
        # TODO Do the country thing properly
        'country':  typesystem.string(max_length=5),
        'gw_dhcp_timeout': typesystem.integer(default=120),
        'gw_use_previous': typesystem.integer(default=1),
        'gw_auto_reboot': typesystem.integer(default=1),
        'gw_auto_reboot_time': typesystem.integer(default=600),
        'client_key': typesystem.string(max_length=255)
    }
