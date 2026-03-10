var fs = require('fs');

module.exports = function(grunt) {
  var includePaths = [ 'webroot/js', 'webroot/css' ];

  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-inline');
  grunt.loadNpmTasks('grunt-jspm-builder');
  grunt.loadNpmTasks('grunt-markdown');
  grunt.loadNpmTasks('grunt-premailer');
  grunt.loadNpmTasks('grunt-scss-lint');
  grunt.loadNpmTasks('grunt-string-replace');
  grunt.loadNpmTasks('grunt-zip');

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    watch: {
      php: {
        files: [
          '!Lib/Cake/**',
          '!Vendor/**',
          '!tmp/**',
          '!.git/**/*.php',
          '**/*.php'
        ],
        tasks: 'null',
      },
      css: {
        files: ['webroot/sass/**/*.scss'],
        tasks: ['scsslint', 'compass']
      },
      email: {
        files: ['webroot/css/email.css', 'View/Layouts/Emails/html/*.template.ctp'],
        tasks: ['premailer', 'string-replace']
      },
      widget: {
        files: ['widgets_src/signup/**/*', '!widgets_src/signup/index.html'],
        tasks: ['inline', 'copy'],
      }
    },
    // grunt Injector is used to build a single html file with
    // all css and js deps inlined so that only a single file
    // is devlivered.
    inline: {
      prod: {
        options: {
          cssmin: true,
          uglify: false
        },
        src:  'widgets_src/signup/signup.html',
        dest: 'webroot/widgets/signup/index.html'
      },
    },
    copy: {
      dev: {
        files: [
          {
            expand: true,
            cwd: 'webroot/widgets',
            src: ['**', '!dev/**'],
            dest: 'webroot/widgets/dev/',
          },
        ],
        options: {
          process: function (content, srcpath) {
            var content = content.replace(/account.apobox.com/g,"apobox.dev");
            content = content.replace(/widgets\/demos/g,"widgets/dev/demos");
            return content.replace(/widgets\/signup/g,"widgets/dev/signup");
          },
        },
      },
      c3: {
        files: [
          {
            expand: true,
            cwd: 'webroot/widgets',
            src: ['**', '!dev/**'],
            dest: 'webroot/widgets/dev/c3/',
          },
        ],
        options: {
          process: function (content, srcpath) {
            var content = content.replace(/account.apobox.com/g,"c6.propic.com");
            return content.replace(/widgets\/signup/g,"widgets/signup");
          },
        },
      },
    },
    server: {
      dev: {
        port: 3333,
        path: '/assets',
        include: includePaths
      }
    },
    build: {
      prod: {
        include: includePaths,
        files: ['application.css', 'application.js'],
        dest: 'webroot/assets'
      }
    },
    compass: {
      dist: {
        options: {
          sourcemap: true,
          outputStyle: 'compressed',
          sassDir: 'webroot/sass',
          cssDir: 'webroot/css',
          bundleExec: true,
          specify: [
            'webroot/sass/global.scss',
            'webroot/sass/public.scss',
            'webroot/sass/admin.scss',
            'webroot/sass/email.scss'
          ]
        }
      }
    },
    jspm: {
      options: {
        sfx: true,
        minify: true,
        mangle: true,
        sourceMaps: false,
      },
      dist: {
        files: {
          'webroot/js/main.js': ['js/src/**/*.js'],
        },
      }
    },
    scsslint: {
      allFiles: [
        'webroot/sass/*.scss',
      ],
      options: {
        config: '.scss-lint.yml',
        bundleExec: true,
      },
    },
    markdown: {
      all: {
        files: [
          {
            expand: true,
            cwd: 'widgets_src',
            src: '*.md',
            dest: 'webroot/widgets/',
            ext: '.html'
          }
        ]
      }
    },
    premailer: {
      email: {
        options: {
          verbose: false,
          bundleExec: true,
        },
        files: {
          'View/Layouts/Emails/html/default.ctp': ['View/Layouts/Emails/html/default.template.ctp']
        }
      }
    },
    'string-replace': {
      dist: {
        files: {
          'View/Layouts/Emails/html/default.ctp': 'View/Layouts/Emails/html/default.ctp',
        },
        options: {
          replacements: [{
            pattern: /<!--(.*)-->/g,
            replacement: '$1'
          }]
        }
      }
    },
    zip: {
      'chrome-app': {
        cwd: 'chrome_app/',
        src: ['chrome_app/*.js','chrome_app/*.json','chrome_app/*.html','chrome_app/*.png'],
        dest: 'chrome_app/apobox_scale.zip',
        compression: 'DEFLATE'
      }
    }
  });

  grunt.event.on('watch', function(action, filepath) {
    var CakeTestRunner = require('./Console/node/cake_test_runner'),
    file = new CakeTestRunner(filepath);

    if (fs.existsSync('.vagrant')) {  //@TODO: This doesn't work because the folder shows up inside the VM too.
      file.vagrantHost = true;
    }

    file.exists(function() { file.run(); });
  });

  grunt.registerMultiTask('server', 'Run a development asset compilation server.', function() {
    var assetServer = require('./Console/node/asset_server');
    assetServer(this.data.port, this.data.path, this.data.include);
  });

  grunt.registerMultiTask('build', 'Build assets to static files.', function() {
    var assetBuilder = require('./Console/node/asset_builder');
    assetBuilder(this.data.dest, this.data.files, this.data.include, this.async());
  });

  grunt.registerTask('test', 'Run the browser tests in command line', function(protocol, url) {
    var path = require('path'),
        runTests = require('./Console/node/run_tests');
    if (!protocol && !url) {
      url = 'localhost/' + path.basename(__dirname) + '/testjs';
    } else if (/http/.test(protocol)) {
      url = url.replace('//', '');
    } else {
      url = protocol;
    }
    runTests('http://' + url, this.async());
  });

  grunt.registerTask('app', ['zip:chrome-app']);
  grunt.registerTask('dev', ['server', 'watch']);
  grunt.registerTask('null', function() {});
  grunt.registerTask('css', ['scsslint', 'compass']);
  grunt.registerTask('email', ['premailer', 'string-replace']);
  grunt.registerTask('js', ['jspm']);
  grunt.registerTask('widget', ['inline', 'copy']);
  grunt.registerTask('default', ['css']);
};
