    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin');" data-transition="push">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Users')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
     <div class="slider tab-slider">
        <ul>
          <ul class="list inset">

             <li class="list-divider">{function="localize('Users')"} {$users_from}-{$users_to}</li>

            {loop="$users_list"}
             <li class="list-item-two-lines selectable">
                <a {if="$view_users == True"} href="#" onclick="navigateTo('?display=settings&cat=admin&action=my_account&uid={$value.id}');" {else} href='' {/if}>
                    <h3>{$value.full_name}</h3>
                    <p>{$value.login} ({$value.primary_group})</p>
                </a>
             </li>
            {/loop}

            {loop="$pager"}
             {if="$value == true"}
                {$control = $user}
             {/if}

             {if="$control == $user-1"}
              <li class="list-divider">{function="localize('Pages', 'settings')"}</li>
              <li class="list-item-single-line selectable">
                 <a href="" onclick="jumpToAjaxPage({$user+1});"> {function="localize('Next page', 'settings')"} </a>
              </li>
             {elseif="$control > 0"}
              <li class="list-item-single-line selectable">
                 <a href="" onclick="jumpToAjaxPage({$user-1});"> {function="localize('Previous page', 'settings')"} </a>
              </li>
             {/if}
            {/loop}

          </ul>
       </ul>
     </div>
    </div>

   <!-- JS code -->
    <script type="text/javascript">
    $(document).ready(function(){
        function jumpToAjaxPage(id) {
            panthera.htmlGET({ url: '?display=settings&cat=admin&action=users&subaction=show_table&usersPage='+id, success: '#all_users_window' });
        };
    });
    </script>
   <!-- End of JS code -->