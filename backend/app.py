from apistar import Include, Route
from apistar.frameworks.wsgi import WSGIApp as App
from apistar.handlers import docs_urls, static_urls
from apistar import environment, typesystem
from apistar.backends import sqlalchemy_backend
import requests
import typing
import json

from database import Base
from app_types import Node, Ssid


class Env(environment.Environment):
    properties = {
        'DEBUG': typesystem.boolean(default=True),
        'DB_NAME': typesystem.string(default='rd'),
        'DB_HOST': typesystem.string(default='db'),
        'DB_PASSWORD': typesystem.string(default='fredFlint'),
        'DB_LOGIN': typesystem.string(default='rd')
    }


env = Env()

settings = {
    'TEMPLATES': {
        'ROOT_DIR': 'templates',
        'PACKAGE_DIRS': ['apistar']
    },
    "DATABASE": {
        "URL":
        'mysql+pymysql://' + env['DB_LOGIN'] + ':' + env['DB_PASSWORD'] + '@' +
        env['DB_HOST'] + '/' + env['DB_NAME'],
        "METADATA":
        Base.metadata
    }
}

Base.metadata.bind = settings['DATABASE']['URL']


def welcome(name=None):
    if name is None:
        return {'message': 'Welcome to API Star!'}
    return {'message': 'Welcome to API Starrrrrr, %s!' % name}


def get_ssids(session: sqlalchemy_backend.Session) -> typing.List[Ssid.Ssid]:
    queryset = session.query(Ssid.Ssid.db).all()
    return [Ssid.Ssid(record) for record in queryset]


def database(session: sqlalchemy_backend.Session, name=None):
    """
    Return a JSON response containing the application settings dictionary.
    """
    return name


def get_node_settings(node_id, session: sqlalchemy_backend.Session) -> typing.List[Node.NodeSetting]:
    """
    Return the nodes nodes settings
    """
    queryset = session.query(Node.NodeSetting.db).first()
    return Node.NodeSetting(queryset)


def get_all_node_settings(session: sqlalchemy_backend.Session) -> typing.List[Node.NodeSetting]:
    queryset = session.query(Node.NodeSetting.db).all()
    return [Node.NodeSetting(record) for record in queryset]


def get_node_config(mac, gateway=None):
    params = {'mac': mac}
    if gateway:
        params['gateway'] = gateway
    r = requests.get('http://web:80/cake2/rd_cake/nodes/get_config_for_node.json', params=params)
    out = r.json()
    print(out)

    if mac == "AC-86-74-89-9B-60":
        networks = out['config_settings']['network']
        for network in networks:
            if network['interface'] == 'lan':
                networks.remove(network)
                continue
            if 'comment' in network and '36' in network['comment']:
                ifname = network['options']['ifname']
                network['options']['ifname'] = 'eth0 ' + ifname
    if 'config_settings' in out:
        networks = out['config_settings']['network']
        for network in networks:
            if 'comment' in network:
                del network['comment']

    return out

routes = [
    Route('/api/', 'GET', welcome),
    Route('/api/database', 'GET', database),
    Route('/api/ssids', 'GET', get_ssids),
    Route('/api/node/settings/', 'GET', get_all_node_settings),
    Route('/api/node/settings/{node_id}/', 'GET', get_node_settings),
    Route('/api/node/config/', 'GET', get_node_config),
    Include('/api/docs', docs_urls),
    Include('/api/static', static_urls)
]

app = App(
    routes=routes,
    settings=settings,
    commands=sqlalchemy_backend.commands,  # Install custom commands.
    components=sqlalchemy_backend.components  # Install custom components.
)

if __name__ == '__main__':
    app.main()
