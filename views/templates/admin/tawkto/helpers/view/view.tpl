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
?>


{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}

<iframe
	id="tawkIframe"
	src=""
	style="min-height: 400px; width : 100%; border: none; margin-top: 20px">
</iframe>

<script type="text/javascript">
	var currentHost = window.location.protocol + "//" + window.location.host,
		url = "{$iframe_url}&parentDomain=" + currentHost,
		baseUrl = '{$base_url}',
		current_id_tab = '{$tab_id}',
		controller = '{$controller}';

	{literal}
	jQuery('#tawkIframe').attr('src', url);

	var iframe = jQuery('#tawk_widget_customization')[0];

	window.addEventListener('message', function(e) {
		if(e.origin === baseUrl) {

			if(e.data.action === 'setWidget') {
				setWidget(e);
			}

			if(e.data.action === 'removeWidget') {
				removeWidget(e);
			}
		}
	});

	function setWidget(e) {

		$.ajax({
			type     : 'POST',
			url      : controller,
			dataType : 'json',
			data     : {
				controller : 'AdminTawkto',
				action     : 'setWidget',
				ajax       : true,
				id_tab     : current_id_tab,
				pageId     : e.data.pageId,
				widgetId   : e.data.widgetId
			},
			success : function(r) {
				if(r.success) {
					e.source.postMessage({action: 'setDone'} , baseUrl);
				} else {
					e.source.postMessage({action: 'setFail'} , baseUrl);
				}
			}
		});
	}

	function removeWidget(e) {
		$.ajax({
			type     : 'POST',
			url      : controller,
			dataType : 'json',
			data     : {
				controller : 'AdminTawkto',
				action     : 'removeWidget',
				ajax       : true,
				id_tab     : current_id_tab
			},
			success : function(r) {
				if(r.success) {
					e.source.postMessage({action: 'removeDone'} , baseUrl);
				} else {
					e.source.postMessage({action: 'removeFail'} , baseUrl);
				}
			}
		});
	}
	{/literal}
</script>