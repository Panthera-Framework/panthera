<script type="text/javascript">
    $("#navigationBackBtn").bind('mouseheld', function(e) { createPopup('?display=navigation_history', 1024, 620); })
</script>

<span style="float: right; margin-right: 10px; margin-top: -5px;">
    <a href="#back-button" id="navigationBackBtn" onclick="navigateTo('{function="navigation::getBackButton()"}');"><span class="tooltip">Click and hold to see history</span><img src="images/admin/tango-icon-theme/Go-previous.svg" style="width: 30px"></a> 
</span>
