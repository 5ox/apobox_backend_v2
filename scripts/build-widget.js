#!/usr/bin/env node
/**
 * Widget build script.
 *
 * Replaces grunt-inline + grunt-contrib-copy:
 * 1. Reads widgets_src/signup/signup.html
 * 2. Inlines all CSS (<link>) and JS (<script src>) into the HTML
 * 3. Writes to webroot/widgets/signup/index.html
 * 4. Creates dev copies with domain replacement
 */
const fs = require('fs');
const path = require('path');

const srcDir = path.resolve(__dirname, '../widgets_src/signup');
const destDir = path.resolve(__dirname, '../webroot/widgets/signup');
const devDir = path.resolve(__dirname, '../webroot/widgets/dev');

// Read source HTML
let html = fs.readFileSync(path.join(srcDir, 'signup.html'), 'utf8');

// Inline CSS: <link rel="stylesheet" href="file.css"> → <style>contents</style>
html = html.replace(/<link[^>]+rel=["']stylesheet["'][^>]+href=["']([^"']+)["'][^>]*>/gi, function(match, href) {
	const cssPath = path.resolve(srcDir, href);
	if (fs.existsSync(cssPath)) {
		const css = fs.readFileSync(cssPath, 'utf8');
		return '<style>' + css + '</style>';
	}
	return match;
});

// Inline JS: <script src="file.js"></script> → <script>contents</script>
html = html.replace(/<script[^>]+src=["']([^"']+)["'][^>]*><\/script>/gi, function(match, src) {
	const jsPath = path.resolve(srcDir, src);
	if (fs.existsSync(jsPath)) {
		const js = fs.readFileSync(jsPath, 'utf8');
		return '<script>' + js + '</script>';
	}
	return match;
});

// Ensure output directories exist
fs.mkdirSync(destDir, { recursive: true });
fs.mkdirSync(devDir, { recursive: true });

// Write production widget
fs.writeFileSync(path.join(destDir, 'index.html'), html, 'utf8');
console.log('Widget built: ' + path.join(destDir, 'index.html'));

// Create dev copy with domain replacement
const devHtml = html
	.replace(/account\.apobox\.com/g, 'apobox.dev')
	.replace(/widgets\/demos/g, 'widgets/dev/demos')
	.replace(/widgets\/signup/g, 'widgets/dev/signup');

const devSignupDir = path.join(devDir, 'signup');
fs.mkdirSync(devSignupDir, { recursive: true });
fs.writeFileSync(path.join(devSignupDir, 'index.html'), devHtml, 'utf8');
console.log('Dev widget built: ' + path.join(devSignupDir, 'index.html'));
