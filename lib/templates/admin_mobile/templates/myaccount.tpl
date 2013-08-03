    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=settings&cat=admin&action=users');" data-transition="push">{function="localize('Users')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('User account', 'settings')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">

             <li class="list-divider">{function="localize('Panel with informations about user.', 'settings')"}</li>

              <li class="list-item-two-lines">
                    <h3>{$user_login}</h3>
                    <p>{function="localize('Login', 'settings')"}</p>
              </li>

              <li class="list-item-two-lines">
                <a href="#" onclick="$('#window_password').slideToggle();" data-ignore="true">
                    <h3>{function="localize('Change password', 'settings')"}</h3>
                    <p>{function="localize('Password', 'settings')"}</p>
                </a>
              </li>

              <div id="window_password" style="display: none;">
                <form action="?display=settings&cat=admin&action=my_account&changepassword{$user_uid}" method="POST" id="changepasswd_form">
                  {if="!isset($dontRequireOld)"}
                    <input type="password" name="old_passwd" class="input-text" placeholder="{function="localize('Old password', 'settings')"}">
                  {/if}
                    <input type="password" name="new_passwd" class="input-text" placeholder="{function="localize('New password', 'settings')"}">
                    <input type="password" name="retyped_newpasswd" class="input-text" placeholder="{function="localize('Retype new password', 'settings')"}">
                    <button type="submit" class="btn-block">{function="localize('Change password')"}</button>
                </form>
                  <br><br>
              </div>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3>{$full_name|ucfirst}</h3>
                    <p>{function="localize('Full name', 'settings')"}</p>
                </a>
              </li>

              <img src="{$profile_picture}" height="auto" width="200px">

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3>{$primary_group}</h3>
                    <p>{function="localize('Primary group', 'settings')"}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                    <h3>{$joined}</h3>
                    <p>{function="localize('Joined', 'settings')"}</p>
              </li>

              <li class="list-item-two-lines">
                <a href="#" onclick="$('#window_language').slideToggle();" data-ignore="true">
                    <h3>{$language|ucfirst}</h3>
                    <p>{function="localize('Language', 'settings')"}</p>
                </a>
              </li>

              <div id="window_language" style="display: none;">
                  <br>
                <form action="?display=settings&cat=admin&action=my_account&changelanguage{$user_uid}" method="POST" id="changelanguage_form">
                    <select name="language">
                       {loop="$locales_added"}
                         <option value="{$key}">{$key}</option>
                       {/loop}
                    </select>
                    <button type="submit" class="btn-block">{function="localize('Change language')"}</button>
                </form>
                  <br><br>
              </div>

            </ul>
        </ul>
     </div>
    </div>

   <!-- JS code -->
    <script type="text/javascript">

    /**
      * Submit change password form
      *
      * @author Damian Kęska
      */

    $('#changepasswd_form').submit(function () {
        panthera.jsonPOST({ data: '#changepasswd_form', success: function (response) {
                if (response.status == "success")
                {
                    jQuery('#window_password').slideUp();
                }
            }
        });

        return false;

     });

     /**
      * Submit language form
      *
      * @author Damian Kęska
      */

    $('#changelanguage_form').submit(function () {
        panthera.jsonPOST({ data: '#changelanguage_form', success: function (response) {
                if (response.status == "success")
                    navigateTo('?display=settings&cat=admin&action=my_account');
            }
        });

        return false;

    });
    </script>
   <!-- End of JS code -->