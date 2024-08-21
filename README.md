# Autotest module

![Build Status](https://github.com/simplesamlphp/simplesamlphp-module-autotest/actions/workflows/php.yml/badge.svg)
[![Coverage Status](https://codecov.io/gh/simplesamlphp/simplesamlphp-module-autotest/branch/master/graph/badge.svg)](https://codecov.io/gh/simplesamlphp/simplesamlphp-module-autotest)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/simplesamlphp/simplesamlphp-module-autotest/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/simplesamlphp/simplesamlphp-module-autotest/?branch=master)
[![Type Coverage](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-autotest/coverage.svg)](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-autotest)
[![Psalm Level](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-autotest/level.svg)](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-autotest)

This module provides an interface to do automatic testing of
authentication sources.

## Installation

Once you have installed SimpleSAMLphp, installing this module is very simple.
Just execute the following command in the root of your SimpleSAMLphp
installation:

```bash
vendor/bin/composer require simplesamlphp/simplesamlphp-module-autotest:dev-master
```

where `dev-master` instructs Composer to install the `master` branch from the
Git repository. See the [releases](releases)  available if you want to use
a stable version of the module.

[releases]: https://github.com/simplesamlphp/simplesamlphp-module-autotest/releases

The module is disabled by default. If you want to enable the module once installed,
you just have to add it to the `module.enable` array in your `config.php`.

## Usage

This module provides three web pages:

- `SIMPLESAMLPHP_ROOT/module.php/autotest/login`
- `SIMPLESAMLPHP_ROOT/module.php/autotest/logout`
- `SIMPLESAMLPHP_ROOT/module.php/autotest/attributes`

All the web pages have a mandatory parameter 'SourceID', which is the name of
the authentication source.

On success, the web pages print a single line with "OK". The attributes page
will also list all the attributes of the user. On error they set the HTTP
status code to 500 Internal Server Error, print a line with "ERROR" and then
any information about the error.

**Note**: You still have to parse the login pages to extract the
          parameters in the login form.
