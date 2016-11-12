# OXID Module Connector

OXID Module Connector (OMC) is a module itself that integrates with the _Extensions_ section of the administration panel of OXID eShop. It is created to display available modules, their most important data and install these modules automatically (if possible) and directly from the OXID eShop admin panel. If an automated installation is not possible for some reason, OMC links to the original module page.

>**ATTENTION!**
* Please do not install any module in your live shop environment!
* Please backup your installation (database + files) before installing a module via OXID Module Connector!
* Best case, please use an development environment when installing modules via OXID Module Connector!

After installing a module via OXID Module Connector (OMC), simply go to _Extensions_ -> _Modules_, find your just installed module here and activate it in order to get it running.

This module is based on [ioly](https://github.com/ioly/) and was developerd during the [#oxhackathon16](https://openspacer.org/12-oxid-community/136-oxid-hackathon-nuernberg-2016/) event. The OXIDforge editorial team takes care of the content (modules).

Please note that this is a community project that comes with absolutely no warranty nor claim for completeness or correctness of the content.

![OXID Module Connector](oxid_module_connector.png)

## Installation

Unfortunately, the module OXID Module Connector (OMC) still has to be installed the classic, manual way ;)
1. download [OXID Module Connector (OMC)](https://github.com/OXIDprojects/OXID-Module-Connector/archive/module.zip) to your local machine
2. unzip it with a tool of your choosing
3. upload the content of the folder modules/ into the modules/ folder of your OXID eShop installation
4. fire up your browser and go to the admin panel of your OXID eShop installation
5. go to _Extensions_ -> _Modules_, select "OXID Module Connector" from the list of the modules and activate it
6. you should now see a new entry in the left navigation bar at _Extensions_ -> _OXID Module Connector_

## Uninstall

You're not satisfied with this module? Bummer! Of course you can uninstall this module as easy as any other:
1. de-activate the module in the list of modules in _Admin panel -> Extensions -> Modules_
2. delete the folder OXID Module Connector from the modules/ directory on your server
3. go to _Admin panel -> Extensions -> Modules_ again. You shall now be asked if the database entries shall be deleted as well. Confirm this request!

## Users, stay up-to-date!

Staying up-to-date with OXID Module Connector is easy: simply activate the appropriate function in module settings to receive core, connector and recipe (modules in the OMC list) updates.

## Help & trouble shooting for users

As mentioned above, this is a community project, maintained by volunteers only. If you need help with OXID Module Connector, please turn to the [boards](http://forum.oxid-esales.com/), and avoid bugging the OMC or the OXIDforge editorial team.
If you need help with modules listed in OMC, please find it in either the boards or contact the vendor of this module.

## How to get your module into OXID Module Connector

Good news first: your module is and will be completely independent from OXID Module Connector (OMC). All OMC will need is a json file that defines where to find the most important information as well as how to map your module structure into the folder structure of an OXID eShop installation.

As OXID Module Connector has been forked from the [ioly project](https://github.com/ioly/ioly/), you may find any useful informaiton (how to write a recipe/json, all about cook books etc.) in [ioly's wiki](https://github.com/ioly/ioly/wiki). If you want to see your module listed in OXID Module Connector, please simply send us a pull request with your recipe to the recipe branch of this repository. Please note that there's no general right of acceptance.