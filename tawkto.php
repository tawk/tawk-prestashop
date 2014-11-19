<?php

/**
 * Tawk.to
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
 * @copyright   Copyright (c) 2014 Tawk.to
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

if (!defined('_PS_VERSION_')) {
	exit;
}

class TawkTo extends Module {
	const TAWKTO_WIDGET_PAGE_ID = 'TAWKTO_WIDGET_PAGE_ID';
	const TAWKTO_WIDGET_WIDGET_ID = 'TAWKTO_WIDGET_WIDGET_ID';

	public function __construct() {
		$this->name = 'tawkto';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'tawk.to';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
		$this->dependencies = array('blockcart');

		parent::__construct();

		$this->displayName = $this->l('Tawk.to');
		$this->description = $this->l('Tawk.to live chat integration.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if (!Configuration::get('MYMODULE_NAME'))
		$this->warning = $this->l('No name provided');
	}

	public function install() {
		return parent::install() && $this->registerHook('footer') && $this->installTab();
	}

	private function installTab() {
		$tab = new Tab();
		$tab->active = 1;
		$tab->class_name = 'AdminTawkto';
		$tab->name = array();

		foreach (Language::getLanguages(true) as $lang) {
			$tab->name[$lang['id_lang']] = 'Tawk.to';
		}

		$tab->id_parent = (int)Tab::getIdFromClassName('AdminAdmin');
		$tab->module = $this->name;

		return $tab->add();
	}

	public function hookDisplayFooter() {
		$pageId = Configuration::get(self::TAWKTO_WIDGET_PAGE_ID);
		$widgetId = Configuration::get(self::TAWKTO_WIDGET_WIDGET_ID);

		if(empty($pageId) || empty($widgetId)) {
			return '';
		}

		$this->context->smarty->assign(array(
			'widget_id' => $widgetId,
			'page_id'   => $pageId
		));

		return $this->display(__FILE__, 'widget.tpl');
	}

	public function uninstall() {
		Configuration::deleteByName(self::TAWKTO_WIDGET_PAGE_ID);
		Configuration::deleteByName(self::TAWKTO_WIDGET_WIDGET_ID);

		return parent::uninstall() && $this->uninstallTab();
	}

	public function uninstallTab() {
		$id_tab = (int)Tab::getIdFromClassName('AdminTawkto');

		if ($id_tab) {
			$tab = new Tab($id_tab);
			return $tab->delete();
		} else {
			return false;
		}
	}

	public function getContent() {
		Tools::redirectAdmin($this->context->link->getAdminLink('AdminTawkto'));
	}
}