<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>
<div class="titlebar">{function="localize('Dash')"} - {function="localize('Everything is here', 'dash')"}{include="_navigation_panel.tpl"}</div>
        {loop="$dash_messages"}
            {if="$value.type == 'warning'"}
                <div class="msgWarning" style="display: block;">{$value.message}</div>
            {/if}

            {if="$value.type == 'error'"}
                <div class="msgError" style="display: block;">{$value.message}</div>
            {/if}

            {if="$value.type == 'info'"}
                <div class="msgInfo" style="display: block;">{$value.message}</div>
            {/if}

            {if="$value.type == 'success'"}
                <div class="msgSuccess" style="display: block;">{$value.message}</div>
            {/if}
        {/loop}

        <div class="grid-1">
               <ul class="picturesBox">
                   {loop="$dash_menu"}
            	   <li><a style="cursor: pointer;" class="{if="$value.linkType == 'ajax'"}ajax_link{/if}"{if="$value.linkType == 'onclick'"} onclick="{$value.link}"{else} href="{$value.link|pantheraUrl}"{/if}>  <img src="{$value.icon|pantheraUrl}" alt=""></a>
            	         <ul class="picturesBoxItem">
                         		<a style="cursor: pointer;" class="{if="$value.linkType == 'ajax'"}ajax_link{/if}"{if="$value.linkType == 'onclick'"} onclick="{$value.link}"{else} href="{$value.link|pantheraUrl}"{/if}>{$value.name}</a>
                         </ul>
                   </li>
                   {/loop}
				</ul>
				 <div class="clear"></div>
        </div>

        {if="isset($galleryItems) and count($galleryItems) > 0"}
        <div class="grid-2">
           <div class="title-grid">{function="localize('Gallery')"}<span></span></div>
           <div class="content-gird">
           <ul class="picturesBox">
                   {loop="$galleryItems"}
            	   <li><a href="{$value->link|pantheraUrl}">  <img src="{$value->thumbnail|pantheraUrl}" alt="" style="max-width: 110px;"></a>
            	         <ul class="picturesBoxItem">
                         		<a href="{$value->link|pantheraUrl}">{$value->title}</a>
                         </ul>
                   </li>
                   {/loop}
		   </ul>
                <div class="clear"></div>
           </div>
        </div>
        {/if}
        
        {if="isset($lastLogged) and count($lastLogged) > 0"}
        <div class="grid-2">
           <div class="title-grid">{function="localize('Recently logged in users')"}<span></span></div>
           <div class="content-table-grid">
              <table class="insideGridTable">
                   {loop="$lastLogged"}
                   <tr>
            	        <td><a href="?display=settings&action=my_account&uid={$value.uid}" class="ajax_link"><img src="{$value.avatar}" style="width: 20px"></a></td><td><a href="?display=settings&action=my_account&uid={$value.uid}" class="ajax_link">{$value.login}</a></td><td> {$value.time} {function="localize('ago')"}</td>
            	   </tr>
                   {/loop}
               </table>
                <div class="clear"></div>
           </div>
        </div>
        {/if}
