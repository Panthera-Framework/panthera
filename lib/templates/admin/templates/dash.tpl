<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>


<div class="titlebar">{"Dash"|localize} - {"Everything is here."|localize:dash}{include file="_navigation_panel.tpl"}</div>

        {foreach from=$dash_messages key=k item=v}
            {if $v.type == "warning"}
                <div class="msgWarning" style="display: block;">{$v.message}</div>
            {/if}

            {if $v.type == "error"}
                <div class="msgError" style="display: block;">{$v.message}</div>
            {/if}

            {if $v.type == "info"}
                <div class="msgInfo" style="display: block;">{$v.message}</div>
            {/if}

            {if $v.type == "success"}
                <div class="msgSuccess" style="display: block;">{$v.message}</div>
            {/if}
        {/foreach}

        <div class="grid-1">
               <ul class="picturesBox">
                   {foreach from=$dash_menu key=k item=v}
            	   <li><a style="cursor: pointer;" class="{if $v.linkType == "ajax"}ajax_link{/if}"{if $v.linkType == "onclick"} onclick="{$v.link}"{else} href="{$v.link|pantheraUrl}"{/if}>  <img src="{$v.icon|pantheraUrl}" alt=""></a>
            	         <ul class="picturesBoxItem">
                         		<a style="cursor: pointer;" class="{if $v.linkType == "ajax"}ajax_link{/if}"{if $v.linkType == "onclick"} onclick="{$v.link}"{else} href="{$v.link|pantheraUrl}"{/if}>{$v.name}</a>
                         </ul>
                   </li>
                   {/foreach}
				</ul>
				 <div class="clear"></div>
        </div>

        {if isset($galleryItems) and count($galleryItems) > 0}
        <div class="grid-1">
           <div class="title-grid">{"Gallery"|localize}<span></span></div>
           <div class="content-gird">
           <ul class="picturesBox">
                   {foreach from=$galleryItems key=k item=v}
            	   <li><a href="{$v->link|pantheraUrl}">  <img src="{$v->thumbnail|pantheraUrl}" alt="" style="max-width: 110px;"></a>
            	         <ul class="picturesBoxItem">
                         		<a href="{$v->link|pantheraUrl}">{$v->title}</a>
                         </ul>
                   </li>
                   {/foreach}
		   </ul>
                <div class="clear"></div>
           </div>
        </div>
        {/if}

