<?php
/**
 * Javascript
 *
 * Loads the Vite-built main.js bundle from webroot/build/js/.
 * All page-specific modules are bundled into main.js and self-activate
 * based on DOM element presence.
 */

echo $this->Html->script('/build/js/main.js');
