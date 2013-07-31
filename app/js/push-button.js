// Toggles the on-off push-buttons for StatusWolf
function push_button(button) {
	if ($(button).parent('div').hasClass('push-button'))
	{
		if ($(button).siblings('input').prop('checked'))
		{
			$(button).parent('.push-button').removeClass('pushed');
			$(button).children('span.iconic').removeClass('iconic-check-alt green').addClass('iconic-x-alt red');
			if ($(button).parent('div').hasClass('binary'))
			{
				$(button).children('span.binary-label').text('No ');
			}
		}
		else
		{
			$(button).parent('.push-button').addClass('pushed');
			$(button).children('span.iconic').removeClass('iconic-x-alt red').addClass('iconic-check-alt green');
			if ($(button).parent('div').hasClass('binary'))
			{
				$(button).children('span.binary-label').text('Yes');
			}
		}
	}

}
