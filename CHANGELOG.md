# Changelog

## 1.4.0 (2021-07-14)

- Drop support for Symfony 3.4, 5.0, and 5.1
- Add support for Symfony 6
- Add support for `psr/log` 2.0 and 3.0

## 1.3.0 (2020-11-02)

- Allow install with PHP 8

## 1.2.1 (2020-08-26)

- [#4](https://github.com/GeniusesOfSymfony/WebSocketPhpClient/pull/4) Fix reconnect on connection reset

## 1.2.0 (2020-08-13)

- Deprecated the package in favor of [Pawl](https://github.com/ratchetphp/Pawl)

## 1.1.1 (2020-06-29)

- Added log messages for most thrown Exceptions

## 1.1.0 (2020-06-01)

- Added a client factory (copied from `Gos\Bundle\WebSocketBundle\Pusher\Wamp\WampConnectionFactoryInterface` and `Gos\Bundle\WebSocketBundle\Pusher\Wamp\WampConnectionFactory`) in the `gos/web-socket-bundle` package
