<?php
/**
 * AppFlashComponent
 */

App::uses('Component', 'Controller');
App::uses('FlashComponent', 'Controller/Component');

/**
 * AppFlashComponent Component
 */
class AppFlashComponent extends FlashComponent {

	/**
	 * Used to set a session variable that can be used to output messages in the view.
	 *
	 * In your controller: $this->Flash->set('This has been saved', null, 'danger'); // or 'info', 'warning', 'success', 'primary'
	 *
	 * Additional params below can be passed to customize the output, or the Message.[key].
	 * You can also set additional parameters when rendering flash messages. See SessionHelper::flash()
	 * for more information on how to do that.
	 *
	 * @param string $message Message to be flashed
	 * @param array $options Options to pass
	 * @param string $type Shortcut to set flash type/class ('danger', 'success', etc.)
	 * @return void
	 */
	public function set($message, $options = array(), $type = null) {
		// If element isn't defined set to the flash element
		if (!isset($options['element'])) {
			$options['element'] = 'flash_bootstrap';
		}

		// Default params for the Flash::set method
		$defaultParams = array(
			'class' => 'alert-default',
		);

		if (!isset($options['params'])) {
			$options['params'] = array();
		}

		if (is_string($type)) {
			if (strpos($type, 'alert-') === false) {
				$type = "alert-{$type}";
			}
			$type = array(
				'class' => $type,
			);
			$options['params'] = Hash::merge($type, $options['params']);
		}

		// Merge passed in params with default params
		$options['params'] = Hash::merge($defaultParams, $options['params']);

		// If key isn't defined set to the default
		if (!isset($options['key'])) {
			$options['key'] = 'flash';
		}

		// Call the parent  method
		$this->parentSet($message, $options);
	}

	/**
	 * Wrapper method for the parent::set static method call
	 *
	 * @param string $message Message to be flashed
	 * @param array $options Options to pass
	 * @return void
	 * @codeCoverageIgnore Don't test Cake core's `set` method
	 */
	protected function parentSet($message, $options) {
		parent::set($message, $options);
	}
}
