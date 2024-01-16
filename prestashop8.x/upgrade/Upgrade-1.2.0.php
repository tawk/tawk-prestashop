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

    // remove the suffix first on the config key names.
    $remove_suffix_result = remove_suffix($db);
    if (!$remove_suffix_result) {
        return false;
    }

    // force reload cache
    Configuration::loadConfiguration();

    // insert the new records for TAWKTO_SELECTED_WIDGET.
    $insert_records_result = insert_records();
    if (!$insert_records_result) {
        return false;
    }

    // delete records for TAWKTO_WIDGET_PAGE_ID & TAWKTO_WIDGET_WIDGET_ID
    $remove_extras_result = remove_extras();
    if (!$remove_extras_result) {
        return false;
    }

    return true;
}

function remove_suffix($db)
{
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

function insert_records()
{
    $res = true;

    // modify global first
    $res &= modify_widget();

    $shop_ids = Shop::getCompleteListOfShopsID();

    $updated_groups = array();
    foreach ($shop_ids as $shop_id) {
        $shop_group_id = (int)Shop::getGroupFromShop($shop_id);

        if (!in_array($shop_group_id, $updated_groups)) {
            // update the group config
            $res &= modify_widget($shop_group_id);
            $updated_groups[] = $shop_group_id;
        }

        // update the shop config
        $res &= modify_widget($shop_group_id, $shop_id);
    }

    return $res;
}

function modify_widget($shop_group_id = null, $shop_id = null)
{
    if (isset($shop_id)) {
        Shop::setContext(Shop::CONTEXT_SHOP, $shop_id);
    } elseif (isset($shop_group_id)) {
        Shop::setContext(Shop::CONTEXT_GROUP, $shop_group_id);
    } else {
        Shop::setContext(Shop::CONTEXT_ALL);
    }

    $page_id = Configuration::get(TawkTo::TAWKTO_WIDGET_PAGE_ID, null, $shop_group_id, $shop_id);
    $widget_id = Configuration::get(TawkTo::TAWKTO_WIDGET_WIDGET_ID, null, $shop_group_id, $shop_id);
    return Configuration::updateValue(
        TawkTo::TAWKTO_SELECTED_WIDGET,
        $page_id.':'.$widget_id,
        false,
        $shop_group_id,
        $shop_id
    );
}

function remove_extras()
{
    // remove TAWKTO_WIDGET_PAGE_ID and TAWKTO_WIDGET_WIDGET_ID records
    $res = Configuration::deleteByName(TawkTo::TAWKTO_WIDGET_PAGE_ID);
    $res &= Configuration::deleteByName(TawkTo::TAWKTO_WIDGET_WIDGET_ID);
    return $res;
}
