import * as bootstrap from 'bootstrap';

document.addEventListener('DOMContentLoaded', function() {
	// Initialize tooltips
	document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
		new bootstrap.Tooltip(el);
	});

	// Tab navigation with hash routing
	var hash = window.location.hash;
	if (hash) {
		var tabEl = document.querySelector('ul.nav a[href="' + hash + '"]');
		if (tabEl) new bootstrap.Tab(tabEl).show();
	}

	document.querySelectorAll('.nav-tabs a').forEach(function(link) {
		link.addEventListener('click', function(e) {
			new bootstrap.Tab(this).show();
			var scrollmem = document.documentElement.scrollTop || document.body.scrollTop;
			window.location.hash = this.hash;
			document.documentElement.scrollTop = scrollmem;
			document.body.scrollTop = scrollmem;
		});
	});

	// Account closure
	document.querySelectorAll('.btn-close-account').forEach(function(btn) {
		btn.addEventListener('click', function(e) {
			e.preventDefault();
			var url = this.getAttribute('href');
			fetch(url)
				.then(function(response) { return response.text(); })
				.then(function(msg) {
					if (msg === 'success') {
						var successEl = document.querySelector('.close-account.success');
						if (successEl) successEl.style.display = '';
					} else {
						var errorEl = document.querySelector('.close-account.error');
						if (errorEl) errorEl.style.display = '';
					}
				});
		});
	});

	document.querySelectorAll('.btn-cancel').forEach(function(btn) {
		btn.addEventListener('click', function(e) {
			e.preventDefault();
			document.querySelectorAll('.close-account').forEach(function(el) {
				el.style.display = 'none';
			});
		});
	});
});
