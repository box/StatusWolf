/**
 * Basic javascript functions for StatusWolf
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 03 July 2013
 */

/**
 * Select the text of any element in the DOM, just as if you'd highlighted
 * it with your mouse. Taken from
 * http://stackoverflow.com/questions/985272/jquery-selecting-text-in-an-element-akin-to-highlighting-with-your-mouse
 *
 * @param element
 */
function select_text(element)
{
	var doc = document
		,text = doc.getElementById(element)
		,range
		,selection;

	// For non-MS browsers, StatusWolf no support the IE
	if (window.getSelection)
	{
		selection = window.getSelection();
		range = doc.createRange();
		range.selectNodeContents(text);
		selection.removeAllRanges();
		selection.addRange(range);
	}
}

function statuswolf_button(button)
{
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
  else if ($(button).parent('div').hasClass('toggle-button'))
  {
    $(button).parent('div').addClass('toggle-on');
    $(button).parent('div').siblings('div.toggle-button').removeClass('toggle-on');
    $(button).children('input').attr('checked', 'Checked');
    $(button).parent('div').siblings('.toggle-button').children('label').children('input').attr('checked', null);
  }
}