{*
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
 *}

{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}

{if !$same_user}
<div id="widget_already_set" style="width: 50%; float: left; color: #3c763d; border-color: #d6e9c6; font-weight: bold;" class="alert alert-warning">Widget set by other user</div>
{/if}

<style>
#module_form .radio {
    margin-top: 0;
    margin-bottom: 0;
}
</style>

<iframe
    id="tawkIframe"
    src=""
    style="min-height: 275px; width: 100%; border: none; margin: 5px 0; padding: 10px; background: #FFF;">
</iframe>
<input type="hidden" class="hidden" name="page_id" value="{$page_id|escape:'html':'UTF-8'}">
<input type="hidden" class="hidden" name="widget_id" value="{$widget_id|escape:'html':'UTF-8'}">
<script type="text/javascript">
    var domain = "{$domain|escape:'url':'UTF-8'}";;
    var currentHost = window.location.protocol + "//" + window.location.host,
        url = decodeURIComponent("{$iframe_url|cat:'&pltf=prestashop&pltfv=1.6&parentDomain='|escape:'url':'UTF-8'}") + currentHost,
        baseUrl = decodeURIComponent("{$base_url|escape:'url':'UTF-8'}"),
        current_id_tab = "{$tab_id|escape:'javascript':'UTF-8'}",
        controller = decodeURIComponent("{$controller|escape:'url':'UTF-8'}");

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

            if(e.data.action === 'reloadHeight') {
                reloadIframeHeight(e.data.height);
            }
        }
    });

    function setWidget(e) {
        $.ajax({
            type : 'POST',
            url : controller,
            dataType : 'json',
            data : {
                controller : 'AdminTawkto',
                action : 'setWidget',
                ajax : true,
                id_tab : current_id_tab,
                pageId : e.data.pageId,
                widgetId : e.data.widgetId,
                domain : domain
            },
            success : function(r) {
                $('input[name="page_id"]').val(e.data.pageId);
                $('input[name="widget_id"]').val(e.data.widgetId);
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
            type : 'POST',
            url : controller,
            dataType : 'json',
            data : {
                controller : 'AdminTawkto',
                action : 'removeWidget',
                ajax : true,
                id_tab : current_id_tab,
                domain : domain
            },
            success : function(r) {
                if(r.success) {
                    e.source.postMessage({action: 'removeDone'} , baseUrl);

                    $('input[name="page_id"]').val(e.data.pageId);
                    $('input[name="widget_id"]').val(e.data.widgetId);
                } else {
                    e.source.postMessage({action: 'removeFail'} , baseUrl);
                }
            }
        });
    }

    function reloadIframeHeight(height) {
        if (!height) {
            return;
        }

        var iframe = jQuery('#tawkIframe');
        if (height === iframe.height()) {
            return;
        }

        iframe.height(height);
    }
    {/literal}
</script>

<form id="module_form" action="" method="post">
    <div class="panel">
        <div class="panel-heading"> <i class="icon-eye-close"></i> Privacy Options </div>
        <div class="form-wrapper row">
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    <span data-toggle="tooltip" data-html="true"
                        title=""  data-original-title="Enable Visitor Recognition">
                        Enable Visitor Recognition
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="radio ">
                        <label>
                            <input type="checkbox" name="enable_visitor_recognition"
                                id="enable_visitor_recognition" value="1"
                                {if is_null($display_opts) || is_null($display_opts->enable_visitor_recognition) || $display_opts->enable_visitor_recognition}
                                    checked
                                {/if}
                            />
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel" id="fieldset_1">
        <div class="panel-heading"> <i class="icon-cogs"></i> Visibility Settings </div>
        <div class="form-wrapper row">
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    <span data-toggle="tooltip" data-html="true"
                        title=""  data-original-title="Select which pages that chat is displayed in the site(s)">
                        Always show Tawk.To widget on every page
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="radio ">
                        <label>
                            <input type="checkbox" name="always_display"
                                id="always_display" value="1"
                                {if is_null($display_opts) || $display_opts->always_display}
                                    checked
                                {/if}
                            />
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="control-label col-lg-3">
                    <span data-toggle="tooltip" data-html="true"
                        title="" >
                        Show on frontpage
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="radio ">
                        <label>
                            <input type="checkbox" name="show_onfrontpage"
                                id="show_onfrontpage" value="1"
                                {if !is_null($display_opts) && $display_opts->show_onfrontpage}
                                    checked
                                {/if}
                            />
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="control-label col-lg-3">
                    <span data-toggle="tooltip" data-html="true"
                        title="" >
                        Show on category pages
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="radio ">
                        <label>
                            <input type="checkbox" name="show_oncategory"
                                id="show_oncategory" value="1"
                                {if !is_null($display_opts) && $display_opts->show_oncategory}
                                    checked
                                {/if}
                            />
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="control-label col-lg-3">
                    <span data-toggle="tooltip" data-html="true"
                        title="" >
                        Show on product pages
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="radio ">
                        <label>
                            <input type="checkbox" name="show_onproduct"
                                id="show_onproduct" value="1"
                                {if !is_null($display_opts) && $display_opts->show_onproduct}
                                    checked
                                {/if}
                            />
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="control-label col-lg-3">
                    <span data-toggle="tooltip" data-html="true"
                        title="" >
                        Show on pages:
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="text">
                        <label>
                        {if (!is_null($display_opts) && !empty($display_opts->show_oncustom)) }
                            {$whitelist = json_decode($display_opts->show_oncustom)}
                            <textarea name="show_oncustom" id="show_oncustom" cols="30"rows="10">{foreach from=$whitelist item=page}{$page|escape:'htmlall':'UTF-8'|cat:"\r\n"}{/foreach}</textarea>
                        {else}
                            <textarea name="show_oncustom" id="show_oncustom" cols="30" rows="10"></textarea>
                        {/if}
                        </label>
                        <br>
                        Add URLs to pages in which you would like to show the widget.<br>
                        Put each URL in a new line.<br>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <div id="optionsSuccessMessage" style="width: 50%; float: left; background-color: #dff0d8; color: #3c763d; border-color: #d6e9c6; font-weight: bold; display: none;" class="alert alert-success">Successfully set widget options to your site</div>
            <button type="submit" value="1" id="module_form_submit_btn" name="submitBlockCategories" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> Save</button>
        </div>
    </div>
    <!-- end form-wrapper -->
</form>
<script type="text/javascript">
{literal}
jQuery(document).ready(function() {
    // process the form
    $('#module_form').submit(function(event) {
        $.ajax({
            type : 'POST',
            url : controller,
            dataType : 'json',
            data : {
                controller : 'AdminTawkto',
                action : 'setVisibility',
                ajax : true,
                id_tab : current_id_tab,
                pageId : $('input[name="page_id"]').val(),
                widgetId : $('input[name="widget_id"]').val(),
                domain : domain,
                options : $(this).serialize()
            },
            success : function(r) {
                if(r.success) {
                    $('#optionsSuccessMessage').toggle().delay(3000).fadeOut();
                }
            }
        });

        // stop the form from submitting the normal way and refreshing the page
        event.preventDefault();
    });

    if (jQuery("#always_display").prop("checked")) {
        jQuery('#show_onfrontpage').prop('disabled', true);
        jQuery('#show_oncategory').prop('disabled', true);
        jQuery('#show_onproduct').prop('disabled', true);
        jQuery('#show_oncustom').prop('disabled', true);
    }

    jQuery("#always_display").change(function() {
        if (this.checked) {
            jQuery('#show_onfrontpage').prop('disabled', true);
            jQuery('#show_oncategory').prop('disabled', true);
            jQuery('#show_onproduct').prop('disabled', true);
            jQuery('#show_oncustom').prop('disabled', true);
        } else {
            jQuery('#show_onfrontpage').prop('disabled', false);
            jQuery('#show_oncategory').prop('disabled', false);
            jQuery('#show_onproduct').prop('disabled', false);
            jQuery('#show_oncustom').prop('disabled', false);
        }
    });
});
{/literal}
</script>
