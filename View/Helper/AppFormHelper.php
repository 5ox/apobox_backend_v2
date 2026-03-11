<?php
App::uses('FormHelper', 'View/Helper');
App::uses('Hash', 'Utility');

/**
 * Class: AppFormHelper
 *
 * @see FormHelper
 */
class AppFormHelper extends FormHelper {

	/**
	 * create
	 *
	 * @param mixed $model The model name for which the form is being defined
	 * @param array $options Optional array of html attributes and options
	 * @return string
	 */
	public function create($model = null, $options = array()) {
		$defaults = array(
			'class' => '',
			'inputDefaults' => array(
				'format' => array('before', 'label', 'between', 'input', 'error', 'after'),
				'div' => array('class' => 'mb-3'),
				'class' => 'form-control',
				'label' => array('class' => 'form-label'),
				'between' => '<div class="col-sm-6">',
				'after' => '</div><div class="col-sm-3"></div>',
				'error' => array(
					'attributes' => array('wrap' => 'span', 'class' => 'form-text text-danger'),
				),
			)
		);
		$options = Hash::merge($defaults, $options);
		return parent::create($model, $options);
	}

	/**
	 * input
	 *
	 * @param mixed $fieldName The fieldName
	 * @param array $options Optional array of html attributes and options
	 * @return string
	 */
	public function input($fieldName, $options = array()) {
		if (!empty($options['type']) && $options['type'] == 'checkbox') {
			$checkboxDefaults = array(
				'class' => false,
				'format' => array('before', 'between', 'input', 'label', 'error', 'after'),
			);
			$options = Hash::merge($checkboxDefaults, $options);
		}

		return parent::input($fieldName, $options);
	}
}
