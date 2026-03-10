import $ from 'jquery';
import 'bootstrap';

$(function() {

	// remove flash message after 4 seconds
	$('.alert-dismissable').delay(4000).slideUp(300, function() {
		$(this).alert('close');
	});

	$('[data-toggle="popover"]').popover({trigger: 'hover'})
});
