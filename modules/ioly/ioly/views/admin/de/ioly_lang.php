<?php
/**
 * ioly
 *
 * PHP version 5.3
 *
 * @category ioly_modulmanager
 * @package  OXID Connector
 * @author   Dave Holloway <dh@gn2-netwerk.de>
 * @author   Tobias Merkl <merkl@proudsourcing.de>
 * @author   Stefan Moises <stefan@rent-a-hero.de>
 * @license  MIT License http://opensource.org/licenses/MIT
 * @link     http://getioly.com/
 * @version	 1.6.4
 */
$sLangName  = "Deutsch";
// -------------------------------
// RESOURCE IDENTITFIER = STRING
// -------------------------------
$aLang = array(
    'charset'                                   => 'UTF-8',
    'mxioly'                                    => 'ioly',
    'IOLY_MAIN_HEADLINE'                        => 'ioly',
    'IOLY_MAIN_TITLE'                           => 'ioly OXID Connector',
    'IOLY_MODULE_NAME'                          => 'Modulname',
    'IOLY_VERSION_MODULE'                       => 'ioly OXID Connector Version:',
    'IOLY_VERSION_CORE'                         => 'ioly Core Version:',
    'IOLY_VERSION_RECIPES'                      => 'ioly Rezepte Version:',
    'IOLY_MODULE_DOWNLOAD'                      => '',
    'IOLY_IOLY_UPDATE_BUTTON'                   => 'ioly Core aktualisieren',
    'IOLY_RECIPE_UPDATE_BUTTON'                 => 'ioly Rezepte aktualisieren',
    'IOLY_CONNECTOR_UPDATE_BUTTON'              => 'ioly Connector aktualisieren',
    'IOLY_MODULE_OXID_VERSION'                  => 'unterst&uuml;tzte OXID-Version',
    'IOLY_MODULE_DOWNLOAD_SUCCESS'              => 'Modul-Download erfolgreich!',
    'IOLY_MODULE_UNINSTALL_SUCCESS'             => 'Modul-Dateien erfolgreich entfernt!',
    'IOLY_MAIN_INFOTEXT'                        => 'Mit dem ioly Modulmanager kannst du mit einem Klick OXID Module herunterladen und in deinem Shop installieren. Mehr Informationen zu ioly findest du unter <a href="https://github.com/ioly/ioly" target="_blank">github.com/ioly/ioly</a> bzw. <a href="http://getioly.com" target="_blank">getioly.com</a>.<br>',
    'IOLY_RECIPE_UPDATE_SUCCESS'                => 'ioly Rezepte erfolgreich aktualisiert!',
    'IOLY_RECIPE_UPDATE_ERROR'                  => 'Problem beim Aktualisieren der ioly Rezepte: ',
    'IOLY_IOLY_UPDATE_SUCCESS'                  => 'ioly Core erfolgreich aktualisiert!',
    'IOLY_IOLY_UPDATE_ERROR'                    => 'Problem beim Aktualisieren des ioly Cores: ',
    'IOLY_CONNECTOR_UPDATE_SUCCESS'             => 'ioly OXID Connector erfolgreich aktualisiert!',
    'IOLY_CONNECTOR_UPDATE_ERROR'               => 'Problem beim Aktualisieren des ioly OXID Connectors.',
    'IOLY_INSTALL_MODULE_HINT'                  => 'Modul-Dateien in das Shopverzeichnis herunterladen und entpacken',
    'IOLY_REINSTALL_MODULE_HINT'                => 'Modul-Dateien in das Shopverzeichnis erneut herunterladen und entpacken',
    'IOLY_UNINSTALL_MODULE_HINT'                => 'Modul-Dateien aus dem Shopverzeichnis entfernen',
    'IOLY_MODULE_INSTALLED'                     => 'Modul installiert',
    'IOLY_EXCEPTION_CORE_NOT_LOADED'            => "ioly Core konnte nicht geladen werden!<br>Folgende Verzeichnisse ben&ouml;tigen Schreibrechte:<br><br><i>&lsaquo;shoproot&rsaquo;/ioly/<br>&lsaquo;shoproot&rsaquo;/modules/<br>&lsaquo;shoproot&rsaquo;/modules/ioly/ioly/</i>",
    'IOLY_PROJECT_URL'                          => 'Moduldetails',
    'IOLY_PRICE_FREE'                           => 'kostenlos',
    'IOLY_OXID_VERSIONS'                        => 'OXID',
    'IOLY_TOGGLE_INFO'                          => 'Infos anzeigen',
    'IOLY_ERROR_BASELIB_MISSING'                => "JS Bibliotheken nocht nicht vorhanden. Installiere '%s' in Version '%s' in 'ioly/libs' ...",
    'IOLY_BUTTON_DOWNLOAD_VERSION_1'            => 'Version',
    'IOLY_BUTTON_DOWNLOAD_VERSION_2'            => 'installieren',
    'IOLY_BUTTON_DOWNLOAD_VERSION_3'            => 'erneut installieren',
    'IOLY_BUTTON_REMOVE_VERSION_1'              => 'Version',
    'IOLY_BUTTON_REMOVE_VERSION_2'              => 'deinstallieren',
    'IOLY_OXID_MAPPINGS'                        => 'Datei-Mappings',
    'IOLY_EXCEPTION_MESSAGE_CODE_1022'          => 'Das Modul wurde deinstalliert, aber folgende Dateien konnten nicht gel&oum;scht werden:',
    'IOLY_EXCEPTION_MESSAGE_CODE_1023'          => 'Das Modul wurde deinstalliert, aber folgende Dateien wurden modifiziert und konnten nicht gel&oum;scht werden:',
    'IOLY_EXCEPTION_MESSAGE_CODE_1021'          => 'Konnte die angegebene Digest-Version nicht finden.',
    'IOLY_EXCEPTION_MESSAGE_CODE_1020'          => 'Konnte keine Digest-Version finden.',
    'IOLY_EXCEPTION_MESSAGE_CODE_1030'          => 'Keine Lizenz-Datei f&uuml;r kommerzielles Modul vorhanden.',
    'IOLY_EXCEPTION_MESSAGE_CODE_1008'          => 'Bitte pr&uuml;fen Sie die Schreibrechte folgender Dateien/Ordner:<br>',
    'IOLY_EXCEPTION_MESSAGE_CODE_1007'          => 'Der Download-Server antwortete mit Fehler:',
    'IOLY_EXCEPTION_MESSAGE_CODE_1010'          => 'Bitte pr&uuml;fen Sie die Schreibrechte folgender Dateien/Ordner:<br>',
    'IOLY_EXCEPTION_MESSAGE_CODE_1011'          => 'Bitte pr&uuml;fen Sie die Schreibrechte folgender Dateien/Ordner:<br>',
    'IOLY_EXCEPTION_MESSAGE_CODE_1000'          => 'Bitte rufen Sie zuerst \$ioly->setSystemBasePath(\$path) auf oder f&uuml;hren Sie folgendes aus: export IOLY_SYSTEM_BASE=%path%',
    'IOLY_EXCEPTION_MESSAGE_CODE_1001'          => 'Bitte rufen Sie zuerst \$ioly->setSystemVersion(\$version) auf oder f&uuml;hren Sie folgendes aus: export IOLY_SYSTEM_VERSION=%version%',
    'IOLY_EXCEPTION_MESSAGE_CODE_1003'          => 'Konnte Modul-Version nicht finden:',
    'IOLY_EXCEPTION_MESSAGE_CODE_1005'          => 'Konnte Modul nicht finden:',
    'IOLY_EXCEPTION_MESSAGE_CODE_1006'          => 'Bitte benutzen Sie vendor/package um ein Modul zu installieren.',
    'SHOP_MODULE_GROUP_IOLY'                    => 'ioly Einstellungen',
    'SHOP_MODULE_iolycookbookurl'               => 'ioly Cookbook URL (URL der Rezepte als ZIP-Archiv)',
    'SHOP_MODULE_iolyautoupdate'                => 'ioly (Core, Rezepte, Connector) automatisch aktualisieren?',
    'SHOP_MODULE_iolyenableinst'                => 'Modul-De-/Installation mit ioly aktivieren?',
    'HELP_SHOP_MODULE_iolyenableinst'           => 'Mit ioly k&ouml;nnen dann Module f&uuml;r beliebige Subshops deaktiviert oder aktiviert werden.',
    'SHOP_MODULE_iolycheckactive'               => 'In Liste pr&uuml;fen, ob Module aktiv sind (kostet Performance)?',
    'HELP_SHOP_MODULE_iolycheckactive'          => 'Beim Laden der Liste in der &Uuml;bersicht pr&uuml;fen, ob die installierten Module aktiv sind (kostet Performance, weil alle metadata.php Dateien der Module eingelesen werden)?',
    'IOLY_RECIPES'                              => 'Rezepte',
    'IOLY_EXCEPTION_MESSAGE_MODULE_ACTIVE'      => 'Das Modul ist noch im Shop aktiv und kann nicht entfernt werden! Bitte deaktivieren Sie es vor dem Entfernen.',
    'IOLY_ACTIVATE_MODULE'                      => 'Modul aktivieren ...',
    'IOLY_DEACTIVATE_MODULE'                    => 'Modul deaktivieren ...',
    'IOLY_DROPDOWN_MORE_ACTIONS'                => 'Weitere Aktionen',
    'IOLY_CLEAR_TEMP'                           => 'Tmp-Verzeichnis l&ouml;schen',
    'IOLY_CREATE_VIEWS'                         => 'Views neu erzeugen ...',
);
?>
