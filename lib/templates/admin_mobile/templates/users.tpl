    {include 'header.tpl'}
    
    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=dash" data-transition="push">{"Dash"|localize}</a></li>
        <li class="active"><a data-ignore="true">{"Users"|localize}</a></li>
      </ul>
    </nav>

    <div class="content inset">
     <div class="slider tab-slider">
        <ul>
          <ul class="list inset">

             <li class="list-divider">{"Users"|localize} {$users_from}-{$users_to}</li>

            {foreach from=$users_list key=k item=v}
             <li class="list-item-two-lines selectable">
                <a {if $view_users == True} href="?display=settings&action=my_account&uid={$v.id}" {else} href="" {/if}>
                    <h3>{$v.full_name}</h3>
                    <p>{$v.login} ({$v.primary_group})</p>
                </a>
             </li>
            {/foreach}

            {foreach from=$pager key=user item=active}
             {if $active == true}
                {$control = $user}
             {/if}

             {if $control eq $user-1}
              <li class="list-divider">{"Pages"|localize:settings}</li>
              <li class="list-item-single-line selectable">
                 <a href="" onclick="jumpToAjaxPage({$user+1});"> {"Next page"|localize:settings} </a>
              </li>
             {elseif $control > 0}
              <li class="list-item-single-line selectable">
                 <a href="" onclick="jumpToAjaxPage({$user-1});"> {"Previous page"|localize:settings} </a>
              </li>
             {/if}
            {/foreach}

          </ul>
       </ul>
     </div>
    </div>
    
    <script type="text/javascript">
    $(document).ready(function(){
        function jumpToAjaxPage(id) {
            panthera.htmlGET({ url: '?display=settings&action=users&subaction=show_table&usersPage='+id, success: '#all_users_window' });
        };
    });
    </script>

    {include 'footer.tpl'}
