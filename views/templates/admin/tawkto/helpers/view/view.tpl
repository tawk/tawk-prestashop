<!--
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
 * @copyright   Copyright (c) 2014 tawk.to
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
-->
{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}

{if !$same_user}
<div id="widget_already_set" style="width: 50%; float: left; color: #3c763d; border-color: #d6e9c6; font-weight: bold;" class="alert alert-warning">Widget set by other user</div>
{/if}

<iframe
    id="tawkIframe"
    src=""
    style="min-height: 275px; width: 100%; border: none; margin: 5px 0; padding: 10px; background: #FFF;">
</iframe>
<input type="hidden" class="hidden" name="page_id" value="{$page_id}">
<input type="hidden" class="hidden" name="widget_id" value="{$widget_id}">
<script>
var domain = '{$domain}';
var currentHost = window.location.protocol + "//" + domain,
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
            widgetId   : e.data.widgetId,
            domain     : domain
        },
        success : function(r) {
            if(r.success) {
                e.source.postMessage({action: 'setDone'} , baseUrl);

                $('input[name="page_id"]').val(e.data.pageId);
                $('input[name="widget_id"]').val(e.data.widgetId);
                $('#module_form, .visibility_warning').toggle();
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
            id_tab     : current_id_tab,
            domain     : domain
        },
        success : function(r) {
            if(r.success) {
                e.source.postMessage({action: 'removeDone'} , baseUrl);

                $('input[name="page_id"]').val(e.data.pageId);
                $('input[name="widget_id"]').val(e.data.widgetId);
                $('#module_form, .visibility_warning').toggle();
            } else {
                e.source.postMessage({action: 'removeFail'} , baseUrl);
            }
        }
    });
}
{/literal}
</script>

<div style="float: left; color: #3c763d; border-color: #d6e9c6; font-weight: bold;{if $page_id && $widget_id}display:none;{/if}" class="alert alert-warning visibility_warning">Please set the chat widget using the form above, to enable the chat visibility options.</div>

<form id="module_form" action="" method="post">
    <div class="panel" id="fieldset_1">
        <div class="panel-heading"> <i class="icon-cogs"></i> Visibility Settings </div>
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
                                {(is_null($display_opts)||$display_opts->always_display)?'checked':''} />
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
                    {if (!is_null($display_opts) && !empty($display_opts->hide_oncustom)) }
                    {$whitelist = json_decode($display_opts->hide_oncustom)}
                    <textarea class="hide_specific" name="hide_oncustom" id="hide_oncustom" cols="30" 
                        rows="10">{foreach from=$whitelist item=page}{$page}{"\r\n"}{/foreach}</textarea>
                    {else}
                        <textarea class="hide_specific" name="hide_oncustom" id="hide_oncustom" cols="30" rows="10"></textarea>
                    {/if}
                    </label>
                    <br>
                    <p style="text-align: justify;">
                    Add URLs to pages in which you would like to hide the widget. ( if "always show" is checked )<br>
                    Put each URL in a new line.
                    </p>
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
                                {(!is_null($display_opts) && $display_opts->show_onfrontpage)?'checked':''} />
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
                                {(!is_null($display_opts) && $display_opts->show_oncategory)?'checked':''} />
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
                                {(!is_null($display_opts) && $display_opts->show_onproduct)?'checked':''} />
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
                        <textarea class="show_specific" name="show_oncustom" id="show_oncustom" cols="30" 
                            rows="10">{foreach from=$whitelist item=page}{$page}{"\r\n"}{/foreach}</textarea>
                        {else}
                            <textarea class="show_specific" name="show_oncustom" id="show_oncustom" cols="30" rows="10"></textarea>
                        {/if}
                        </label>
                        <br>
                        Add URLs to pages in which you would like to show the widget.<br>
                        Put each URL in a new line.<br>
                    </div>
                </div>
            </div>

        </div><!-- end form-wrapper -->
        <div class="panel-footer">
            <div id="optionsSuccessMessage" style="width: 50%; float: left; background-color: #dff0d8; color: #3c763d; border-color: #d6e9c6; font-weight: bold; display: none;" class="alert alert-success">Successfully set widget options to your site</div>
            <button type="submit" value="1" id="module_form_submit_btn" name="submitBlockCategories" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> Save</button>
        </div>
    </div>
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
        type     : 'POST',
        url      : controller,
        dataType : 'json',
        dataType : 'json',
        data     : {
            controller : 'AdminTawkto',
            action     : 'setVisibility',
            ajax       : true,
            id_tab     : current_id_tab,
            pageId     : $('input[name="page_id"]').val(),
            widgetId   : $('input[name="widget_id"]').val(),
            domain     : domain,
            options    : $(this).serialize()
        },
        success : function(r) {
            if(r.success) {
                $('#optionsSuccessMessage').toggle().delay(3000).fadeOut();
            } else {
            }
        }
    });

    // stop the form from submitting the normal way and refreshing the page
    event.preventDefault();
});

if(jQuery("#always_display").prop("checked")){
    jQuery('.show_specific').prop('disabled', true);
} else {
    jQuery('.hide_specific').prop('disabled', true);
}

jQuery("#always_display").change(function() {
    if(this.checked){
        jQuery('.hide_specific').prop('disabled', false);
        jQuery('.show_specific').prop('disabled', true);
    }else{
        jQuery('.hide_specific').prop('disabled', true);
        jQuery('.show_specific').prop('disabled', false);
    }
}); 
{/literal}
});

</script>

