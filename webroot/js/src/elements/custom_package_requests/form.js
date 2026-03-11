var toggleInsurance = function() {
	var insuranceSelect = document.querySelector('#CustomPackageRequestInsurance');
	var coverageContainer = document.querySelector('#insurance-coverage');
	var coverageInput = document.querySelector('#CustomPackageRequestInsuranceCoverage');
	if (!insuranceSelect || !coverageContainer || !coverageInput) return;

	if (insuranceSelect.value == '1') {
		coverageContainer.classList.remove('hidden');
	} else {
		coverageContainer.classList.add('hidden');
		coverageInput.value = '';
		coverageInput.removeAttribute('value');
	}
};

var removeSpaces = function() {
	var el = document.querySelector('#CustomPackageRequestTrackingId');
	if (el) el.value = el.value.replace(/\s+/g, '');
};

var showCoverageIfPopulated = function() {
	var coverageInput = document.querySelector('#CustomPackageRequestInsuranceCoverage');
	var insuranceSelect = document.querySelector('#CustomPackageRequestInsurance');
	if (coverageInput && coverageInput.value !== '') {
		var container = document.querySelector('#insurance-coverage');
		if (container) container.classList.remove('hidden');
		if (insuranceSelect) insuranceSelect.value = '1';
	}
};

document.addEventListener('DOMContentLoaded', function() {
	showCoverageIfPopulated();
	var insuranceSelect = document.querySelector('#CustomPackageRequestInsurance');
	if (insuranceSelect) insuranceSelect.addEventListener('change', toggleInsurance);
	var trackingInput = document.querySelector('#CustomPackageRequestTrackingId');
	if (trackingInput) trackingInput.addEventListener('change', removeSpaces);
});
