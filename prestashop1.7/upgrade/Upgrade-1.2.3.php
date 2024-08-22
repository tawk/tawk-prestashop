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
 * @copyright Copyright (c) 2014-2022 tawk.to
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

if (!defined('_PS_VERSION_')) {
  exit;
}

/**
 * Upgrade entry point
 *
 * @return boolean
 */
function upgrade_module_1_2_3() {
  // update the records for TAWKTO_WIDGET_OPTS.
  $update_records_result = update_records();
  if (!$update_records_result) {
    return FALSE;
  }

  return TRUE;
}

/**
 * Update records
 *
 * @return boolean
 */
function update_records() {
  $res = TRUE;

  // modify global first
  $res &= update_visibility_opts();

  $shop_ids = Shop::getCompleteListOfShopsID();

  $updated_groups = [];
  foreach ($shop_ids as $shop_id) {
    $shop_group_id = (int) Shop::getGroupFromShop($shop_id);

    if (!in_array($shop_group_id, $updated_groups)) {
      // update the group config
      $res &= update_visibility_opts($shop_group_id);
      $updated_groups[] = $shop_group_id;
    }

    // update the shop config
    $res &= update_visibility_opts($shop_group_id, $shop_id);
  }

  return $res;
}

/**
 * Update visibility options
 *
 * @param null|integer $shop_group_id Shop group ID.
 * @param null|integer $shop_id       Shop ID.
 * @return boolean
 */
function update_visibility_opts($shop_group_id = NULL, $shop_id = NULL) {
  if (isset($shop_id)) {
    Shop::setContext(Shop::CONTEXT_SHOP, $shop_id);
  }
  elseif (isset($shop_group_id)) {
    Shop::setContext(Shop::CONTEXT_GROUP, $shop_group_id);
  }
  else {
    Shop::setContext(Shop::CONTEXT_ALL);
  }

  $opts = Configuration::get(TawkTo::TAWKTO_WIDGET_OPTS, NULL, $shop_group_id, $shop_id);

  if (!$opts) {
    return FALSE;
  }

  $opts = json_decode($opts);

  if (isset($opts->show_oncustom) && is_array($opts->show_oncustom) && $opts->show_oncustom === []) {
    $opts->show_oncustom = json_encode([]);
  }

  if (isset($opts->hide_oncustom) && is_array($opts->hide_oncustom) && $opts->hide_oncustom === []) {
    $opts->hide_oncustom = json_encode([]);
  }

  return Configuration::updateValue(
        TawkTo::TAWKTO_WIDGET_OPTS,
        json_encode($opts),
        FALSE,
        $shop_group_id,
        $shop_id
    );
}
