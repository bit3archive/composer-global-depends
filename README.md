[![Version](https://img.shields.io/packagist/v/bit3/composer-global-depends.svg?style=flat-square)](https://packagist.org/packages/bit3/composer-global-depends)
[![Stable Build Status](https://img.shields.io/travis/bit3/composer-global-depends/master.svg?style=flat-square)](https://travis-ci.org/bit3/composer-global-depends)
[![Code Quality](https://img.shields.io/scrutinizer/g/bit3/composer-global-depends.svg?style=flat-square)](https://scrutinizer-ci.com/g/bit3/composer-global-depends/)
[![Dependencies](https://img.shields.io/versioneye/d//bit3/composer-global-depends.svg?style=flat-square)](https://www.versioneye.com/user/projects/54cccc89de7924f81a00033f)
[![License](https://img.shields.io/packagist/l/bit3/composer-global-depends.svg?style=flat-square)](https://github.com/bit3/composer-global-depends/blob/master/LICENSE)
[![Downloads](https://img.shields.io/packagist/dt/bit3/composer-global-depends.svg?style=flat-square)](https://packagist.org/packages/bit3/composer-global-depends)

Global dependency analyser
==========================

Search for packages that depend on another package globally!

Installation
------------

```
$ php composer.phar create-project bit3/composer-global-depends dev-master
```

Usage
-----

```
$ cd composer-global-depends
$ ./bin/composer-global-depends.php phpmd/phpmd
[acquia/acquia-search-proxy 0.2.x-dev] require-dev phpmd/phpmd ~1.0                   
[acquia/acquia-search-proxy dev-master] require-dev phpmd/phpmd ~1.0                  
[acquia/acquia-search-proxy 0.2.x-dev] require-dev phpmd/phpmd ~1.0                   
[acquia/acquia-search-proxy dev-master] require-dev phpmd/phpmd ~1.0                  
  1182 [------>---------------------] 5 secs   albertofem/translatableroutepath-bundle
...
```
