<?php
/*
 * This is needed for cookie based authentication to encrypt password in
 * cookie
 */
$cfg['blowfish_secret'] = 'test8294o4o404'; /* YOU MUST FILL IN THIS FOR COOKIE AUTH! */

$cfg['QueryHistoryDB'] = true;
$cfg['QueryHistoryMax'] = 30;
$cfg['LeftRecentTable'] = 5;
$cfg['ShowSQL'] = true;

$cfg['FirstLevelNavigationItems'] = 
$cfg['MaxNavigationItems'] = 60;
$cfg['NavigationTreeEnableGrouping'] = false;
$cfg['NavigationTreeTableLevel'] = 0;

$cfg['Export']['asfile'] = false;
$cfg['Export']['sql_structure_or_data'] = "structure";
$cfg['Export']['sql_create_table_statements'] = false;
$cfg['Export']['sql_if_not_exists'] = false;
$cfg['Export']['sql_auto_increment'] = false;

/*
 * Servers configuration
 */
$i = 0;

$i++;
$cfg['Servers'][$i]['auth_type'] = 'config';
$cfg['Servers'][$i]['host'] = 'db';
$cfg['Servers'][$i]['connect_type'] = 'tcp';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['user'] = 'root';
$cfg['Servers'][$i]['password'] = 'localdb';
$cfg['Servers'][$i]['extension'] = 'mysqli';
