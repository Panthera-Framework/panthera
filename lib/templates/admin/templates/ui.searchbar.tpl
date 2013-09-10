{$bar=$uiSearchbars[$uiSearchbarName]}

<style>
    #searchField {
        background: url({$PANTHERA_URL}/images/admin/ui/search.png) no-repeat 5px 2px;
        -moz-border-radius: 2px;
        border-radius: 2px;
        font-size: 14px;
        height: 22px;
        line-height: 1.2em;
        padding: 4px 10px 4px 28px;
        width: 80%;
        height: 28px;
        
        -webkit-transition-duration: 400ms;
        -webkit-transition-property: width, background;
        -webkit-transition-timing-function: ease;
        -moz-transition-duration: 400ms;
        -moz-transition-property: width, background;
        -moz-transition-timing-function: ease;
        -o-transition-duration: 400ms;
        -o-transition-property: width, background;
        -o-transition-timing-function: ease;
    }
    
    #searchField:focus {
        -webkit-transition-duration: 400ms;
        -webkit-transition-property: width, background;
        -webkit-transition-timing-function: ease;
        -moz-transition-duration: 400ms;
        -moz-transition-property: width, background;
        -moz-transition-timing-function: ease;
        -o-transition-duration: 400ms;
        -o-transition-property: width, background;
        -o-transition-timing-function: ease;
        width: 80%;
        background-color: rgb(249, 249, 249);
    }
</style>

<script type="text/javascript">
$(document).ready(function () {
    $('#{$uiSearchbarName}_form').submit(function () {
        {if="$bar.navigate"}
            navigateTo('{$bar.formAction}&'+$('#uiTop_form').serialize());
        {else}
        
            {if="$bar.formMethod == 'POST'"}
                panthera.jsonPOST( { 'data': '#{$uiSearchbarName}_form', method: '{$bar.formMethod}', success: function (response) {
                        panthera.logging.output('Looking for callback {$uiSearchbarName}_callback', 'searchBar');
                
                        if (typeof {$uiSearchbarName}_callback == "function")
                        {
                            {$uiSearchbarName}_callback(response);
                        }
                    }
                });
            {else}
                {$uiSearchbarName}_callback('{$bar.formAction}', $('#{$uiSearchbarName}_form').serialize());
            {/if}
        {/if}
        
        return false;
    });
});
</script>

<div style="margin-left: 25px; margin-top: 15px; margin-bottom: 15px; width: 92%; position: relative;">
        <div style="margin: 0 auto; max-width: 40%; position: relative; height: 30px;">
                <span style="float: right;">
                    {if="count($bar['settings']) > 0"}
                    <span data-dropdown="#searchDropdown" id="searchDropdownSpan" style="position: relative; cursor: pointer;">
                        <img src="{$PANTHERA_URL}/images/admin/ui/search-settings.png" style="max-height: 13px;">
                    </span>
                    {/if}

                
                {loop="$bar['icons']"}
                    {if="isset($value['link'])"}
                    <a href="{$value.link}"{if="isset($value['popup'])"} onclick="createPopup('{$value.popup}', 1300, 550);"{/if}{if="isset($value['alt'])"} alt="{$value.alt}"{/if}>
                    {/if}
                    <img src="{$value.icon}" style="max-height: 23px; margin-left: 3px; vertical-align: middle; padding-bottom: 5px;"{if="isset($value['alt'])"} alt="{$value.alt}"{/if}>
                    {if="isset($value['link'])"}
                    </a>
                    {/if}
                {/loop}
                </span>
                <form action="{$bar.formAction}" method="{$bar.formMethod}" id="{$uiSearchbarName}_form">
                    <input type="text" id="searchField" value="{$bar.query}" name="query" placeholder="{function="localize('Search', 'search')"}" style="float: right; margin-right: 5px;"> 

                    {if="count($bar['settings']) > 0"}
                    <div id="searchDropdown" class="dropdown dropdown-tip dropdown-relative">
                        <ul class="dropdown-menu">
                        {loop="$bar['settings']"}
                            <li>
                                {*} Checkboxes {/*}
                                {if="$value.type == 'checkbox'"}
                                <label><input type="checkbox" name="{$value.id}" id="sb_{$uiSearchbarName}_{$value.id}" value="{$value.value}"{if="$value['active']"} checked{/if}> {$value.title}</label>

                                {*} Selects {/*}
                                {elseif="$value.type == 'select'"}
                                <label>{$value.title} 
                                <select name="{$value.id}">
                                    {loop="$value.value"}
                                    <option value="{$key}"{if="$value.selected"} selected{/if}>{$value.title}</option>
                                    {/loop}
                                </select>
                                </label>

                                {*} Text fields {/*}

                                {elseif="$value.type == 'text'"}
                                <label><input type="text" name="{$value.id}" placeholder="{$value.title}" style="width: 98%;"></label>
                                {/if}
                            </li>
                        {/loop}
                        </ul>
                    </div>
                    {/if}
                </form>
        </div>
</div>
