document.addEventListener('DOMContentLoaded', function() {
	var checkbox = document.querySelector('#TrackingAddException');
	if (!checkbox) return;

	checkbox.addEventListener('change', function() {
		var container = document.querySelector('#TrackingCommentsContainer');
		if (container) {
			container.style.display = checkbox.checked ? '' : 'none';
			if (checkbox.checked) {
				var comments = document.querySelector('#TrackingComments');
				if (comments) comments.focus();
			}
		}
	});
});
