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
 * @version  1.9.0
 */
 
/**
 * Metadata version
 */
$sMetadataVersion = '1.1';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'ioly',
    'title'       => 'ioly OXID Connector',
    'description' => array(
        'de' => 'ioly Modulmanager (<a href="http://getioly.com" target="_blank">getioly.com</a> / <a href="https://github.com/ioly/ioly" target="_blank">github.com/ioly/ioly</a>)',
        'en' => 'ioly module manager (<a href="http://getioly.com" target="_blank">getioly.com</a> / <a href="https://github.com/ioly/ioly" target="_blank">github.com/ioly/ioly</a>)',
    ),
    'thumbnail'   => 'ioly_logo.png',
    'version'     => '1.9.1',
    'author'      => 'ioly',
    'url'         => 'https://github.com/ioly/ioly',
    'email'       => 'hello@getioly.com',
    'extend'      => array(
    ),
    'files'       => array(
        'ioly_main'     => 'ioly/ioly/controllers/admin/ioly_main.php',
        'ioly_helper'   => 'ioly/ioly/core/ioly_helper.php',
    ),
    'templates'   => array(
        'ioly_main.tpl'     => 'ioly/ioly/views/admin/tpl/ioly_main.tpl',
    ),
    'blocks'      => array(
        array(
            'template' => 'headitem.tpl',
            'block' => 'admin_headitem_incjs',
            'file' => '/blocks/admin_headitem_incjs.tpl'
        ),
        array(
            'template' => 'headitem.tpl',
            'block' => 'admin_headitem_js',
            'file' => '/blocks/admin_headitem_js.tpl'
        ),
        array(
            'template' => 'headitem.tpl',
            'block' => 'admin_headitem_inccss',
            'file' => '/blocks/admin_headitem_inccss.tpl'
        ),
    ),
    'events'      => array(
        //'onActivate' => 'ioly_setup::onActivate',
    ),
    'settings'    => array(
        array('group' => 'IOLY', 'name' => 'iolycookbookurl', 'type' => 'aarr',  'value' => array('ioly' => 'http://github.com/ioly/ioly/archive/master.zip')),
        array('group' => 'IOLY', 'name' => 'iolyautoupdate', 'type' => 'bool',  'value' => false),
        array('group' => 'IOLY', 'name' => 'iolyenableinst', 'type' => 'bool',  'value' => true),
        array('group' => 'IOLY', 'name' => 'iolycheckactive', 'type' => 'bool',  'value' => false),
    )
);
?>
