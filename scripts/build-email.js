#!/usr/bin/env node
/**
 * Email template build script.
 *
 * Replaces grunt-premailer: reads the template .ctp, inlines CSS using juice,
 * uncomments CakePHP tags (<!-- ... --> → ...), and writes the output.
 */
const fs = require('fs');
const path = require('path');
const juice = require('juice');

const templatePath = path.resolve(__dirname, '../View/Layouts/Emails/html/default.template.ctp');
const outputPath = path.resolve(__dirname, '../View/Layouts/Emails/html/default.ctp');

const html = fs.readFileSync(templatePath, 'utf8');
const inlined = juice(html, {
	preserveMediaQueries: true,
	preserveFontFaces: true,
	preserveImportant: true,
	webResources: {
		relativeTo: path.resolve(__dirname, '../'),
	},
});

// Uncomment CakePHP tags: <!--<?php ... ?>--> → <?php ... ?>
const result = inlined.replace(/<!--(.*?)-->/g, '$1');
fs.writeFileSync(outputPath, result, 'utf8');
console.log('Email template built: ' + outputPath);
