
# The Hosts-Project #
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/teamneusta/php-cli-hosts/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/teamneusta/php-cli-hosts/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/teamneusta/php-cli-hosts/badges/build.png?b=master)](https://scrutinizer-ci.com/g/teamneusta/php-cli-hosts/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/teamneusta/php-cli-hosts/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/teamneusta/php-cli-hosts/?branch=master)

### What's hosts-Project for? ###

The **hosts** Project is a little helper to manage ssh hosts, users and ports with hierarchy for configuration scope.
Only working with key-based authentication.

### Minimum Requirements ###


* Unix System
* PHP 7.0 >=
* ssh
* public key authorized on remote server

How to get started?
---------------------

Install hosts:

### As a Phar (Recommended)

```bash
$ curl -LSs https://teamneusta.github.io/php-cli-hosts/installer.php | php
```

The command will check your PHP settings, warn you of any issues, and the download it to the current directory. From there, you may place it anywhere that will make it easier for you to access (such as `/usr/local/bin`) and chmod it to `755`.


```bash
$ hosts --version
  Hosts 2.0.0
```

Whenever a new version of the application is released, you can simply run the `self-update` command to get the latest version:

```bash
$ hosts self-update
```

Basic Usage
---------------------

Adding a new host:

```bash
$ hosts host:add
  Please enter name: someHost
  Please enter host: www.some-host.de
  Please enter username: web-user
  Please enter Port:[22] 2001
  Please select a host:
    [0] local
    [1] project
   > 0
  Added Entry: web-user@www.some-host.de for local scope.
```

As you can see, there are only 2 scope for locally adding a new host. The Third Scope will be introduced later on.

Listing available hosts:

```bash
$ hosts host:list
  +------------------+---------------------------+----------+---------+
  | Name             | Host                      | User     | Scope   |
  +------------------+---------------------------+----------+---------+
  | someHost         | www.some-host.de          | web-user | local   |
  | someOtherHost    | www.some-other-host.de    | web-user | project |
  | someExternalHost | www.some-external-host.de | web-user | global  |
  +------------------+---------------------------+----------+---------+
```

Connecting to a host:

```bash
$ hosts connect
  Please select a host:
    [0] someHost
    [1] someOtherHost
    [2] exit
   > 0
  You have selected: someHost
  establishing connection...
  
   ! [CAUTION] Leaving local bash!
```
