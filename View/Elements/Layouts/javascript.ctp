<?php
/**
 * Javascript
 *
 * This element is the entrypoint for Javascript code. It expects to find
 * a `main.js` file in `webroot/js` by default. Additional JS files can be
 * included or main.js can be overridden. Optionally, and suggested for
 * development, JSPM auto building can be enabled to avoid needing to run a
 * build step after every change.
 *
 * To add or change JS files, pass in an array called `extra` to this element.
 *
 *    // Adding an admin.js
 *    $this->element('Layouts/javascript', ['extra' => ['admin']]);
 *
 *    or
 *
 *    // Replacing main.js
 *    $this->element('Layouts/javascript', ['extra' => ['main' => 'myjs']]);
 *
 * To use JSPM, set `Javascript.autoBuild` to `true` in the config. This is on
 * by default in Vagrant config.
 */

$scripts = ['main' => 'main'];
$scripts = array_merge($scripts, (!empty($extra) ? $extra : []));

if (Configure::read('Javascript.autoBuild')) {
	$this->element('js-import', ['js' => $scripts]);
	$scripts = [
		'jspm' => 'jspm/packages/system',
		'jspm_config' => 'jspm/config',
	];
}

echo $this->Html->script($scripts);
