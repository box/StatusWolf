$('label').click(function() {
	if ($(this).parent('div').hasClass('push-button'))
	{
		if ($(this).siblings('input').prop('checked'))
		{
			$(this).parent('.push-button').removeClass('pushed');
			$(this).children('span.iconic').removeClass('iconic-check-alt green').addClass('iconic-x-alt red');
			if ($(this).parent('div').hasClass('binary'))
			{
				$(this).children('span.binary-label').text('No');
			}
		}
		else
		{
			$(this).parent('.push-button').addClass('pushed');
			$(this).children('span.iconic').removeClass('iconic-x-alt red').addClass('iconic-check-alt green');
			if ($(this).parent('div').hasClass('binary'))
			{
				$(this).children('span.binary-label').text('Yes');
			}
		}
	}

});
