# ioly

> "Everybody loves ioly!"

<img align="right" width="150" src="https://raw.github.com/ioly/ioly/gh-pages/assets/img/ioly-logo-github.png"> 

ioly is a decentralized/centralized universal package installer for web based php software. You can use it with any system that supports modules/extensions/packages. e.g. OXID eSales, Wordpress, Redaxo etc. etc. etc.. 

The ioly system is driven by a "cookbook", stored on GitHub, which contains "recipes" to install any zip-based package. If something is missing, you can send us a pull request to extend/improve the main ioly cookbook. As soon as we merge your modifications, EVERY ioly installation has access to your package!

## Getting started

1. download [ioly core](https://github.com/ioly/ioly/tree/core)
2. download ioly connector (e. g. [OXID connector](https://github.com/ioly/ioly/tree/connector-oxid)) OR install it via ioly core, see below :) E.g. "php ioly.php install ioly/ioly-oxid-connector latest"
3. search for a recipe
4. install recipe
4. done


## Installation

### Manual installation

Download `ioly` core via console or via HTTP or:

`curl -O https://raw.githubusercontent.com/ioly/ioly/core/ioly.php`

Using `ioly` module manager via console. In this example, we'll add a module to an installation of OXID eSales:

``` sh
$ cd /var/www/myshop.de/
$ export IOLY_SYSTEM_BASE=/var/www/myshop.de/
$ export IOLY_SYSTEM_VERSION=4.9
$ php ioly.php update
$ php ioly.php install ioly/ioly-oxid-connector latest
$ php ioly.php list
$ php ioly.php search paypal
$ php ioly.php show oxid/paypal
$ php ioly.php install oxid/paypal 3.2.1
```

`NOTE:`
If you are in the base directory of an OXID eShop, you don't have to set `IOLY_SYSTEM_BASE` and `IOLY_SYSTEM_VERSION`, since
`ioly core` uses the current directory and the OXID package info "pkg.info" file to automatically set those variables.

### Install ioly Core via Composer

You can also use the ioly Core via Composer:

``` json
{
  "repositories": {
    "ioly/ioly": {
      "type": "vcs",
      "url": "https://github.com/ioly/ioly.git"
    }
  },
  "require": {
    "ioly/ioly": "dev-core"
  }
}
````

and also use it from PHP e.g.:

```php
require_once 'vendor/autoload.php';

$ioly = new \ioly\ioly();
$ioly->setSystemBasePath(dirname(__FILE__));
$ioly->setSystemVersion('5.2');

// add custom cookbook :)
$ioly->addCookbook('myowncookbook', "http://my.domain.de/git-archives/myCookbook/develop.zip");

/**
 * define ioly packages here
 */
$aPackages = array(
    'ioly/ioly-oxid-connector' => 'latest',
);

foreach ($aPackages as $package => $version) {
    if (!$ioly->isInstalledInVersion($package, $version)) {
        try {
            $ioly->install($package, $version);
            echo "\nPackage: $package installed in version: $version";
        } catch (Exception $ex) {
            echo "\nError installing package '$package': " . $ex->getMessage();
        }
    } else {
        echo "\nPackage $package already installed in version: $version";
    }
}
````

### Contributing

1. write a recipe
2. commit your recipe
3. send a pull request
4. done, everybody can use it!

[Read more here!](https://github.com/ioly/ioly/wiki/Contributing-to-the-ioly-cookbook)

##### Example
``` json
{
    "name": "PayPal",
    "vendor": "oxid",
    "type": "oxid",
    "license": "GNU",
    "desc": {
        "en": "PayPal payment method for checkout.",
        "de": "PayPal als Zahlart."
    },
    "tags": [
        "frontend",
        "paypal",
        "payment",
        "checkout"
    ],
    "versions": {
        "3.2.1": {
            "project": "https://github.com/OXID-eSales/paypal/tree/v3.2.1",
            "url": "https://github.com/OXID-eSales/paypal/archive/v3.2.1.zip",
            "supported": [
                "4.9",
                "4.8",
                "4.7"
            ],
            "mapping": [
                {
                    "src": "source/modules/oe/",
                    "dest": "modules/oe/"
                }
            ],
            "touch": [
                "modules/oe/vendormetadata.php"
            ]
        }
    }
}
```

[Read more about writing a recipe.](https://github.com/ioly/ioly/wiki/Writing-a-recipe)


### Requirements

- PHP 5.3.0 or above (at least 5.3.4 recommended to avoid potential bugs)
- PHP extensions:
  * cUrl
  * JSON
  * ZIP


### Authors
---
Dave Holloway - <http://www.gn2-netwerk.de> - <http://twitter.com/dajoho><br />
Tobias Merkl - <http://www.proudsourcing.de> - <http://twitter.com/tabsl><br />
Stefan Moises - <http://www.rent-a-hero.de> - <http://twitter.com/upsettweety><br />

You can also view the list of [contributors](https://github.com/ioly/ioly/contributors) who participated in this project.


License
---
ioly is licensed under the MIT License - see the LICENSE file for details.
