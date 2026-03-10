import $ from 'jquery';

var toggleInsurance = function() {
	var coverage = $('#CustomPackageRequestInsuranceCoverage').val();
	if ($('#CustomPackageRequestInsurance').val() == '1') {
		$('#insurance-coverage').removeClass('hidden');
	} else {
		$('#insurance-coverage').addClass('hidden');
		$('#CustomPackageRequestInsuranceCoverage').val('').removeAttr('value');
	}
};
var removeSpaces = function() {
	var el = $('#CustomPackageRequestTrackingId');
	$(el).val($(el).val().replace(/\s+/g, ''));
};
var showCoverageIfPopulated = function() {
	if ($('#CustomPackageRequestInsuranceCoverage').val() !== '') {
		$('#insurance-coverage').removeClass('hidden');
		$('#CustomPackageRequestInsurance').val(1);
	}
};
$(document).ready(function() {
	showCoverageIfPopulated();
	$('#CustomPackageRequestInsurance').on('change', function() {toggleInsurance()});
	$('#CustomPackageRequestTrackingId').on('change', function() {removeSpaces()});
});
