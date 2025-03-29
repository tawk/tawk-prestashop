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
 * @copyright Copyright (c) 2014-2024 tawk.to
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Tawkto exception
 */
class TawktoException extends Exception
{
}

/**
 * Admin settings controller
 */
class AdminTawktoController extends ModuleAdminController
{
    public const NO_CHANGE = 'nochange';

    /**
     * __construct
     *
     * @return void
     */
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

    /**
     * Set toolbar title
     *
     * @return void
     */
    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->l('tawk.to');
        $this->toolbar_title[] = $this->l('Widget');
    }

    /**
     * Set toolbar actions
     *
     * @return mixed
     */
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

    /**
     * Render admin widget settings view
     *
     * @return string
     */
    public function renderView()
    {
        // get current shopId
        $shop = Context::getContext()->shop;
        $domain = $shop->domain;

        $optKey = TawkTo::TAWKTO_WIDGET_OPTS;

        // returns 'false' if retrieved none.
        $widgetOpts = Configuration::get($optKey);
        if (!$widgetOpts) {
            $widgetOpts = null;
        }
        $widgetOpts = Tools::jsonDecode($widgetOpts);

        if ($widgetOpts && !empty($widgetOpts->js_api_key)) {
            $widgetOpts->js_api_key = self::NO_CHANGE;
        }

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

        $this->tpl_view_vars = [
            'iframe_url' => $this->getIframeUrl(),
            'prestashop_version' => _PS_VERSION_,
            'base_url' => $this->getBaseUrl(),
            'controller' => $this->context->link->getAdminLink('AdminTawkto'),
            'tab_id' => (int) $this->context->controller->id,
            'domain' => $domain,
            'widget_opts' => $widgetOpts,
            'page_id' => $pageId,
            'widget_id' => $widgetId,
            'same_user' => $sameUser,
        ];

        return parent::renderView();
    }

    /**
     * Base plugin URL
     *
     * @return string
     */
    private function getBaseUrl()
    {
        return 'https://plugins.tawk.to';
    }

    /**
     * Generates iframe URL
     *
     * @return string
     */
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
                . '/generic/widgets?currentPageId=' . $pageId
                . '&currentWidgetId=' . $widgetId;
    }

    /**
     * Validate page ID and widget ID
     *
     * @param string $pageId page ID
     * @param string $widgetId widget ID
     *
     * @return int|false
     */
    private static function idsAreCorrect(string $pageId, string $widgetId)
    {
        return preg_match('/^[0-9A-Fa-f]{24}$/', $pageId) === 1 && preg_match('/^[a-z0-9]{1,50}$/i', $widgetId) === 1;
    }

    /**
     * Save widget page ID and widget ID
     *
     * @return void
     */
    public function ajaxProcessSetWidget()
    {
        if (!Tools::getIsset('pageId') || !Tools::getIsset('widgetId')) {
            die(Tools::jsonEncode(['success' => false]));
        }

        $pageId = Tools::getValue('pageId');
        $widgetId = Tools::getValue('widgetId');
        if (!self::idsAreCorrect($pageId, $widgetId)) {
            die(Tools::jsonEncode(['success' => false]));
        }

        $currentWidgetKey = TawkTo::TAWKTO_SELECTED_WIDGET;
        Configuration::updateValue($currentWidgetKey, $pageId . ':' . $widgetId);

        $userKey = TawkTo::TAWKTO_WIDGET_USER;
        Configuration::updateValue($userKey, $this->context->employee->id);

        die(Tools::jsonEncode(['success' => true]));
    }

    /**
     * Remove widget page ID and widget ID
     *
     * @return void
     */
    public function ajaxProcessRemoveWidget()
    {
        $keys = [
            TawkTo::TAWKTO_SELECTED_WIDGET,
            TawkTo::TAWKTO_WIDGET_USER,
        ];

        foreach ($keys as $key) {
            if (Shop::getContext() == Shop::CONTEXT_ALL) {
                Configuration::updateValue($key, '');
            } else {
                // Configuration::deleteFromContext method cannot be used by
                // 'All Shops' or the current shop context is 'CONTEXT_ALL'.
                Configuration::deleteFromContext($key);
            }
        }

        die(Tools::jsonEncode(['success' => true]));
    }

    /**
     * Save visibility settings
     *
     * @return void
     */
    public function ajaxProcessSetOptions()
    {
        $key = TawkTo::TAWKTO_WIDGET_OPTS;
        $jsonOpts = [];

        try {
            // Process selected options
            $jsonOpts = $this->processSetOptions(Tools::getValue('options'));
        } catch (Exception $e) {
            if ($e instanceof TawktoException) {
                die(json_encode(['success' => false, 'message' => $e->getMessage()]));
            }

            die(json_encode(['success' => false, 'message' => 'An error occurred while saving options']));
        }

        // Override current options/fallback if not selected
        $currentOpts = Configuration::get($key);
        if (!empty($currentOpts)) {
            $currentOpts = json_decode($currentOpts, true);
            if (is_array($currentOpts)) {
                $jsonOpts = array_merge($currentOpts, $jsonOpts);
            }
        }

        if (!isset($jsonOpts['config_version'])) {
            $jsonOpts['config_version'] = 0;
        } else {
            ++$jsonOpts['config_version'];
        }

        Configuration::updateValue($key, json_encode($jsonOpts));

        die(json_encode(['success' => true]));
    }

    /**
     * Process options
     *
     * @param string $params Selected options
     *
     * @return array
     *
     * @throws TawktoException Error processing options
     */
    private function processSetOptions(string $params): array
    {
        // default options
        $jsonOpts = [
            'always_display' => false,

            // default value needs to be a json encoded of an empty array
            // since we're going to save a json encoded array later on.
            'hide_oncustom' => json_encode([]),

            'show_onfrontpage' => false,
            'show_oncategory' => false,
            'show_onproduct' => false,

            // default value needs to be a json encoded of an empty array
            // since we're going to save a json encoded array later on.
            'show_oncustom' => json_encode([]),

            'enable_visitor_recognition' => false,
            'js_api_key' => '',
        ];

        if (empty($params)) {
            return $jsonOpts;
        }

        parse_str($params, $options);
        foreach ($options as $column => $value) {
            switch ($column) {
                case 'hide_oncustom':
                case 'show_oncustom':
                    // replace newlines and returns with comma, and convert to array for saving
                    $value = urldecode($value);
                    $value = str_ireplace(["\r\n", "\r", "\n"], ',', $value);
                    if (!empty($value)) {
                        $value = explode(',', $value);
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

                case 'js_api_key':
                    if ($value === self::NO_CHANGE) {
                        unset($jsonOpts['js_api_key']);
                        break;
                    }

                    if ($value === '') {
                        break;
                    }

                    $value = trim($value);

                    if (strlen($value) !== 40) {
                        throw new TawktoException('Invalid API key.');
                    }

                    try {
                        $jsonOpts['js_api_key'] = $this->encryptData($value);
                    } catch (Exception $e) {
                        error_log($e->getMessage());

                        throw new TawktoException('Error saving Javascript API Key.');
                    }

                    break;
            }
        }

        return $jsonOpts;
    }

    /**
     * Encrypt data
     *
     * @param string $data Data to encrypt
     *
     * @return string Encrypted data
     *
     * @throws Exception error encrypting data
     */
    private function encryptData(string $data)
    {
        if (!defined('_COOKIE_KEY_')) {
            throw new Exception('Cookie key not defined');
        }

        try {
            $iv = random_bytes(16);
        } catch (Exception $e) {
            throw new Exception('Failed to generate IV');
        }

        $encrypted = openssl_encrypt($data, 'AES-256-CBC', _COOKIE_KEY_, 0, $iv);

        if ($encrypted === false) {
            throw new Exception('Failed to encrypt data');
        }

        $encrypted = base64_encode($iv . $encrypted);

        if ($encrypted === false) {
            throw new Exception('Failed to encode data');
        }

        return $encrypted;
    }
}
