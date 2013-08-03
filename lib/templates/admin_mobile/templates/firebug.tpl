    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=debug&cat=admin');">{function="localize('Debugging center')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Firebug settings', 'firebug')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">

        <ul>
            <li id="firebug" class="tab-item active">
                <ul class="list inset">
                    <li class="list-divider">{function="localize('Whitelist - only listed users will be able to use Firebug', 'firebug')"}</li>

                    {if="count($whitelist) == 0"}
                    <li class="list-item-single-line">
                      <div class="inline">
                        <h3>{function="localize('No addresses in whitelist, everybody is able to use Firebug', 'firebug')"}</h3>
                      </div>
                    </li>
                   {else}
                    {loop="$whitelist"}
                        <li class="list-item-single-line" id="addr_{$key}">
                          <div class="inline">
                            <button class="btn-small" style="float: right;" onclick="removeAddress('{$value}', '{$key}');">{function="localize('Delete')"}</button>
                            <h3>{$value}</h3>
                          </div>
                        </li>
                    {/loop}
                   {/if}

                   <li class="list-item-single-line">
                     <div class="inline">
                        <button class="btn-small" id="addrAddBtn" style="float: right;">{function="localize('Add')"}</button>
                        <input type="text" placeholder="IP" value='{$current_address}' id="addr" onfocus="this.value = '';" class="input-text inline" style="border-bottom: 0px; max-width: calc(100% - 162px);">
                     </div>
                   </li>

                   <br>

                   <li class="list-divider">{function="localize('informations', 'firebug')"}</li>

                   <li class="list-item-two-lines">
                      <div>
                        <h3>{$client_version}</h3>
                        <p>{function="localize('Client version', 'firebug')"}</p>
                      </div>
                   </li>

                   <li class="list-item-two-lines">
                      <div>
                        <h3>{$server_version}</h3>
                        <p>{function="localize('Server version', 'firebug')"}</p>
                      </div>
                   </li>

                </ul>
            </li>
        </ul>
      </div>
    </div>

  <!-- JS code -->
   <script type="text/javascript">
   $(document).ready(function () {
        /**
          * After click on a "Add" button a form will be sent
          *
          * @event click
          * @author Damian Kęska
          */

        $('#addrAddBtn').click(function () {
            panthera.jsonPOST({ url: '?display=firebugSettings&cat=admin&action=add', data: 'addr='+$('#addr').val(), success: function (response) {
                 if (response.status == "success")
                    navigateTo("?display=firebugSettings&cat=admin");
            }});

        });
   });

    /**
     * Remove address from table
     *
     * @param string address IP address
     * @author Damian Kęska
     */

   function removeAddress(address, id)
   {
        panthera.jsonPOST({ url: '?display=firebugSettings&cat=admin&action=remove', data: 'addr='+address, success: function (response) {
            if (response.status == "success")
                $('#addr_'+id).remove();

            }
       });
   }
   </script>
  <!-- End of JS code -->