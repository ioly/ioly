ioly helps you easily integrate modules into your PHP application.


Getting started
---
1. download ioly
2. search for a recipe
3. install recipe
4. done


Installation
---
Download `ioly` core using console:

  ``` sh
  $ curl -sS https://github.com/ioly/ioly/archive/core.zip | unzip
  ```

Download `ioly` core using FTP:

[https://github.com/ioly/ioly/archive/core.zip](https://github.com/ioly/ioly/archive/core.zip)


Contributing
---
1. write a recipe
2. commit your recipe
3. done, everybody can use it!

##### Example
``` json
  {
      "name": "PayPal",
      "vendor": "oxid",
      "type": "oxid",
      "license": "GNU",
      "desc": "PayPal payment method for checkout.",
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
              ]
          }
      }
  }
```

[Read more about writing a recipe.](https://github.com/ioly/ioly/wiki/Writing-a-recipe)


Requirements
---
- PHP 5.3.0 or above (at least 5.3.4 recommended to avoid potential bugs)
- PHP extensions:
  * cUrl
  * JSON
  * Phar
  * ZIP


Authors
---
Dave Holloway - <http://www.gn2-netwerk.de> - <http://twitter.com/dajoho><br />
Tobias Merkl - <http://www.proudsourcing.de> - <http://twitter.com/tabsl><br />
Stefan Moises - <http://www.rent-a-hero.de> - <http://twitter.com/smxsm><br />

See also the list of [contributors](https://github.com/ioly/ioly/contributors) who participated in this project.


License
---
ioly is licensed under the MIT License - see the LICENSE file for details.
