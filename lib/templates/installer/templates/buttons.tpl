<script type="text/javascript">
$(document).ready(function () {
    {if="$installerBackBtn == True"}
    $('#installer-controll-backBtn').attr('disabled', false);
    {else}
    $('#installer-controll-backBtn').attr('disabled', true);
    {/if}
    
    {if="$installerNextBtn == True"}
    $('#installer-controll-nextBtn').attr('disabled', false);
    {else}
    $('#installer-controll-nextBtn').attr('disabled', true);
    {/if}
});
</script>
