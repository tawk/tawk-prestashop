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
 * @copyright Copyright (c) 2014-2026 tawk.to
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'tawkto/vendor/autoload.php';

use Tawk\Modules\UrlPatternMatcher;

/**
 * tawk.to module
 */
class Tawkto extends Module
{
    public const TAWKTO_WIDGET_PAGE_ID = 'TAWKTO_WIDGET_PAGE_ID';
    public const TAWKTO_WIDGET_WIDGET_ID = 'TAWKTO_WIDGET_WIDGET_ID';
    public const TAWKTO_WIDGET_OPTS = 'TAWKTO_WIDGET_OPTS';
    public const TAWKTO_WIDGET_USER = 'TAWKTO_WIDGET_USER';
    public const TAWKTO_SELECTED_WIDGET = 'TAWKTO_SELECTED_WIDGET';
    public const TAWKTO_VISITOR_SESSION = 'TAWKTO_VISITOR_SESSION';

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->name = 'tawkto';
        $this->tab = 'front_office_features';
        $this->version = '1.6.0';
        $this->author = 'tawk.to';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.5', 'max' => '1.7'];

        parent::__construct();

        $this->displayName = $this->l('tawk.to');
        $this->description = $this->l('tawk.to live chat integration.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('MYMODULE_NAME')) {
            $this->warning = $this->l('No name provided');
        }
    }

    /**
     * Install module
     *
     * @return bool
     */
    public function install()
    {
        return parent::install() && $this->registerHook('displayFooter') && $this->installTab();
    }

    /**
     * Install tab
     *
     * @return bool
     */
    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminTawkto';
        $tab->name = [];

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'tawk.to';
        }

        $tab->id_parent = (int) Tab::getIdFromClassName('AdminAdmin');
        $tab->module = $this->name;

        return $tab->add();
    }

    /**
     * Hook to add widget on page
     *
     * @return mixed|string
     */
    public function hookDisplayFooter()
    {
        $current_widget = self::getPropertyAndWidget();
        if (empty($current_widget)) {
            return '';
        }

        $pageId = $current_widget['page_id'];
        $widgetId = $current_widget['widget_id'];

        $result = Configuration::get(self::TAWKTO_WIDGET_OPTS);
        // default values
        $enable_visitor_recognition = true;
        $js_api_key = '';
        $config_version = 0;

        if ($result) {
            $options = json_decode($result);
            $current_page = (string) $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            if (isset($options->enable_visitor_recognition)) {
                $enable_visitor_recognition = $options->enable_visitor_recognition;
            }

            if (isset($options->js_api_key)) {
                $js_api_key = $options->js_api_key;
            }

            if (isset($options->config_version)) {
                $config_version = $options->config_version;
            }

            // prepare visibility
            if (false == $options->always_display) {
                // show on specified urls
                $show_pages = $this->getArrayFromJson($options->show_oncustom);

                $show = false;
                if (UrlPatternMatcher::match($current_page, $show_pages)) {
                    $show = true;
                }

                if (!$show) {
                    if ('product' == $this->context->controller->php_self) {
                        if ($options->show_onproduct) {
                            $show = true;
                        }
                    }

                    if ('category' == $this->context->controller->php_self) {
                        if ($options->show_oncategory) {
                            $show = true;
                        }
                    }

                    if ('index' == $this->context->controller->php_self) {
                        if ($options->show_onfrontpage) {
                            $show = true;
                        }
                    }
                }

                if (!$show) {
                    return;
                }
            } else {
                // hide on specified urls
                $hide_pages = $this->getArrayFromJson($options->hide_oncustom);

                $show = true;
                if (UrlPatternMatcher::match($current_page, $hide_pages)) {
                    $show = false;
                }

                if (!$show) {
                    return;
                }
            }
        }

        // add customer details as visitor info
        $customer_name = '';
        $customer_email = '';
        $hash = null;
        if ($enable_visitor_recognition && !is_null($this->context->customer->id)) {
            $customer = $this->context->customer;
            $customer_name = $customer->firstname . ' ' . $customer->lastname;
            $customer_email = $customer->email;

            $hash = $this->getVisitorHash($customer_email, $js_api_key, $config_version);
        }

        $this->context->smarty->assign([
            'widget_id' => $widgetId,
            'page_id' => $pageId,
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'hash' => $hash,
        ]);

        return $this->display(__FILE__, 'widget.tpl');
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        if (!parent::uninstall() || !$this->uninstallTab()) {
            return false;
        }

        $keys = [
            self::TAWKTO_SELECTED_WIDGET,
            self::TAWKTO_WIDGET_OPTS,
            self::TAWKTO_WIDGET_USER,
        ];

        foreach ($keys as $key) {
            Configuration::deleteByName($key);
        }

        return true;
    }

    /**
     * Uninstall tab
     *
     * @return bool
     */
    public function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminTawkto');

        if ($id_tab) {
            $tab = new Tab($id_tab);

            return $tab->delete();
        } else {
            return false;
        }
    }

    /**
     * Redirect to tawk.to module
     *
     * @return void
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminTawkto'));
    }

    /**
     * Get property ID and widget ID
     *
     * @return array[string]string
     */
    public static function getPropertyAndWidget()
    {
        $current_widget = Configuration::get(self::TAWKTO_SELECTED_WIDGET);
        if (empty($current_widget)) {
            return null;
        }

        $current_widget = explode(':', $current_widget);
        if (count($current_widget) < 2) {
            // this means that something went wrong when saving the property and
            // widget.
            return null;
        }

        return [
            'page_id' => $current_widget[0],
            'widget_id' => $current_widget[1],
        ];
    }

    /**
     * Convert JSON to array
     *
     * @param string|string[] $data data
     *
     * @return string[]
     */
    private function getArrayFromJson($data)
    {
        $arr = [];
        if (is_string($data)) {
            $data = json_decode($data);
        }

        if (is_array($data)) {
            $arr = $data;
        }

        return $arr;
    }

    /**
     * Get visitor hash
     *
     * @param string $email Visitor email
     * @param string $js_api_key JS API key
     * @param int $config_version Config version
     *
     * @return string|null
     */
    private function getVisitorHash(string $email, string $js_api_key, int $config_version)
    {
        if (empty($js_api_key)) {
            return null;
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        if (isset($_SESSION[self::TAWKTO_VISITOR_SESSION])) {
            $current_session = $_SESSION[self::TAWKTO_VISITOR_SESSION];

            if (isset($current_session['hash'])
                && $current_session['email'] === $email
                && $current_session['config_version'] === $config_version) {
                return $current_session['hash'];
            }
        }

        try {
            $key = $this->getDecryptedData($js_api_key);
        } catch (Exception $e) {
            error_log($e->getMessage());

            return null;
        }

        $hash = hash_hmac('sha256', $email, $key);

        $_SESSION[self::TAWKTO_VISITOR_SESSION] = [
            'hash' => $hash,
            'email' => $email,
            'config_version' => $config_version,
        ];

        return $hash;
    }

    /**
     * Decrypt data
     *
     * @param string $data Data to decrypt
     *
     * @return string Decrypted data
     *
     * @throws Exception error decrypting data
     */
    private function getDecryptedData(string $data)
    {
        if (!defined('_COOKIE_KEY_')) {
            throw new Exception('Cookie key not defined');
        }

        $decoded = base64_decode($data);

        if ($decoded === false) {
            throw new Exception('Failed to decode data');
        }

        $iv = substr($decoded, 0, 16);
        $encrypted_data = substr($decoded, 16);

        $decrypted_data = openssl_decrypt($encrypted_data, 'AES-256-CBC', _COOKIE_KEY_, 0, $iv);

        if ($decrypted_data === false) {
            throw new Exception('Failed to decrypt data');
        }

        return $decrypted_data;
    }
}
