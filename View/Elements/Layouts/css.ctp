<?php

$css = array(
	'bootstrap' => 'bootstrap.min',
	'fonts' => 'font-awesome.min',
	'global' => 'global',
);
$css = array_merge($css, (!empty($extra) ? $extra : []));
if (Configure::read('CDN.enabled')) {
	$css['bootstrap'] = '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css';
	$css['fonts'] = '//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css';
}
echo $this->Html->css($css);
