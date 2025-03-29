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
 * @copyright Copyright (c) 2014-2024 tawk.to
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
/* Tooltip */
.tawk-tooltip {
  position: relative;
  display: inline;
  color: #03a84e;
}

.tawk-tooltip .tawk-tooltiptext {
  visibility: hidden;
  background-color: #545454;
  color: #fff;
  padding: 0.5rem;
  width: 300px;
  border-radius: 0.5rem;
  font-size: 0.8rem;
  line-height: 1;
  list-style-type: none;

  /* Position the tooltip text - see examples below! */
  position: absolute;
  z-index: 1000;
  top: 12px;
}

.tawk-tooltip .tawk-tooltiptext::before {
  content: "";
  display: block;
  width: 0;
  height: 0;
  position: absolute;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-bottom: 5px solid #545454;
  top: -5px;
  left: 5px;
}

.tawk-tooltip:hover .tawk-tooltiptext {
  visibility: visible;
}

.options-alert {
    width: 50%;
    float: left;
    font-weight: bold;
    display: none;
}
</style>

<iframe
    id="tawkIframe"
    src=""
    style="min-height: 275px; width: 100%; border: none; margin: 5px 0; padding: 10px; background: #FFF;">
</iframe>
<input type="hidden" class="hidden" name="page_id" value="{$page_id|escape:'html':'UTF-8'}">
<input type="hidden" class="hidden" name="widget_id" value="{$widget_id|escape:'html':'UTF-8'}">
<script>
    var domain = "{$domain|escape:'url':'UTF-8'}";;
    var currentHost = window.location.protocol + "//" + window.location.host,
        url = decodeURIComponent("{$iframe_url|cat:'&pltf=prestashop&pltfv='|cat:$prestashop_version|cat:'&parentDomain='|escape:'url':'UTF-8'}") + currentHost,
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
                if(r.success) {
                    e.source.postMessage({action: 'setDone'} , baseUrl);

                    $('input[name="page_id"]').val(e.data.pageId);
                    $('input[name="widget_id"]').val(e.data.widgetId);
                    $('.visibility_warning').hide();
                    $('#module_form').show();
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
                    $('.visibility_warning').show();
                    $('#module_form').hide();
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

<div style="float: left; color: #3c763d; border-color: #d6e9c6; font-weight: bold;{if $page_id && $widget_id}display:none;{/if}" class="alert alert-warning visibility_warning">Please set the chat widget using the form above, to enable the chat visibility options.</div>

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
                                {if is_null($widget_opts) || is_null($widget_opts->enable_visitor_recognition) || $widget_opts->enable_visitor_recognition}
                                    checked
                                {/if}
                            />
                        </label>
                    </div>
                </div>
            </div>
            <p class='tawk-notice'>
                Note: If Secure Mode is enabled on your property, please enter your Javascript API Key to ensure visitor recognition works correctly.
            </p>
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    <span data-toggle="tooltip" data-html="true"
                        title=""  data-original-title="Javascript API Key">
                        Javascript API Key
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="radio ">
                        <label>
                            <input type="password" name="js_api_key" id="js_api_key"
                                value="{if !is_null($widget_opts) && $widget_opts->js_api_key}{$widget_opts->js_api_key|escape:'html':'UTF-8'}{/if}"
                            />
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel" id="fieldset_1">
        <div class="panel-heading"> <i class="icon-cogs"></i> Visibility Options </div>
        <div class="form-wrapper row">
            <div class="form-group row">
                <label class="control-label col-lg-3" for="always_display">
                    <span data-toggle="tooltip" data-html="true"
                        title="" data-original-title="Select which pages that chat is displayed in the site(s)">
                        Always show tawk.to widget on every page
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="radio ">
                        <label>
                            <input type="checkbox" name="always_display"
                                id="always_display" value="1"
                                {if is_null($widget_opts) || $widget_opts->always_display}
                                    checked
                                {/if}
                            />
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group col-lg-12">
                <label class="control-label col-lg-3" for="hide_oncustom">
                    <span data-toggle="tooltip" data-html="true"
                        title=""  data-original-title="Select which pages that chat is not displayed">
                        Except on pages:
                    </span>
                </label>
                <div class="col-lg-6 control-label">
                    <label>
                    {if (!is_null($widget_opts) && !empty($widget_opts->hide_oncustom)) }
                        {$whitelist = json_decode($widget_opts->hide_oncustom)}
                        <textarea class="hide_specific" name="hide_oncustom" id="hide_oncustom" cols="30" rows="10">{foreach from=$whitelist item=page}{$page|escape:'htmlall':'UTF-8'|cat:"\r\n"}{/foreach}</textarea>
                    {else}
                        <textarea class="hide_specific" name="hide_oncustom" id="hide_oncustom" cols="30" rows="10"></textarea>
                    {/if}
                    </label>
                    <br>
                    <div style="text-align: justify;">
                        Add URLs/paths to pages in which you would like to hide the widget. ( if "always show" is checked )<br>
                        Put each URL/path in a new line. Paths should have a leading '/'.
                        <br>
                        <div class="tawk-tooltip">
                            Examples of accepted path patterns
                            <ul class="tawk-tooltiptext">
                            <li>*</li>
                            <li>*/to/somewhere</li>
                            <li>/*/to/somewhere</li>
                            <li>/path/*/somewhere</li>
                            <li>/path/*/lead/*/somewhere</li>
                            <li>/path/*/*/somewhere</li>
                            <li>/path/to/*</li>
                            <li>/path/to/*/</li>
                            <li>*/to/*/page</li>
                            <li>/*/to/*/page</li>
                            <li>/path/*/other/*</li>
                            <li>/path/*/other/*/</li>
                            <li>http://www.example.com/</li>
                            <li>http://www.example.com/*</li>
                            <li>http://www.example.com/*/to/somewhere</li>
                            <li>http://www.example.com/path/*/somewhere</li>
                            <li>http://www.example.com/path/*/lead/*/somewhere</li>
                            <li>http://www.example.com/path/*/*/somewhere</li>
                            <li>http://www.example.com/path/to/*</li>
                            <li>http://www.example.com/path/to/*/</li>
                            <li>http://www.example.com/*/to/*/page</li>
                            <li>http://www.example.com/path/*/other/*</li>
                            <li>http://www.example.com/path/*/other/*/</li>
                            </ul>
                        </div>
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
                            <input class="show_specific" type="checkbox" name="show_onfrontpage"
                                id="show_onfrontpage" value="1"
                                {if !is_null($widget_opts) && $widget_opts->show_onfrontpage}
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
                            <input class="show_specific" type="checkbox" name="show_oncategory"
                                id="show_oncategory" value="1"
                                {if !is_null($widget_opts) && $widget_opts->show_oncategory}
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
                            <input class="show_specific" type="checkbox" name="show_onproduct"
                                id="show_onproduct" value="1"
                                {if !is_null($widget_opts) && $widget_opts->show_onproduct}
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
                        {if (!is_null($widget_opts) && !empty($widget_opts->show_oncustom)) }
                            {$whitelist = json_decode($widget_opts->show_oncustom)}
                            <textarea class="show_specific" name="show_oncustom" id="show_oncustom" cols="30" rows="10">{foreach from=$whitelist item=page}{$page|escape:'htmlall':'UTF-8'|cat:"\r\n"}{/foreach}</textarea>
                        {else}
                            <textarea class="show_specific" name="show_oncustom" id="show_oncustom" cols="30" rows="10"></textarea>
                        {/if}
                        </label>
                        <br>
                        Add URLs/paths to pages in which you would like to show the widget.<br>
                        Put each URL/path in a new line. Paths should have a leading '/'.
                        <br>
                        <div class="tawk-tooltip">
                            Examples of accepted path patterns
                            <ul class="tawk-tooltiptext">
                            <li>*</li>
                            <li>*/to/somewhere</li>
                            <li>/*/to/somewhere</li>
                            <li>/path/*/somewhere</li>
                            <li>/path/*/lead/*/somewhere</li>
                            <li>/path/*/*/somewhere</li>
                            <li>/path/to/*</li>
                            <li>/path/to/*/</li>
                            <li>*/to/*/page</li>
                            <li>/*/to/*/page</li>
                            <li>/path/*/other/*</li>
                            <li>/path/*/other/*/</li>
                            <li>http://www.example.com/</li>
                            <li>http://www.example.com/*</li>
                            <li>http://www.example.com/*/to/somewhere</li>
                            <li>http://www.example.com/path/*/somewhere</li>
                            <li>http://www.example.com/path/*/lead/*/somewhere</li>
                            <li>http://www.example.com/path/*/*/somewhere</li>
                            <li>http://www.example.com/path/to/*</li>
                            <li>http://www.example.com/path/to/*/</li>
                            <li>http://www.example.com/*/to/*/page</li>
                            <li>http://www.example.com/path/*/other/*</li>
                            <li>http://www.example.com/path/*/other/*/</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <div id="optionsSuccessMessage" style="background-color: #dff0d8; color: #3c763d; border-color: #d6e9c6;" class="alert alert-success options-alert">Successfully set widget options to your site</div>
            <div id="optionsFailureMessage" class="alert alert-danger options-alert"></div>
            <button type="submit" value="1" id="module_form_submit_btn" name="submitBlockCategories" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> Save</button>
        </div>
    </div><!-- end form-wrapper -->
</form>
<script>

jQuery(document).ready(function() {
    {if !$page_id || !$widget_id}
        $('#module_form').hide();
    {/if}
    {literal}
    // process the form
    $('#module_form').submit(function(event) {
        $.ajax({
            type : 'POST',
            url : controller,
            dataType : 'json',
            data : {
                controller : 'AdminTawkto',
                action : 'setOptions',
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
                } else {
                    $('#optionsFailureMessage').text(r.message).toggle().delay(3000).fadeOut();
                }
            }
        });

        // stop the form from submitting the normal way and refreshing the page
        event.preventDefault();
    });

    if (jQuery("#always_display").prop("checked")) {
        jQuery('.show_specific').prop('disabled', true);
    } else {
        jQuery('.hide_specific').prop('disabled', true);
    }

    jQuery("#always_display").change(function() {
        if (this.checked) {
            jQuery('.hide_specific').prop('disabled', false);
            jQuery('.show_specific').prop('disabled', true);
        } else {
            jQuery('.hide_specific').prop('disabled', true);
            jQuery('.show_specific').prop('disabled', false);
        }
    });
    {/literal}
});
</script>

