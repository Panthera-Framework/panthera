<script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery-1.10.0.min.js"></script>
<script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery-ui.min.js"></script>
<script type="text/javascript" src="{$PANTHERA_URL}/js/admin/panthera.js"></script>
<script type="text/javascript" src="{$PANTHERA_URL}/js/admin/pantheraUI.js"></script>
<script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery.dropdown.js"></script>

<script type="text/javascript">
    panthera.locale.add({
        'Yes': '{function="localize('Yes')"}', 'No': '{function="localize('No')"}', 'Close': '{function="localize('Close')"}'
    });
  
    customNextBtn = false;
  
  /**
    * Next step button
    *
    * @hook onNextBtn
    * @return void 
    * @author Damian Kęska
    */
  
    function nextBtn()
    {
        if (customNextBtn == false)
            navigateTo('?_nextstep=True');
        else
            $(document).trigger('onNextBtn');
    }
  
  /**
    * Data validation button
    *
    * @return void 
    * @author Damian Kęska
    */
  
    function checkBtn()
    {
        $(document).trigger('onCheckBtn');
    }
</script>
