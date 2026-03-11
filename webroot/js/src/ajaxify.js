/**
 * Ajaxify plugin
 *
 * This plugin library makes it easier to ajaxify portions of your application.
 */
export default class Ajaxify {
	/**
	 * Turn pagination links into ajax calls.
	 *
	 * @param {string} container The selector of the container.
	 * @returns void
	 */
	static pagination(container) {
		var containerEl = document.querySelector(container);
		if (!containerEl) return;

		containerEl.querySelectorAll('nav a, thead a').forEach(function(link) {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				fetch(this.getAttribute('href'))
					.then(function(response) { return response.text(); })
					.then(function(html) {
						var parser = new DOMParser();
						var doc = parser.parseFromString(html, 'text/html');
						var newContent = doc.querySelector(container);
						if (newContent) {
							containerEl.innerHTML = newContent.innerHTML;
							Ajaxify.pagination(container);
						}
					});
			});
		});
	}
}
