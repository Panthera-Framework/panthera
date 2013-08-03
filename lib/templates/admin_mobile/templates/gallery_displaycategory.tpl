    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=gallery" data-transition="push">{function="localize('Gallery')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Display category')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
     <div class="slider tab-slider">
       {if="!$all_langs"}
        <ul>
            <ul class="list inset">
                <li class="list-divider">{function="localize('Gallery in other languages', 'gallery')"}</li>

               {loop="$languages"}
                <li class="list-item-single-line">
                  <a href="#{$key}" onclick="window.location = '?display=gallery&action=display_category&unique={$unique}&language={$key}'">
                    <p>{$key}</p>
                  </a>
                </li>
               {/loop}

               <label>{function="localize('Items', 'gallery')"}</label>
            </ul>
        </ul>
      {/if}

      <div class="gallery">

        {loop="$item_list"}
              <a href="?display=gallery&action=edit_item_form&itid={$value->id}">
                <div class="image">
                    <img src="{$value->getThumbnail(300, True, True)}" class="picture" {if="$value->visibility == 1"} style='opacity: 0.2;' {/if}/>
                    <div class="description" {if="$value->visibility == 1"} style='color: #7E7E7E;' {/if}>
                        <p><b>{$value->title}</b></p>
                        <small>{$value->description}</small>
                        <br>
                    </div>
                </div>
              </a>
        {/loop}

      </div>
     </div>
    </div>