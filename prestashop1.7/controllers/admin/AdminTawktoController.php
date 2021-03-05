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

class AdminTawktoController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';

        parent::__construct();
        $this->meta_title = $this->l('tawk.to');

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->l('tawk.to');
        $this->toolbar_title[] = $this->l('Widget');
    }

    public function initToolbar()
    {
        $r = parent::initToolbar();

        if (isset($this->toolbar_btn)) {
            unset($this->toolbar_btn['back']);
        } else {
            unset($this->page_header_toolbar_btn['back']);
        }

        return $r;
    }

    public function renderView()
    {
        $this->tpl_view_vars = array(
                'iframe_url' => $this->getIframeUrl(),
                'base_url'   => $this->getBaseUrl(),
                'controller' => $this->context->link->getAdminLink('AdminTawkto'),
                'tab_id'     => (int)$this->context->controller->id,
                'shops'      => $this->getStoreDetails()
            );

        return parent::renderView();
    }

    private function getBaseUrl()
    {
        return 'https://plugins.tawk.to';
    }

    private function getIframeUrl()
    {
        return $this->getBaseUrl().'/generic/widgets?currentPageId=&currentWidgetId=';
    }

    private function getStoreDetails()
    {
        $shops = Shop::getShops();
        $details = array();
        if (count($shops) > 0) {
            foreach ($shops as $shop) {
                $shopId = $shop['id_shop'];
                $details[$shopId] = array(
                    'id' => (int)$shopId,
                    'name' => $shop['name'],
                    'domain' => trim($shop['domain'])
                );
            }
        }
        return $details;
    }

    private static function idsAreCorrect($pageId, $widgetId)
    {
        return preg_match('/^[0-9A-Fa-f]{24}$/', $pageId) === 1 && preg_match('/^[a-z0-9]{1,50}$/i', $widgetId) === 1;
    }

    // Controller Functions
    public function ajaxProcessSetWidget()
    {
        if (!Tools::getIsset('pageId') || !Tools::getIsset('widgetId')) {
            die(Tools::jsonEncode(array('success' => false)));
        }

        $pageId = Tools::getValue('pageId');
        $widgetId = Tools::getValue('widgetId');
        if (!self::idsAreCorrect($pageId, $widgetId)) {
            die(Tools::jsonEncode(array('success' => false)));
        }

        $shopId = Tools::getValue('shopId');

        $pageKey = TawkTo::TAWKTO_WIDGET_PAGE_ID."_{$shopId}";
        Configuration::updateGlobalValue($pageKey, Tools::getValue('pageId'), false, 0, 0);

        $widgetKey = TawkTo::TAWKTO_WIDGET_WIDGET_ID."_{$shopId}";
        Configuration::updateGlobalValue($widgetKey, Tools::getValue('widgetId'), false, 0, 0);

        $userKey = TawkTo::TAWKTO_WIDGET_USER."_{$shopId}";
        Configuration::updateGlobalValue($userKey, $this->context->employee->id, false, 0, 0);

        die(Tools::jsonEncode(array('success' => true)));
    }

    public function ajaxProcessRemoveWidget()
    {
        $shopId = Tools::getValue('shopId');

        $pageKey = TawkTo::TAWKTO_WIDGET_PAGE_ID."_{$shopId}";
        Configuration::deleteByName($pageKey);

        $widgetKey = TawkTo::TAWKTO_WIDGET_WIDGET_ID."_{$shopId}";
        Configuration::deleteByName($widgetKey);

        $userKey = TawkTo::TAWKTO_WIDGET_USER."_{$shopId}";
        Configuration::deleteByName($userKey);

        die(Tools::jsonEncode(array('success' => true)));
    }

    public function ajaxProcessSetVisibility()
    {
        $shopId = Tools::getValue('shopId');

        $jsonOpts = array(
            'always_display' => false,
            'hide_oncustom' => array(),
            'show_onfrontpage' => false,
            'show_oncategory' => false,
            'show_onproduct' => false,
            'show_oncustom' => array(),
        );

        $options = Tools::getValue('options');
        if (!empty($options)) {
            $options = explode('&', $options);
            foreach ($options as $post) {
                list($column, $value) = explode('=', $post);
                switch ($column) {
                    case 'hide_oncustom':
                    case 'show_oncustom':
                        // replace newlines and returns with comma, and convert to array for saving
                        $value = urldecode($value);
                        $value = str_ireplace(array("\r\n", "\r", "\n"), ',', $value);
                        if (!empty($value)) {
                            $value = explode(",", $value);
                            $jsonOpts[$column] = json_encode($value);
                        }
                        break;
                    case 'show_onfrontpage':
                    case 'show_oncategory':
                    case 'show_onproduct':
                    case 'always_display':
                        $jsonOpts[$column] = ($value == 1);
                        break;
                }
            }
        }

        $key = TawkTo::TAWKTO_WIDGET_OPTS."_{$shopId}";
        Configuration::updateGlobalValue($key, json_encode($jsonOpts), false, 0, 0);

        die(Tools::jsonEncode(array('success' => true)));
    }

    public function displayAjaxGetStoreVisibilityOpts()
    {
        $result = null;
        if (!Tools::getIsset('shopId')) {
            die(Tools::jsonEncode($result));
        }

        $shopId = Tools::getValue('shopId');
        $opts = Configuration::get(TawkTo::TAWKTO_WIDGET_OPTS."_{$shopId}");

        // this prevents $result to return $opts as 'false'.
        if (!$opts) {
            die(Tools::jsonEncode($result));
        }
        $result = $opts;

        // already json encoded
        die($result);
    }

    public function displayAjaxGetStoreWidget()
    {
        if (!Tools::getIsset('shopId')) {
            die(Tools::jsonEncode(null));
        }

        $shopId = Tools::getValue('shopId');
        $result = array();

        $result['sameUser'] = true; // assuming there is only one admin by default
        $empId = Configuration::get(TawkTo::TAWKTO_WIDGET_USER."_{$shopId}");
        if ($this->context->employee->id != $empId && $empId) {
            $result['sameUser'] = false;
        }

        $pageId = Configuration::get(TawkTo::TAWKTO_WIDGET_PAGE_ID."_{$shopId}");
        if ($pageId) {
            $result['pageId'] = $pageId;
        }

        $widgetId = Configuration::get(TawkTo::TAWKTO_WIDGET_WIDGET_ID."_{$shopId}");
        if ($widgetId) {
            $result['widgetId'] = $widgetId;
        }

        die(Tools::jsonEncode($result));
    }
}
