<?php

/**
 * @author smxsm
 * @package ioly-oxid-ce-487
 * 
 * Encoding: UTF-8
 * Date: 02.11.2014
 * 
 * Description of vendormetadata
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
    'title'       => 'ioly Module Manager OXID Connector',
    'description' => array(
        'de' => 'We all love ioly.',
        'en' => 'We all love ioly.',
    ),
    'thumbnail'   => 'ioly_logo.png',
    'version'     => '1.0.0',
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
        array('group' => 'IOLY', 'name' => 'iolycookbookurl', 'type' => 'str',  'value' => 'http://github.com/ioly/ioly/archive/master.zip'),        
    )
);
?>
