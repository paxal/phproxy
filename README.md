# PHProxy

PHProxy is an HTTP/HTTPS proxy built for development purpose, with custom name
resolution.

Currently, is supports HTTPS protocol, and might support HTTP protocol in some
conditions.

## Install

Clone this repository.

## Run

Example : run locally on port 8001.
```bash
./bin/phproxy run 127.0.0.1:8001
```

## Options

### Configuration file

* `--config file` : will load configuration from this file.
* `--save` : will load configuration file, apply changes, and save it again,
  and then run the proxy.
  
### Authentication
* `--auth` : adds basic authentication credentials. Add as many as you wish.

### Hosts names resolution

* `--translate from=to` : will connect to `to` instead of `from`. Could be
  either ips or domain names for both `from` and `to`.

### SSL Support

Some browsers/extensions support SSL Proxies. Thus, you can configure the proxy
to run on ssl :

* `--ssl` : will activate SSL support.
* `--ssl-cert` : use this file as PEM certificate file (with chain).
* `--ssl-key` : use this file as PEM private key.
* `--ssl-passphrase` : pass phrase for private key.

## Disclaimer

One MUST consider security when running a proxy : run the proxy behind a
firewall, or add security, ideally over SSL transport.

## TODO

* Add DI for extension support, remove usage of static functions.
