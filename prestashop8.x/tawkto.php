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

class Tawkto extends Module
{
    const TAWKTO_WIDGET_PAGE_ID = 'TAWKTO_WIDGET_PAGE_ID';
    const TAWKTO_WIDGET_WIDGET_ID = 'TAWKTO_WIDGET_WIDGET_ID';
    const TAWKTO_WIDGET_OPTS = 'TAWKTO_WIDGET_OPTS';
    const TAWKTO_WIDGET_USER = 'TAWKTO_WIDGET_USER';
    const TAWKTO_SELECTED_WIDGET = 'TAWKTO_SELECTED_WIDGET';

    public function __construct()
    {
        $this->name = 'tawkto';
        $this->tab = 'front_office_features';
        $this->version = '1.2.3';
        $this->author = 'tawk.to';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.7');

        parent::__construct();

        $this->displayName = $this->l('tawk.to');
        $this->description = $this->l('tawk.to live chat integration.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('MYMODULE_NAME')) {
            $this->warning = $this->l('No name provided');
        }
    }

    public function install()
    {
        return parent::install() && $this->registerHook('displayFooter') && $this->installTab();
    }

    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminTawkto';
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'tawk.to';
        }

        $tab->id_parent = (int) Tab::getIdFromClassName('AdminAdmin');
        $tab->module = $this->name;

        return $tab->add();
    }

    public function hookDisplayFooter()
    {
        $current_widget = self::getPropertyAndWidget();
        if (empty($current_widget)) {
            return '';
        }

        $pageId = $current_widget['page_id'];
        $widgetId = $current_widget['widget_id'];

        $result = Configuration::get(self::TAWKTO_WIDGET_OPTS);
        $enable_visitor_recognition = true; // default value
        if ($result) {
            $options = json_decode($result);
            $current_page = (string) $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

            if (isset($options->enable_visitor_recognition)) {
                $enable_visitor_recognition = $options->enable_visitor_recognition;
            }

            // prepare visibility
            if (false==$options->always_display) {
                // show on specified urls
                $show_pages = $this->getArrayFromJson($options->show_oncustom);

                $show = false;
                foreach ($show_pages as $slug) {
                    if (!empty($slug)) {
                        $slug = str_ireplace(array('http://','https://'), '', $slug);
                        if (stripos($current_page, $slug)!==false) {
                            $show = true;
                            break;
                        }
                    }
                }

                if (!$show) {
                    if ('product' == $this->context->controller->php_self) {
                        if (false==$options->show_onproduct) {
                            return;
                        }
                    }

                    if ('category' == $this->context->controller->php_self) {
                        if (false==$options->show_oncategory) {
                            return;
                        }
                    }

                    if ('index' == $this->context->controller->php_self) {
                        if (false==$options->show_onfrontpage) {
                            return;
                        }
                    }
                }

                if (!$show && !in_array($this->context->controller->php_self, array('index', 'category', 'product'))) {
                    return;
                }
            } else {
                // hide on specified urls
                $hide_pages = $this->getArrayFromJson($options->hide_oncustom);

                $show = true;
                foreach ($hide_pages as $slug) {
                    // we need to add htmlspecialchars due to slashes added when saving to database
                    $slug = (string) htmlspecialchars($slug);
                    if (!empty($slug)) {
                        $slug = str_ireplace(array('http://','https://'), '', $slug);
                        if (stripos($current_page, $slug)!==false) {
                            $show = false;
                            break;
                        }
                    }
                }

                if (!$show) {
                    return;
                }
            }
        }

        // add customer details as visitor info
        $customer_name = null;
        $customer_email = null;
        if ($enable_visitor_recognition && !is_null($this->context->customer->id)) {
            $customer = $this->context->customer;
            $customer_name = $customer->firstname.' '.$customer->lastname;
            $customer_email = $customer->email;
        }

        $this->context->smarty->assign(array(
            'widget_id' => $widgetId,
            'page_id' => $pageId,
            'customer_name' => (!is_null($customer_name)) ? $customer_name : '',
            'customer_email' => (!is_null($customer_email)) ? $customer_email : '',
        ));

        return $this->display(__FILE__, 'widget.tpl');
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !$this->uninstallTab()) {
            return false;
        }

        $keys = array(
            self::TAWKTO_SELECTED_WIDGET,
            self::TAWKTO_WIDGET_OPTS,
            self::TAWKTO_WIDGET_USER
        );

        foreach ($keys as $key) {
            Configuration::deleteByName($key);
        }

        return true;
    }

    public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminTawkto');

        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        } else {
            return false;
        }
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminTawkto'));
    }

    public function getPropertyAndWidget()
    {
        $current_widget = Configuration::get(self::TAWKTO_SELECTED_WIDGET);
        if (empty($current_widget)) {
            return null;
        }

        $current_widget = explode(':', $current_widget);
        if (count($current_widget) < 2) {
            // this means that something went wrong when saving the property and widget.
            return null;
        }

        return array(
            'page_id' => $current_widget[0],
            'widget_id' => $current_widget[1]
        );
    }

    private function getArrayFromJson($data)
    {
        $arr = array();
        if (is_string($data)) {
            $data = json_decode($data);
        }

        if (is_array($data)) {
            $arr = $data;
        }

        return $arr;
    }
}
