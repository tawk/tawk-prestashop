<?php
/**
 * Class AdminTawktoController
 *
 * @category  Prestashop
 * @category  Module
 * @author    tawk.to <support(at)tawk.to>
 * @copyright Mediacom87
 * @license   opensource license see comment below
 */

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
 * @copyright   Copyright (c) 2014 tawk.to
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

    public function initToolBarTitle() {
        $this->toolbar_title[] = $this->l('tawk.to');
        $this->toolbar_title[] = $this->l('Widget');
    }

    public function initToolbar()
    {
        $r = parent::initToolbar();

        if(isset($this->toolbar_btn)) {
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
        if (count($shops) > 1) {
            foreach ($shops as $key => $shop) {
                $shopId = $shop['id_shop'];
                $details[$shopId] = array(
                    'id' => intval($shopId),
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
        if(!isset($_POST['pageId']) || !isset($_POST['widgetId']) || !self::idsAreCorrect($_POST['pageId'], $_POST['widgetId'])) {
            die(Tools::jsonEncode(array('success' => false)));
        }

        $shopId = $_POST['shopId'];

        $pageKey = TawkTo::TAWKTO_WIDGET_PAGE_ID."_{$shopId}";
        Configuration::updateValue($pageKey, $_POST['pageId']);

        $widgetKey = TawkTo::TAWKTO_WIDGET_WIDGET_ID."_{$shopId}";
        Configuration::updateValue($widgetKey, $_POST['widgetId']);

        $userKey = TawkTo::TAWKTO_WIDGET_USER."_{$shopId}";
        Configuration::updateValue($userKey, $this->context->employee->id);

        die(Tools::jsonEncode(array('success' => true)));
    }

    public function ajaxProcessRemoveWidget()
    {
        $shopId = $_POST['shopId'];

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
        $shopId = $_POST['shopId'];

        $jsonOpts = array(
                'always_display' => false,
                'hide_oncustom' => array(),
                'show_onfrontpage' => false,
                'show_oncategory' => false,
                'show_onproduct' => false,
                'show_oncustom' => array(),
            );

        if (isset($_REQUEST['options']) && !empty($_REQUEST['options'])) {
            $options = explode('&', $_REQUEST['options']);
            foreach ($options as $post) {
                list($column, $value) = explode('=', $post);
                switch ($column) {
                    case 'hide_oncustom':
                    case 'show_oncustom':
                        // replace newlines and returns with comma, and convert to array for saving
                        $value = urldecode($value);
                        $value = str_ireplace(["\r\n", "\r", "\n"], ',', $value);
                        $value = explode(",", $value);
                        $value = (empty($value)||!$value)?array():$value;
                        $jsonOpts[$column] = json_encode($value);
                        break;

                    case 'show_onfrontpage':
                    case 'show_oncategory':
                    case 'show_onproduct':
                    case 'always_display':
                    // default:
                        $jsonOpts[$column] = ($value==1)?true:false;
                        break;
                }
            }
        }

        $key = TawkTo::TAWKTO_WIDGET_OPTS."_{$shopId}";
        Configuration::updateValue($key, json_encode($jsonOpts));

        // not needed to log who set visibility
        // $key = TawkTo::TAWKTO_WIDGET_USER."_{$shopId}";
        // Configuration::updateValue($key, $this->context->employee->id);

        die(Tools::jsonEncode(array('success' => true)));
    }

    public function displayAjaxGetStoreVisibilityOpts() {
        $result = null;
        if (!isset($_GET['shopId'])) {
            die(Tools::jsonEncode($result));
        }

        $shopId = $_GET['shopId'];
        $opts = Configuration::get(TawkTo::TAWKTO_WIDGET_OPTS."_{$shopId}");

        // this prevents $result to return $opts as 'false'.
        if (!$opts) {
            die(Tools::jsonEncode($result));
        }
        $result = $opts;

        // already json encoded
        die($result);
    }

    public function displayAjaxGetStoreWidget() {
        if (!isset($_GET['shopId'])) {
            die(Tools::jsonEncode($result));
        }

        $shopId = $_GET['shopId'];
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
