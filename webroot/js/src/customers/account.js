import $ from 'jquery';
import 'bootstrap';

$(document).ready(function() {
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	});

	$(function(){
		var hash = window.location.hash;
		hash && $('ul.nav a[href="' + hash + '"]').tab('show');

		$('.nav-tabs a').click(function (e) {
			$(this).tab('show');
			var scrollmem = $('body').scrollTop();
			window.location.hash = this.hash;
			$('html,body').scrollTop(scrollmem);
		});

		$('.btn-close').on('click', function(e) {
			e.preventDefault();
			var url = $(this).attr('href');
			$.ajax({
				url: url
			})
				.done(function(msg) {
					if (msg === 'success') {
						$('.close-account.success').fadeIn();
					} else {
						$('.close-account.error').fadeIn();
					}
				});
		});

		$('.btn-cancel').on('click', function(e) {
			e.preventDefault();
			$('.close-account').fadeOut();
		});
	});
});
