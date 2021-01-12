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

{* Select store section *}
<div class="panel" id="fieldset_1">
    <div class="panel-heading"> <i class="icon-shopping-cart"></i> Stores </div>
    <div class="form-wrapper row">
        <div class="form-group row">
            <select id="stores" class="form-control">
            {foreach $shops as $shop}
                <option value={$shop['id']}>{$shop['name']}</option>
            {/foreach}
            </select>
        </div>
    </div>
</div>

{* Select property and widget section *}
<div class="panel" id="fieldset_2">
    <div class="panel-heading"> <i class="icon-cogs"></i> Configure Widget</div>
    <div class="form-wrapper row">
        <div class="form-group row">
            <div id="widget_already_set" class="alert alert-warning" style="display: none">
                Widget set by other user
            </div>
            <div id="widget_error" class="alert alert-danger" style="display: none"></div>
            <iframe
                id="tawkIframe"
                src=""
                style="min-height: 275px; width: 100%; border: none; margin: 5px 0; padding: 10px; background: #FFF;">
            </iframe>
        </div>
    </div>
</div>

<div class="panel" id="fieldset_3">
    <div class="panel-heading"> <i class="icon-eye-open"></i> Visibility Settings </div>
    <div id="visibility_warning" class="alert alert-warning">Please set the chat widget using the form above, to enable the chat visibility options.</div>
    <div id="visibility_error" class="alert alert-danger" style="display: none;"></div>
    <form id="module_form" action="" method="post">
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
                            <input type="checkbox" name="always_display" id="always_display" value="1" />
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="control-label col-lg-3" for="hide_oncustom">
                    <span data-toggle="tooltip" data-html="true"
                        title=""  data-original-title="Select which pages that chat is not displayed">
                        Except on pages:
                    </span>
                </label>
                <div class="col-lg-6 control-label">
                    <label>
                        <textarea class="hide_specific" name="hide_oncustom" id="hide_oncustom" cols="30" rows="10"></textarea>
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
                            <input class="show_specific" type="checkbox" name="show_onfrontpage" id="show_onfrontpage" value="1" />
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
                            <input class="show_specific" type="checkbox" name="show_oncategory" id="show_oncategory" value="1" />
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
                            <input class="show_specific" type="checkbox" name="show_onproduct" id="show_onproduct" value="1" />
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
                            <textarea class="show_specific" name="show_oncustom" id="show_oncustom" cols="30" rows="10"></textarea>
                        </label>
                        <br>
                        Add URLs to pages in which you would like to show the widget.<br>
                        Put each URL in a new line.<br>
                    </div>
                </div>
            </div>

        </div><!-- end form-wrapper -->
        <div class="panel-footer">
            <div id="optionsSuccessMessage" class="alert alert-success">Successfully set widget options to your site</div>
            <button type="submit" value="1" id="module_form_submit_btn" name="submitBlockCategories" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> Save</button>
        </div>
    </form>
</div>

<script type="text/javascript">
    var currentHost = window.location.protocol + "//" + window.location.host;
    var url = "{$iframe_url}&parentDomain=" + currentHost;
    var baseUrl = '{$base_url}';
    var current_id_tab = '{$tab_id}';
    var controller = '{$controller}';
    var shops = JSON.parse('{$shops|@json_encode}');
    var shopId, domain;

    jQuery(document).ready(function() {
        shopId = jQuery('#stores').val();
        domain = shops[shopId].domain;
        setup();

        jQuery('#stores').change(function (e) {
            shopId = e.target.value;
            domain = shops[shopId].domain;
            getStoreWidget();
            getStoreVisibilityOpts();
            toggleVisibilityForm();
        });

        $('#module_form').submit(setVisibility);

        jQuery("#always_display").change(function() {
            if (this.checked) {
                jQuery('.hide_specific').prop('disabled', false);
                jQuery('.show_specific').prop('disabled', true);
            } else {
                jQuery('.hide_specific').prop('disabled', true);
                jQuery('.show_specific').prop('disabled', false);
            }
        });

    });

    {literal}
    // Event Listeners
    window.addEventListener('message', function(e) {
        if (e.origin === baseUrl) {

            if (e.data.action === 'setWidget') {
                setWidget(e);
            }

            if (e.data.action === 'removeWidget') {
                removeWidget(e);
            }
        }
    });

    // Functions
    function setup() {
        getStoreWidget();
        getStoreVisibilityOpts();

        $('#module_form').hide();
    }

    function getStoreWidget() {
        var errEl = $('#widget_error');
        errEl.hide();
        var payload = {
            controller : 'AdminTawkto',
            action : 'getStoreWidget',
            ajax : true,
            shopId : parseInt(shopId)
        };
        $.get(controller, payload)
            .success(function (data) {
                var result = JSON.parse(data);
                var updatedUrl = new URL(url);
                updatedUrl.searchParams.set('currentWidgetId', result.widgetId || '');
                updatedUrl.searchParams.set('currentPageId', result.pageId || '');

                url = updatedUrl.href;
                jQuery('#tawkIframe').attr('src', url);

                toggleSameUserWarning(result.sameUser);
                toggleVisibilityForm(result.widgetId, result.pageId);
            })
            .error(function (xhr, status, err) {
                errEl.html('Failed to retrieve current store\'s widget.');
                errEl.show();
            });
    }

    function getStoreVisibilityOpts() {
        var errEl = $('#visibility_error');
        errEl.hide();

        var payload = {
            controller : 'AdminTawkto',
            action : 'getStoreVisibilityOpts',
            ajax : true,
            shopId : parseInt(shopId)
        };
        $.get(controller, payload)
            .success(function (data) {
                var result = JSON.parse(data);

                if (!result) {
                    setDefaultVisibilityFields();
                } else {
                    // bind values to specified elements
                    for (var [key, value] of Object.entries(result)) {
                        var el = $('#' + key);
                        var tag = el.prop('tagName');
                        var type = el.prop('type');

                        if (tag === 'INPUT' && (type === 'checkbox' || type === 'radio')) {
                            el.prop('checked', value);
                        } else if (tag === 'TEXTAREA') {
                            if (typeof value === 'string') {
                                value = JSON.parse(value);
                            }
                            el.val(value.join('\r\n'));
                        } else {
                            el.val(value);
                        }

                        // trigers change event
                        el.trigger('change');
                    }
                }

            })
            .error(function (xhr, status, err) {
                errEl.html('Failed to retrieve visibility settings.');
                errEl.show();
            });
    }

    function setWidget(e) {
        $.ajax({
            type : 'POST',
            url : controller,
            dataType : 'json',
            data : {
                controller : 'AdminTawkto',
                action     : 'setWidget',
                ajax       : true,
                id_tab     : current_id_tab,
                pageId     : e.data.pageId,
                widgetId   : e.data.widgetId,
                domain     : domain,
                shopId     : parseInt(shopId)
            },
            success : function(r) {
                if (r.success) {
                    e.source.postMessage({action: 'setDone'} , baseUrl);
                    toggleVisibilityForm(e.data.pageId, e.data.widgetId);
                    toggleSameUserWarning(true);
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
                action     : 'removeWidget',
                ajax       : true,
                id_tab     : current_id_tab,
                domain     : domain,
                shopId     : parseInt(shopId)
            },
            success : function(r) {
                if (r.success) {
                    e.source.postMessage({action: 'removeDone'} , baseUrl);
                    toggleVisibilityForm();
                } else {
                    e.source.postMessage({action: 'removeFail'} , baseUrl);
                }
            }
        });
    }

    function setVisibility(e) {
        $.ajax({
            type : 'POST',
            url : controller,
            dataType : 'json',
            data : {
                controller : 'AdminTawkto',
                action : 'setVisibility',
                ajax : true,
                id_tab : current_id_tab,
                domain : domain,
                options : $(this).serialize(),
                shopId : parseInt(shopId)
            },
            success : function(r) {
                if(r.success) {
                    $('#optionsSuccessMessage').toggle().delay(3000).fadeOut();
                }
            }
        });

        // stop the form from submitting the normal way and refreshing the page
        event.preventDefault();
    }

    function toggleVisibilityForm(pageId, widgetId) {
        if (pageId && widgetId) {
            $('#visibility_warning').hide();
            $('#module_form').show();
        } else {
            $('#visibility_warning').show();
            $('#module_form').hide();
        }
    }

    function toggleSameUserWarning(sameUser) {
        if (sameUser) {
            $('#widget_already_set').hide();
        } else {
            $('#widget_already_set').show();
        }
    }

    function setDefaultVisibilityFields() {
        var alwaysDisplayEl = $('#always_display');
        alwaysDisplayEl.prop('checked', true);
        alwaysDisplayEl.trigger('change');
    }
    {/literal}

</script>

