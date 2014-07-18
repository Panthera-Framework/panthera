<script type="text/javascript">
/**
 * Remove a object
 *
 * @author Damian Kęska
 */

function dataModelManagementRemove(objectid)
{
	panthera.confirmBox.create('{$lang.deletionConfirm}', function (responseText) {
        if (responseText == 'Yes')
        {
        	panthera.jsonPOST({url: '?{function="Tools::getQueryString(null, 'action=remove', '_')"}', data: 'objectID='+objectid, success: function(response) {
				if (response.status == 'success') { navigateTo(window.location.href); }
			}});
        }
	});
}
</script>