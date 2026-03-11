<?php

$css = array(
	'bootstrap' => 'bootstrap.min',
	'fonts' => 'font-awesome.min',
	'global' => '/build/css/global',
);
$css = array_merge($css, (!empty($extra) ? $extra : []));

// Map extra names to Vite build paths
foreach ($css as $key => $value) {
	if (in_array($key, ['public', 'admin', 'email'])) {
		$css[$key] = '/build/css/' . $value;
	}
}

if (Configure::read('CDN.enabled')) {
	$css['bootstrap'] = '//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';
	$css['fonts'] = '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css';
}
echo $this->Html->css($css);
