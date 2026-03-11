import * as bootstrap from 'bootstrap';

document.addEventListener('DOMContentLoaded', function() {

	// Remove flash messages after 4 seconds
	document.querySelectorAll('.alert-dismissible').forEach(function(el) {
		setTimeout(function() {
			el.style.transition = 'opacity 0.3s ease, max-height 0.3s ease';
			el.style.opacity = '0';
			el.style.maxHeight = '0';
			el.style.overflow = 'hidden';
			setTimeout(function() { el.remove(); }, 300);
		}, 4000);
	});

	// Initialize popovers
	document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el) {
		new bootstrap.Popover(el, { trigger: 'hover' });
	});
});
