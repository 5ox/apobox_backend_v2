import $ from 'jquery';
import 'bootstrap';

$(document).ready(function() {
	$('#TrackingAddException').change(function() {
		$('#TrackingCommentsContainer').toggle(200);
		$('#TrackingComments').focus();
	});
});
