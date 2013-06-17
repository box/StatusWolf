// Handles the toggle button groups for StatusWolf
$('label').click(function() {
	$(this).parent('div.toggle-button').addClass('toggle-on');
	$(this).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
	$(this).children('input').attr('checked', 'Checked');
	$(this).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
});
