# OXID module connector (en)

OXID module connector (OMC) displays available modules, a short description and allows to install these modules automatically (if possible) and directly from the OXID eShop admin panel. If an automated installation is not possible for some reason, OMC links to the original module page. ([deutsche Version](https://github.com/OXIDprojects/OXID-Module-Connector/blob/recipes/LIESMICH.md))


## Installation

1. [download OXID Modul Connector](https://github.com/OXIDprojects/OXID-Module-Connector/archive/recipes.zip)
2. Extract module (oxcom-omc) to the /modules directory
3. Activate module
4. That´s it!

Installation with Composer or using console? No problem! ;-) [Installation instructions](https://github.com/OXIDprojects/OXID-Module-Connector/wiki/Installation)


## Usage

- In der Shop-Admin-Navigation im Bereich Erweiterungen gibt es einen neuen Punkt Connector.
- Beim ersten Öffnen des Connectors werden automatisch alle benötigen Daten (ioly Core, aktueller Modulkatalog) heruntergeladen.
- Sobald die Modulliste angezeigt wird kann mit nur einem Klick ein Modul installiert/aktiviert werden.
- Sollte ein Modul nicht als Download zur Verfügung stehen gibt es einen direkten Link zur Modulseite des Anbieters.

![OXID Module Connector](oxid_module_connector.png)


## Notices

This module is based on [ioly](https://github.com/ioly/) and was developed during the [#oxhackathon16](https://openspacer.org/12-oxid-community/136-oxid-hackathon-nuernberg-2016/) event. The OXIDforge editorial team currently takes care of the content (modules) in their leisure time as best as they can.

Please note that this is a community project that comes with absolutely no warranty nor claim for completeness or correctness of the content. If you like it, we appreciate if you contribute either modules or general improvements.

>**ATTENTION!**
This module is designed for development and testing environments. Please do not install any module in your live shop environment! Please backup your installation (database + files) before installing a module via OXID Module Connector!


## Requirements

- PHP 5.4.0
- PHP extensions:
  * cUrl
  * JSON
  * ZIP


## License
OXID module connector is licensed under the MIT License - see the [LICENSE file](https://github.com/OXIDprojects/OXID-Module-Connector/blob/recipes/LICENSE) for details.
