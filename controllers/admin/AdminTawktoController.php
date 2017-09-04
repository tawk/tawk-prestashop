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
 * @author    tawk.to <support(at)tawk.to>
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
        $this->meta_title = $this->l('tawk.to');

        parent::__construct();
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
        // get all stores
        $shops = Shop::getShops();

        $shopId = 1;
        $domain = $_SERVER['SERVER_NAME'];
        foreach ($shops as $shop) {
            if ($domain && $shop['domain']==$domain) {
                $domain = trim($shop['domain']);
                $shopId = (int) $shop['id_shop'];
            }
        }
        reset($shops);

        $optKey = TawkTo::TAWKTO_WIDGET_OPTS."_{$shopId}";
        if (!$displayOpts = Configuration::get($optKey)) {
            $displayOpts = null;
        }
        // Check for visibility options
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('configuration');
        $sql->where('name = "'.TawkTo::TAWKTO_WIDGET_OPTS."_{$shopId}".'"');
        $result =  Db::getInstance()->executeS($sql);
        $result = current($result);
        $displayOpts = json_decode($result['value']);

        $sameUser = true; // assuming there is only one admin by default
        $empId = Configuration::get(TawkTo::TAWKTO_WIDGET_USER."_{$shopId}");
        if ($this->context->employee->id != $empId && $empId) {
            $sameUser = false;
        }

        $this->tpl_view_vars = array(
                'iframe_url' => $this->getIframeUrl(),
                'base_url'   => $this->getBaseUrl(),
                'controller' => $this->context->link->getAdminLink('AdminTawkto'),
                'tab_id'     => (int)$this->context->controller->id,
                'shops'      => $shops,
                'domain'     => $domain,
                'display_opts' => $displayOpts,
                'page_id'    => Configuration::get(TawkTo::TAWKTO_WIDGET_PAGE_ID."_{$shopId}"),
                'widget_id'  => Configuration::get(TawkTo::TAWKTO_WIDGET_WIDGET_ID."_{$shopId}"),
                'same_user'  => $sameUser
            );

        return parent::renderView();
    }

    private function getBaseUrl()
    {
        return 'https://plugins.tawk.to';
    }

    private function getIframeUrl()
    {
        $domain = $_SERVER['SERVER_NAME'];
        $shopId = $this->context->shop->id;

        // we still need to do this as prestashop is not fetching the correct context SHOP values
        // when accessing the module admin via multistore
        $shops = Shop::getShops();
        if (count($shops) > 1) {
            foreach ($shops as $shop) {
                if ($shop['domain']==$domain) {
                    $shopId = (int) $shop['id_shop'];
                }
            }
        }
        
        $pageKey = TawkTo::TAWKTO_WIDGET_PAGE_ID."_{$shopId}";
        $widgetKey = TawkTo::TAWKTO_WIDGET_WIDGET_ID."_{$shopId}";
        return $this->getBaseUrl()
            .'/generic/widgets'
            .'?currentPageId='.Configuration::get($pageKey)
            .'&currentWidgetId='.Configuration::get($widgetKey);
    }

    private static function idsAreCorrect($pageId, $widgetId)
    {
        return preg_match('/^[0-9A-Fa-f]{24}$/', $pageId) === 1 && preg_match('/^[a-z0-9]{1,50}$/i', $widgetId) === 1;
    }

    public function ajaxProcessSetWidget()
    {
        $fail = false;
        if (!Tools::getIsset(Tools::getValue('pageId')) || !Tools::getIsset(Tools::getValue('widgetId'))) {
            $fail = true;
        }

        if (!self::idsAreCorrect(Tools::getValue('pageId'), Tools::getValue('widgetId'))) {
            $fail = true;
        }

        if ($fail) {
            die(Tools::jsonEncode(array('success' => false)));
        }

        $shopId = 1;
        $shops = Shop::getShops();
        $domain = addslashes(trim($_REQUEST['domain']));
        if (count($shops) && !empty($domain)) {
            foreach ($shops as $shop) {
                if ($domain && $shop['domain']==$domain) {
                    $shopId = (int) $shop['id_shop'];
                }
            }
        }
        
        $pageKey = TawkTo::TAWKTO_WIDGET_PAGE_ID."_{$shopId}";
        Configuration::updateValue($pageKey, Tools::getValue('pageId'));

        $widgetKey = TawkTo::TAWKTO_WIDGET_WIDGET_ID."_{$shopId}";
        Configuration::updateValue($widgetKey, Tools::getValue('widgetId'));

        $userKey = TawkTo::TAWKTO_WIDGET_USER."_{$shopId}";
        Configuration::updateValue($userKey, $this->context->employee->id);

        die(Tools::jsonEncode(array('success' => true)));
    }

    public function ajaxProcessRemoveWidget()
    {
        $shopId = 1;
        $shops = Shop::getShops();
        $domain = addslashes(trim($_REQUEST['domain']));
        if (count($shops) && !empty($domain)) {
            foreach ($shops as $shop) {
                if ($domain && $shop['domain']==$domain) {
                    $shopId = (int) $shop['id_shop'];
                }
            }
        }

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
        $shopId = 1;
        $shops = Shop::getShops();
        $domain = addslashes(trim($_REQUEST['domain']));
        if (count($shops) && !empty($domain)) {
            foreach ($shops as $key => $shop) {
                if ($domain && $shop['domain']==$domain) {
                    $shopId = (int) $shop['id_shop'];
                }
            }
        }

        $jsonOpts = array(
                'always_display' => false,
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
                    case 'show_oncustom':
                        // replace newlines and returns with comma, and convert to array for saving
                        $value = urldecode($value);
                        $value = str_ireplace(array("\r\n", "\r", "\n"), ',', $value);
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
}
