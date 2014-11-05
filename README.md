# ioly recipes
===
It's realy easy to write your own recipe.

1. [fork](https://github.com/ioly/ioly/fork) ioly [`master`](https://github.com/ioly/ioly/tree/master) branch
2. create vendor directory (if not exists)
3. create recipe file
4. send merge reqest


Example
---
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


Documentation
---
Key | Value | Type | Description
--- | --- | --- | ---
name | PayPal | string | Module name
vendor | oxid | string | Vendor name (= vendor directory name)
type | oxid | string | Application type
license | GNU | string | License type
desc | PayPal payment method for checkout. | string | Module description
tags | frontend, paypal, payment, checkout | array | Module tags
versions | 3.2.1 | arrray | Module versions (see table below)


##### Module versions
Key | Value | Type | Description
--- | --- | --- | ---
project | https://github.com/OXID-eSales/paypal/tree/v3.2.1 | string | Project / module url
url | https://github.com/OXID-eSales/paypal/archive/v3.2.1.zip | string | Source file url
supported | 4.9, 4.8, 4.7 | array | Support application versions
mapping | | array | Firectory / file mappgins  (see table below)


##### Directory / file mappings
Key | Value | Type | Description
--- | --- | --- | ---
src | source/modules/oe/ | string | Source directory / file (recursive)
dest | modules/oe/ | string | Destination directory / file


More examples
---
Using single mapping file ...
``` json
  {
      "name": "XML-Sitemap for OXID",
      "vendor": "proudcommerce",
      "type": "oxid",
      "license": "GNU",
      "desc": "Generated an XML sitemap for OXID eshop.",
      "tags": [
          "frontend",
          "seo",
          "sitemap"
      ],
      "versions": {
          "1.0.0": {
              "project": "https://github.com/proudcommerce/google_sitemap",
              "url": "https://github.com/proudcommerce/google_sitemap/archive/master.zip",
              "supported": [
                  	"4.9",
                  	"4.8",
                  	"4.7",
                  	"4.6"
              ],
              "mapping": [
              	{
                  	"src": "google_sitemap_xml.php",
                  	"dest": "google_sitemap_xml.php"
              	}
              ]
          }
      }
  }
```


Using more module versions ...
``` json
  {
      "name": "Mobile Theme",
      "vendor": "oxid",
      "type": "oxid",
      "license": "GNU",
      "desc": "Mobile theme for OXID eshop.",
      "tags": [
          "frontend",
          "mobile",
          "theme"
      ],
      "versions": {
          "1.3.0": {
              "project": "https://github.com/OXID-eSales/mobile_theme/tree/b-1.3",
              "url": "https://github.com/OXID-eSales/mobile_theme/archive/v1.3.0.zip",
              "supported": [
                  	"4.9",
                  	"4.8"
              ],
              "mapping": [
              	{
                  	"src": "source/modules/oe/",
                  	"dest": "modules/oe/"
              }
          },
          "1.1.0": {
              "project": "https://github.com/OXID-eSales/mobile_theme/tree/b-1.1",
              "url": "https://github.com/OXID-eSales/mobile_theme/archive/v1.1.0.zip",
              "supported": [
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


Using more module directory / file mappings ...
``` json
  {
      "name": "TOXID cURL",
      "vendor": "marmalade",
      "type": "oxid",
      "license": "unknown",
      "desc": "TOXID cURL enables you to load dynamic CMS content into your OXID eShop.",
      "tags": [
      	"frontend",
          "content",
          "wordpress",
          "typo3",
          "redaxo"
      ],
      "versions": {
          "2.0.0": {
              "project": "https://github.com/jkrug/TOXID-cURL",
              "url": "https://github.com/jkrug/TOXID-cURL/archive/master.zip",
              "supported": [
              		"4.9",
                  	"4.8",
                  	"4.7"
              ],
              "mapping": [
              	{
                  	"src": "",
                  	"dest": "modules/toxid_curl/"
              	},
              	{
                  	"src": "smarty/plugins/",
                  	"dest": "smarty/plugins/"
              	}
              ]
          }
      }
  }
```