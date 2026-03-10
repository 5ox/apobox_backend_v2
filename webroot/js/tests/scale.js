import Scale from '../src/scale';

QUnit.module("parseResult");
QUnit.test("valid weight with other characters", function(assert) {
	var param = 'random12.25';
	var expected = {pounds: 12, ounces: 4};
	assert.deepEqual(new Scale().parseResult(param), expected, "with characters before");
	var param = '12.25characters';
	var expected = {pounds: 12, ounces: 4};
	assert.deepEqual(new Scale().parseResult(param), expected, "with characters after");
	var param = 'random12.25characters';
	var expected = {pounds: 12, ounces: 4};
	assert.deepEqual(new Scale().parseResult(param), expected, "with characters on both sides");
});
QUnit.test("round pounds", function(assert) {
	var param = '12.99';
	var expected = {pounds: 13, ounces: 0};
	assert.deepEqual(new Scale().parseResult(param), expected);
});
QUnit.test("zero pounds", function(assert) {
	var param = '0.015';
	var expected = {pounds: 0, ounces: 1};
	assert.deepEqual(new Scale().parseResult(param), expected);
});
QUnit.test("zero ounces", function(assert) {
	var param = '1.00';
	var expected = {pounds: 1, ounces: 0};
	assert.deepEqual(new Scale().parseResult(param), expected);
});
