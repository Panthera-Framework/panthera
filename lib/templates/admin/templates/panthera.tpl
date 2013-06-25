<div class="paHeader">
      <div class="paTitle">{"About Panthera"|localize:panthera}</div>
      <div class="paDescription">{"Panthera framework informations"|localize:panthera}</div>
</div>

<div class="paLine"></div>

<article>
 <div class="text-section">
    {$text}
 <br><br>
 <h2>{"Includes"|localize}:</h2>
 <ul>
 {foreach from=$goods key=k item=i}
     <li>{$i}</li>
 {/foreach}
 </ul>  
 </div>
</article>
