    {include 'header.tpl'}
    
    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li class="active"><a data-ignore="true">{"Login"|localize}</a></li>
      </ul>
    </nav>
    
    <div class="content">
     <form name="loginform" class="inset" id="loginform" action="?" method="post">
       <input type="text" name="log" id="user_login" placeholder="{"Login"|localize}" class="input-text" autocomplete="off">
       <input type="password" name="pwd" placeholder="{"Password"|localize}" class="input-text" autocomplete="off">
       
       <input type="submit" class="btn-block" value="{"Sign in"|localize}" id="send_button">
       
       <button class="btn-block" id="wp-recover" onclick="setRecovery();">{"Recover password"|localize}</button>
     </form>
    </div>
    {include 'footer.tpl'}
