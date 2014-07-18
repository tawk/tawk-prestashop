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

class AdminTawktoController extends ModuleAdminController {
	public function __construct()
	{
		$this->bootstrap = true;
		$this->display = 'view';
		$this->meta_title = $this->l('Tawk.to');

		parent::__construct();
		if (!$this->module->active)
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
	}

	public function initToolBarTitle() {
		$this->toolbar_title[] = $this->l('Tawk.to');
		$this->toolbar_title[] = $this->l('Widget');
	}

	public function initToolbar() {
		$r = parent::initToolbar();

		if(isset($this->toolbar_btn)) {
			unset($this->toolbar_btn['back']);
		} else {
			unset($this->page_header_toolbar_btn['back']);
		}

		return $r;
	}

	public function renderView() {

		$this->tpl_view_vars = array(
			'iframe_url' => $this->getIframeUrl(),
			'base_url'   => $this->getBaseUrl(),
			'controller' => $this->context->link->getAdminLink('AdminTawkto'),
			'tab_id'     => (int)$this->context->controller->id
		);

		return parent::renderView();
	}

	private function getBaseUrl() {
		return 'https://plugins.tawk.to';
	}

	private function getIframeUrl() {
		return $this->getBaseUrl()
			.'/generic/widgets'
			.'?currentPageId='.Configuration::get(TawkTo::TAWKTO_WIDGET_PAGE_ID)
			.'&currentWidgetId='.Configuration::get(TawkTo::TAWKTO_WIDGET_WIDGET_ID);
	}

	private static function idsAreCorrect($pageId, $widgetId) {
		return preg_match('/^[0-9A-Fa-f]{24}$/', $pageId) === 1 && preg_match('/^[a-z0-9]{1,50}$/i', $widgetId) === 1;
	}

	public function ajaxProcessSetWidget() {
		if(!isset($_POST['pageId']) || !isset($_POST['widgetId']) || !self::idsAreCorrect($_POST['pageId'], $_POST['widgetId'])) {
			die(Tools::jsonEncode(array('success' => false)));
		}

		Configuration::updateValue(TawkTo::TAWKTO_WIDGET_PAGE_ID, $_POST['pageId']);
		Configuration::updateValue(TawkTo::TAWKTO_WIDGET_WIDGET_ID, $_POST['widgetId']);

		die(Tools::jsonEncode(array('success' => true)));
	}

	public function ajaxProcessRemoveWidget() {
		Configuration::deleteByName(TawkTo::TAWKTO_WIDGET_PAGE_ID);
		Configuration::deleteByName(TawkTo::TAWKTO_WIDGET_WIDGET_ID);

		die(Tools::jsonEncode(array('success' => true)));
	}
}