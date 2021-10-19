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
        // get current shopId
        $shop = Context::getContext()->shop;
        $domain = $shop->domain;

        $optKey = TawkTo::TAWKTO_WIDGET_OPTS;

        // returns 'false' if retrieved none.
        $displayOpts = Configuration::get($optKey);
        if (!$displayOpts) {
            $displayOpts = null;
        }
        $displayOpts = Tools::jsonDecode($displayOpts);

        $sameUser = true; // assuming there is only one admin by default
        $empId = Configuration::get(TawkTo::TAWKTO_WIDGET_USER);
        if ($this->context->employee->id != $empId && $empId) {
            $sameUser = false;
        }

        $currentWidget = TawkTo::getPropertyAndWidget();
        $pageId = '';
        $widgetId = '';
        if (!empty($currentWidget)) {
            $pageId = $currentWidget['page_id'];
            $widgetId = $currentWidget['widget_id'];
        }

        $this->tpl_view_vars = array(
            'iframe_url' => $this->getIframeUrl(),
            'base_url' => $this->getBaseUrl(),
            'controller' => $this->context->link->getAdminLink('AdminTawkto'),
            'tab_id' => (int) $this->context->controller->id,
            'domain' => $domain,
            'display_opts' => $displayOpts,
            'page_id' => $pageId,
            'widget_id' => $widgetId,
            'same_user' => $sameUser
        );

        return parent::renderView();
    }

    private function getBaseUrl()
    {
        return 'https://plugins.tawk.to';
    }

    private function getIframeUrl()
    {
        $currentWidget = TawkTo::getPropertyAndWidget();
        $pageId = '';
        $widgetId = '';
        if (!empty($currentWidget)) {
            $pageId = $currentWidget['page_id'];
            $widgetId = $currentWidget['widget_id'];
        }

        return $this->getBaseUrl()
            .'/generic/widgets'
            .'?currentPageId='.$pageId
            .'&currentWidgetId='.$widgetId;
    }

    private static function idsAreCorrect($pageId, $widgetId)
    {
        return preg_match('/^[0-9A-Fa-f]{24}$/', $pageId) === 1 && preg_match('/^[a-z0-9]{1,50}$/i', $widgetId) === 1;
    }

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

        $currentWidgetKey = TawkTo::TAWKTO_SELECTED_WIDGET;
        Configuration::updateValue($currentWidgetKey, $pageId.':'.$widgetId);

        $userKey = TawkTo::TAWKTO_WIDGET_USER;
        Configuration::updateValue($userKey, $this->context->employee->id);

        die(Tools::jsonEncode(array('success' => true)));
    }

    public function ajaxProcessRemoveWidget()
    {
        $keys = array(
            TawkTo::TAWKTO_SELECTED_WIDGET,
            TawkTo::TAWKTO_WIDGET_USER
        );

        foreach ($keys as $key) {
            if (Shop::getContext() == Shop::CONTEXT_ALL) {
                Configuration::updateValue($key, '');
            } else {
                // Configuration::deleteFromContext method cannot be used by
                // 'All Shops' or the current shop context is 'CONTEXT_ALL'.
                Configuration::deleteFromContext($key);
            }
        }

        die(Tools::jsonEncode(array('success' => true)));
    }


    public function ajaxProcessSetVisibility()
    {
        $jsonOpts = array(
            'always_display' => false,

            // default value needs to be a json encoded of an empty array
            // since we're going to save a json encoded array later on.
            'hide_oncustom' => json_encode(array()),

            'show_onfrontpage' => false,
            'show_oncategory' => false,
            'show_onproduct' => false,

            // default value needs to be a json encoded of an empty array
            // since we're going to save a json encoded array later on.
            'show_oncustom' => json_encode(array()),

            'enable_visitor_recognition' => false
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
                    case 'enable_visitor_recognition':
                        $jsonOpts[$column] = ($value == 1);
                        break;
                }
            }
        }

        $key = TawkTo::TAWKTO_WIDGET_OPTS;
        Configuration::updateValue($key, json_encode($jsonOpts));

        die(Tools::jsonEncode(array('success' => true)));
    }
}
