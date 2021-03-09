<?php
/**
 * tawk.to
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@tawk.to so we can send you a copy immediately.
 *
 * @author tawkto support@tawk.to
 * @copyright Copyright (c) 2014-2021 tawk.to
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_2_0()
{
    $db = Db::getInstance();
    $keys = array(
        TawkTo::TAWKTO_WIDGET_PAGE_ID,
        TawkTo::TAWKTO_WIDGET_WIDGET_ID,
        TawkTo::TAWKTO_WIDGET_OPTS,
        TawkTo::TAWKTO_WIDGET_USER
    );

    // start building the update sql statement
    $sql = array(
        'UPDATE '._DB_PREFIX_.bqSQL(Configuration::$definition['table']).' conf',
        'SET conf.name = CASE'
    );

    // build case when clause
    foreach ($keys as $key) {
        array_push($sql, "WHEN conf.name LIKE '".pSQL($key)."%' THEN '".pSQL($key)."'");
    }

    // build else and end case clause and where clause
    array_push(
        $sql,
        "ELSE conf.name",
        "END",
        "WHERE conf.name LIKE 'TAWKTO_WIDGET_%';"
    );

    // join sql array and execute
    $result = $db->execute(join(' ', $sql)); //returns boolean value

    return $result;
}
