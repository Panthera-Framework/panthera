    {include 'header.tpl'}

    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=gallery" data-transition="push">{"Gallery"|localize}</a></li>
        <li class="active"><a data-ignore="true">{"Display category"|localize}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="gallery">
        {foreach from=$item_list key=k item=i}
              <a href="?display=gallery&action=edit_item_form&itid={$i->id}">
                <div class="image">
                    <img src="{$i->getThumbnail(400, True, True)}" alt="{$i->title}" class="picture" {if $i->visibility eq 1} style="opacity: 0.2;" {/if}/>
                    <div class="description" {if $i->visibility eq 1} style="color: #7E7E7E;" {/if}>
                        <p><b>{$i->title}</b></p>
                        <small>{$i->description}</small>
                        <br>
                    </div>
                </div>
              </a>
        {/foreach}
      </div>

    </div>

    {include 'footer.tpl'}
