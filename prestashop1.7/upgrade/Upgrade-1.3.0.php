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

function upgrade_module_1_3_0()
{
    // update the records for TAWKTO_WIDGET_OPTS.
    $update_records_result = update_records();
    if (!$update_records_result) {
        return false;
    }

    return true;
}

function update_records()
{
    $res = true;

    // modify global first
    $res &= update_visibility_opts();

    $shop_ids = Shop::getCompleteListOfShopsID();

    $updated_groups = array();
    foreach ($shop_ids as $shop_id) {
        $shop_group_id = (int)Shop::getGroupFromShop($shop_id);

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
    $base_url = parse_url(Context::getContext()->shop->getBaseURL(true));
    $shop_host = $base_url['host'] . ':' . $base_url['port'];

    if (!$opts) {
        return false;
    }

    $opts = json_decode($opts);


    if (isset($opts->show_oncustom)) {
        $show_oncustom = json_decode($opts->show_oncustom);
        if (is_array($show_oncustom)) {
            $opts->show_oncustom = addWildcardToPatternList($show_oncustom, $shop_host);
        }
    }

    if (isset($opts->hide_oncustom)) {
        $hide_oncustom = json_decode($opts->hide_oncustom);
        if (is_array($hide_oncustom)) {
            $opts->hide_oncustom = addWildcardToPatternList($hide_oncustom, $shop_host);
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

function addWildcardToPatternList($patternList, $storeHost)
{
    $wildcard = PathHelper::get_wildcard();

    $newPatternList = [];
    $addedPatterns = [];

    foreach ($patternList as $pattern) {
        if (empty($pattern)) {
            continue;
        }

        $pattern = ltrim($pattern, PHP_EOL);
        $pattern = trim($pattern);

        if (strpos($pattern, 'http://') !== 0 &&
            strpos($pattern, 'https://') !== 0 &&
            strpos($pattern, '/') !== 0
        ) {
            // Check if the first part of the string is a host.
            // If not, add a leading / so that the pattern
            // matcher treats is as a path.
            $firstPatternChunk = explode('/', $pattern)[0];
            if ($firstPatternChunk !== $storeHost) {
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

        $newPatternList[]             = $newPattern;
        $addedPatterns[$newPattern] = true;
    }

    // EOL for display purposes
    return json_encode($newPatternList);
}
