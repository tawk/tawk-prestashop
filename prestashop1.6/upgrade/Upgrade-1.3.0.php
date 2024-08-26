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

require_once _PS_MODULE_DIR_ . 'tawkto/vendor/autoload.php';

use Tawk\Helpers\PathHelper;

/**
 * Upgrade entry point
 *
 * @return bool
 */
function upgrade_module_1_3_0()
{
    // update the records for TAWKTO_WIDGET_OPTS.
    $update_records_result = update_records();
    if (!$update_records_result) {
        return false;
    }

    return true;
}

/**
 * Update records
 *
 * @return bool
 */
function update_records()
{
    $res = true;

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
 * @param int|null $shop_group_id shop group ID
 * @param int|null $shop_id shop ID
 *
 * @return bool
 */
function update_visibility_opts($shop_group_id = null, $shop_id = null)
{
    if (isset($shop_id)) {
        Shop::setContext(Shop::CONTEXT_SHOP, $shop_id);
    } elseif (isset($shop_group_id)) {
        Shop::setContext(Shop::CONTEXT_GROUP, $shop_group_id);
    } else {
        Shop::setContext(Shop::CONTEXT_ALL);
    }

    $opts = Configuration::get(TawkTo::TAWKTO_WIDGET_OPTS, null, $shop_group_id, $shop_id);

    if (!$opts) {
        return false;
    }

    $opts = json_decode($opts);

    if (isset($opts->show_oncustom)) {
        $show_oncustom = json_decode($opts->show_oncustom);
        if (is_array($show_oncustom)) {
            $opts->show_oncustom = add_wildcard_to_pattern_list($show_oncustom);
        }
    }

    if (isset($opts->hide_oncustom)) {
        $hide_oncustom = json_decode($opts->hide_oncustom);
        if (is_array($hide_oncustom)) {
            $opts->hide_oncustom = add_wildcard_to_pattern_list($hide_oncustom);
        }
    }

    return Configuration::updateValue(
        TawkTo::TAWKTO_WIDGET_OPTS,
        json_encode($opts),
        false,
        $shop_group_id,
        $shop_id
    );
}

/**
 * Check pattern list for wildcard
 *
 * @param string[] $patternList list of patterns
 * @param string $wildcard wildcard
 *
 * @return bool
 */
function check_pattern_list_has_wildcard(array $patternList, string $wildcard)
{
    foreach ($patternList as $pattern) {
        if (strpos($pattern, $wildcard) > -1) {
            return true;
        }
    }

    return false;
}

/**
 * Add wildcard to pattern list
 *
 * @param string[] $patternList list of patterns
 *
 * @return string|false
 */
function add_wildcard_to_pattern_list(array $patternList)
{
    $wildcard = PathHelper::get_wildcard();

    if (check_pattern_list_has_wildcard($patternList, $wildcard)) {
        return json_encode($patternList);
    }

    $newPatternList = [];
    $addedPatterns = [];

    foreach ($patternList as $pattern) {
        if (empty($pattern)) {
            continue;
        }

        $pattern = ltrim($pattern, PHP_EOL);
        $pattern = trim($pattern);

        if (strpos($pattern, 'http://') !== 0
              && strpos($pattern, 'https://') !== 0
              && strpos($pattern, '/') !== 0
        ) {
            // Check if the first part of the string is a host.
            // If not, add a leading / so that the pattern
            // matcher treats is as a path.
            $firstPatternChunk = explode('/', $pattern)[0];

            if (check_valid_host($firstPatternChunk) === false) {
                $pattern = '/' . $pattern;
            }
        }

        $newPatternList[] = $pattern;
        $newPattern = $pattern . '/' . $wildcard;
        if (in_array($newPattern, $patternList, true)) {
            continue;
        }

        if (true === isset($addedPatterns[$newPattern])) {
            continue;
        }

        $newPatternList[] = $newPattern;
        $addedPatterns[$newPattern] = true;
    }

    // EOL for display purposes
    return json_encode($newPatternList);
}

/**
 * Check if is hostname
 *
 * @param string $host hostname
 *
 * @return bool
 */
function check_valid_host(string $host)
{
    // contains port
    if (strpos($host, ':') < 0) {
        return true;
    }

    // is localhost
    if (strpos($host, 'localhost') === 0) {
        return true;
    }

    // gotten from https://forums.digitalpoint.com/threads/what-will-be-preg_match-for-domain-names.1953314/#post-15036873
    // but updated the ending regex part to include numbers so it also matches
    // IPs.
    $host_check_regex = '/^[a-zA-Z0-9]*((-|\.)?[a-zA-Z0-9])*\.([a-zA-Z0-9]{1,4})$/';

    return preg_match($host_check_regex, $host) > 0;
}
