/**
 * Ajaxify plugin
 *
 * This plugin library makes it easier to ajaxify portions of your application.
 */
export default class Ajaxify {
	/**
	 * Turn pagination links into ajax calls.
	 *
	 * This expects all pagination links to be within `nav` and `thead` elements.
	 *
	 * When a pagination link is clicked, the contents of `container` will be
	 * replaced dynamically and this function will be re-applied to the new contents.
	 *
	 * @param {string} container The selector of the container.
	 * @returns void
	 */
	static pagination(container) {
		$('nav a, thead a', container).click(function() {
			$.ajax($(this).attr('href')).done(function(data) {
				$(container).html($(data).find(container).html());
				Ajaxify.pagination(container);
			});
			return false;
		});
	}
}
