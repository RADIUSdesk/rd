from apistar import typesystem
from database import Ssid as db_entry

class Ssid(typesystem.Object):
    db = db_entry.Ssid
    properties = {
        'name': typesystem.string(max_length=100),
        'available_to_siblings': typesystem.integer(default=1),
        'extra_name': typesystem.string(max_length=100),
        'extra_value': typesystem.string(max_length=100)
    }
