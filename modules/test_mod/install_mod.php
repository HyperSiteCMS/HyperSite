<?php

/*
 * @package         HyperSite CMS
 * @file            install_mod.php
 * @file_desc       Installation file for test_mod
 * @author          Ryan Morrison
 * @website         -
 * @copyright       (c) 2019 HyperSite CMS
 * @license         http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/* Check if we are in CMS otherwise exit code. */
if (!defined('IN_HSCMS')) {
    exit;
}

/* Main Code here */
/*
 * This is where you would put any relevant code for installing a module. This code is called whenever you "load" a module, so if you unload
 * a module it will reverse any action. Ie- if you create a database table on module load, it will delete said table on unload and all data
 * in the table will be lost. Please back up the database (or ask your web-host to do so) before you unload modules.
 */
$install_actions = array(
    0 => array(
        'create' => array(
            'test_mod' => array(
                'id' => array(
                    'type' => 'INT',
                    'length' => 11,
                    'primary' => true,
                    'auto_increment' => true
                ),
                'this' => array(
                    'type' => 'VARCHAR',
                    'length' => 255,
                    'allow_null' => false
                ),
                'thing' => array(
                    'type' => 'VARCHAR',
                    'length' => 255,
                    'allow_null' => true,
                    'default' => 'abcdefgh'
                )
            ),
        )
    ),
    1 => array(
        'insert' => array(
            'table' => 'test_mod',
            'entries' => array(
                'this' => 'Some thing'
            )
        )
    ),
    2 => array(
        'insert' => array(
            'table' => 'test_mod',
            'entries' => array(
                'this' => 'another thing',
                'thing' => 'different'
            )
        )
    ),
    3 => array(
        'insert' => array(
            'table' => 'navigation',
            'entries' => array(
                'url' => 'test_mod',
                'title' => 'Test Module'
            )
        )
    )
);
