{$site_header}

{function="localizeDomain('contact')"}

<script type="text/javascript">
var gmap = "";
var map = "";

function gmapsCallback ()
{
    var mapOptions = {
        zoom: {$map_zoom},
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: new google.maps.LatLng({$map_x}, {$map_y})
    };
    
    gmap.createMap("map", mapOptions);
}

/**
  * Init MCE editor
  *
  * @author Mateusz Warzyński
  */

function initEditor()
{
    mceSetContent('address_text', htmlspecialchars_decode("{$adress_text}"));
}

jQuery(document).ready(function() {
    /**
      * Init MCE Editor
      *
      * @author Mateusz Warzyński
      */
    
    mceInit('address_text');

    setTimeout(initEditor, 500);
    
    if ($('#map').length > 0)
    {
        {if="!isset($map_zoom)"}
        {$map_zoom = 10}
        {$map_x = 0}
        {$map_y = 0}
        {/if}

        gmap = new panthera.googleMap('gmapsCallback', '{$gmapsApiKey}');
        
        if (typeof google !== 'undefined')
            gmapsCallback();
            
        /**
          * Find place from input
          *
          * @author Mateusz Warzyński
          */

        $('#map_form').submit(function () {
            place = $('#map_searchbox').val();
            
            if (place != "")
            {
                gmap.getLocation(place);
                $('#map_bounds').val(JSON.stringify({ "bounds":gmap.map.getBounds(), "zoom": gmap.map.getZoom(), "center": gmap.map.getCenter() }));
            }

            return false;
        });
        
         /**
          * Save map bounds
          *
          * @author Mateusz Warzyński
          */

        $('#contact_form').submit(function () {
            $('#map_bounds').val(JSON.stringify({ "bounds":gmap.map.getBounds(), "zoom": gmap.map.getZoom(), "center": gmap.map.getCenter() }));
            return false;
        });
    }

    /**
      * Save contact information
      *
      * @author Mateusz Warzyński
      */

    $('#contact_form').submit(function () {
        panthera.jsonPOST({ data: '#contact_form', mce: 'tinymce_all'});
        return false;

    });
    
    /**
      * Save options contact information
      *
      * @author Mateusz Warzyński
      */

    $('#contact_form_options').submit(function () {
        panthera.jsonPOST({ url: "?display=contact&cat=admin&action=save_options", data: '#contact_form_options'});
        return false;

    });
    
    panthera.forms.checkboxToggleLayer({ input: '#oneContactCheckbox', layer: '#contactLanguage', reversed: true });
});
</script>

{function="uiMce::display()"}

{include="ui.titlebar"}

<div id="topContent" style="min-height: 0px;">
    <div class="searchBarButtonArea">
    
        <span data-searchbardropdown="#searchDropdown" id="searchDropdownSpan" style="position: relative; cursor: pointer;">
             <input type="button" value="{function="localize('Switch language', 'custompages')"}">
        </span>

        <div id="searchDropdown" class="searchBarDropdown searchBarDropdown-tip searchBarDropdown-relative">
            <ul class="searchBarDropdown-menu">
            {loop="$languages"}
                <li style="text-align: left;"><a href="#{$key}" onclick="navigateTo('?display=contact&cat=admin&language={$key}');">{$key}</a></li>
            {/loop}
            </ul>
        </div>
       
        <input type="button" value="{function="localize('Options')"}" onclick="panthera.popup.toggle('element:#popupOptions');">
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block; width: 70%; margin: 0 auto;">
            <p style="font-size: 11px;">
                <form action="?display=contact&cat=admin&action=save" method="GET" id="map_form"><input type="text" value="" id="map_searchbox" placeholder="{function="localize('Search', 'contactpage')"}" style="width:150px; font-size:11px;"></form>
            </p>
            <div id="map" style="width: 100%; height: 300px; margin-top: 10px;"></div>
    </div><br><br>
   
   <form action="?display=contact&cat=admin&action=save" method="GET" id="contact_form">
    <div style="display: inline-block; margin: 0 auto;">
            <textarea id="address_text" name="address_text" style="height: 350px; width: 750px;">{$adress_text}</textarea>
            <input type="submit" value="{function="localize('Save')"}" style="margin-top: 5px;">
    </div>
    <input type="hidden" name="map_bounds" id="map_bounds">
   </form>
</div>

<!-- Options popup -->

<div id="popupOptions" style="display: none;">
   <form action="?display=contact&cat=admin&action=save_options" method="GET" id="contact_form_options">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        <thead>
            <tr>
                <th colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Options')"}</p>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>{function="localize('E-mail address where all messages from form will to', 'contactpage')"}:</th>
                <th><input type="text" name="contact_email" value="{$contact_mail}"></th>
            </tr>
            <tr>
                <th>{function="localize('One contact page for all languages', 'contactpage')"}:</th>
                <th><input type="checkbox" value="1" name="all_langs"{if="$oneContactPage == True} checked{/if"} id="oneContactCheckbox"></th>
            </tr>
            
            <!-- <tr{if="$oneContactPage == True} style='display: none;'{/if"} id="contactLanguage">
                <th style="border-right: 0px; border-bottom: 0px;">{function="localize('Save this contact page in', 'contactpage')"}: </th>
                <th style="border-right: 0px; border-bottom: 0px;">
                    <select name="save_as_language">
                    {loop="$languages"}
                    <option value="{$key}"{if="$key == $selected_language"} selected{/if}>{$key}</option>
                    {/loop}
                    </select>
                </th>
            </tr> -->
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    <input type="submit" value="{function="localize('Save')"}" style="float: right; margin-right: 30px;">
                </td>
            </tr>
        </tfoot>
    </table>
   </form>
</div>
