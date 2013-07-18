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