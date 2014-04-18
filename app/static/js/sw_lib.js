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
  if ($(button).hasClass('push-button'))
  {
    if ($(button).children('input').prop('checked'))
    {
      $(button).removeClass('pushed')
          .children('input').attr('checked', null).prop('checked', false);
      $(button).children('label').children('span.elegant-icons').removeClass('icon-check-alt green').addClass('icon-close-alt red');
      if ($(button).hasClass('binary'))
      {
        $(button).children('label').children('span.binary-label').text('No ');
      }
    }
    else
    {
      $(button).addClass('pushed')
          .children('input').attr('checked', 'Checked').prop('checked', true);
      $(button).children('label').children('span.elegant-icons').removeClass('icon-close-alt red').addClass('icon-check-alt green');
      if ($(button).hasClass('binary'))
      {
        $(button).children('label').children('span.binary-label').text('Yes');
      }
    }
  }
  else if ($(button).hasClass('toggle-button'))
  {
    $(button).addClass('toggle-on');
    $(button).siblings('div.toggle-button').removeClass('toggle-on');
    $(button).children('label').children('input').attr('checked', 'Checked');
    $(button).siblings('div.toggle-button').children('label').children('input').attr('checked', null);
  }
}
