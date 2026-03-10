<?php
App::uses('HttpSocket', 'Network/Http');

/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('AppController', 'Controller');

/**
 * Static content controller. Will selectively load /manager/pages/filename
 * URLs for manager users.
 */
class PagesController extends AppController {

	/**
	 * This controller does not use a model
	 *
	 * @var array
	 */
	public $uses = array();

	/**
	 * Allows controllers to change auth access without having to override
	 * the entire beforeFilter.
	 *
	 * @access	public
	 * @return	void
	 */
	public function appAuth() {
		$this->Auth->allow(array(
			'developers_widget',
			'display',
			'tos',
		));
	}

	/**
	 * Displays a view
	 *
	 * @return mixed
	 */
	public function display() {
		return $this->_display(func_get_args(), '');
	}

	/**
	 * Displays an manager view
	 *
	 * @return mixed
	 */
	public function manager_display() {
		return $this->_display(func_get_args(), 'manager');
	}

	/**
	 * Does the heavy lifting for determing what file to view, if permitted.
	 *
	 * @param string $path The partial URL path requested.
	 * @param string $prefix The routing prefix (if any) in use for this request (to restrict access depending on role.)
	 * @return mixed
	 * @throws NotFoundException
	 */
	protected function _display($path, $prefix = '') {
		$count = count($path);
		if (!$count) {
			return $this->redirect(Configure::read('App.fullBaseUrl'));
		}
		$page = $subpage = $title = null;

		if (!empty($path[0])) {
			$page = $path[0];
		}
		if (!empty($path[1])) {
			$subpage = $path[1];
		}
		if (!empty($path[$count - 1])) {
			$title = Inflector::humanize($path[$count - 1]);

			// Block any prefixed page requests when no URL prefix present.
			// (Stops `/pages/manager_pagename` from working, but not `/manager/pages/pagename`.)
			if (empty($prefix) && $this->_nameIsPrefixed($path[$count - 1])) {
				throw new NotFoundException(__('Invalid page'));
			} elseif (!empty($prefix)) {
				// Auto-prepend the $prefix to the final component of the path.
				$path[$count - 1] = "{$prefix}_" . $path[$count - 1];
			}
		}
		$this->set(compact('page', 'subpage'));
		$this->set('title_for_layout', $title);
		$this->render(implode('/', $path));
	}

	/**
	 * Helper for determining if the provided filename begins with any of
	 * the app's Configured routing prefixes.
	 *
	 * @param string $ctpName The final component of the requested path name.
	 * @return bool True if $ctpName starts with any of the app's
	 */
	protected function _nameIsPrefixed($ctpName) {
		// If there are no active routing prefixes, the file is not considered to be prefixed.
		if (!($allPrefixes = Configure::read('Routing.prefixes'))) {
			return false;
		}

		// If the name is determined to start with any of the prefixes, return true.
		$f = function ($carry, $v) use ($ctpName) {
			return ($carry ?: strpos($ctpName, "{$v}_") === 0);
		};
		if (array_reduce($allPrefixes, $f, false)) {
			return true;
		}

		// If all other checks drop through, name is not prefixed.
		return false;
	}

	/**
	 * Fetches the Terms of Service page from apobox.com and extracts the
	 * <pre></pre> content. The extracted content is cached in `tos_content`.
	 *
	 * @return void
	 */
	public function tos() {
		$this->layout = 'tos';
		$content = Cache::read('tos_content');
		if (empty($content)) {
			$HttpSocket = $this->initHttpSocket();
			$request = $HttpSocket->get('http://www.apobox.com', 'page_id=3140');
			if ($request->isOk()) {
				$html = $request->body();
				$dom = $this->initDomDocument();
				// suppress HTML5 tag warnings
				libxml_use_internal_errors(true);
				$dom->loadHTML($html);
				libxml_clear_errors();
				$content = '';
				foreach ($dom->getElementsByTagName('pre') as $node) {
					$content .= $dom->saveHtml($node);
				}
				Cache::write('tos_content', $content);
			}
		}
		$this->set(compact('content'));
	}

	/**
	 * Display the widget docs in a formatted view to apply apobox styling.
	 *
	 * @return void
	 */
	public function developers_widget() {
		$title = 'Developer Documentation';
		$content = file_get_contents(WWW_ROOT . 'widgets/signup.html');
		$this->set(compact('content', 'title'));
		$this->render('general');
	}

	/**
	 * initHttpSocket
	 *
	 * @return object $HttpSocket
	 */
	protected function initHttpSocket() {
		return new HttpSocket();
	}

	/**
	 * initDomDocument
	 *
	 * @return object $DomDocument
	 */
	protected function initDomDocument() {
		return new DomDocument();
	}
}
