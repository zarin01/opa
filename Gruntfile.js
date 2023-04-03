const sass = require("node-sass");
var local_host = '';

module.exports = function(grunt) {

    const sass = require('node-sass'); //this part

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        sass: {
            dist: {
                options: {
                    style: 'nested',
                    implementation: sass
                },
                files: {
                    'assets/css/app.css': 'assets/scss/app.scss'
                }
            },
            admin: {
                options: {
                    style: 'nested',
                    implementation: sass
                },
                files: {
                    'admin/assets/css/app.css': 'admin/assets/scss/app.scss'
                }
            }
        },

        watch: {
            sass: {
                files: '**/*.scss',
                tasks: ['sass','autoprefixer','cssmin']
            }
        },

        autoprefixer: {
            options: {
                browsers: ['last 2 version', 'ie 8', 'ie 9']
            },
            pub: {
                src: 'assets/css/app.css'
            }
        },

        cssmin: {
            options: {
                shorthandCompacting: false,
                roundingPrecision: -1
            },
            dist: {
                files: {
                    'assets/css/app-min.css': ['assets/css/app.css']
                }
            },
            admin: {
                files: {
                    'admin/assets/css/app.min.css': ['admin/assets/css/app.css']
                }
            }
        },

        browserSync: {
            dev: {
                bsFiles: {
                    src : [
                        '**/*.php',
                        '**/*/{png,jpg.gif}',
                        '**/*.css',
                    ]
                },
                options: {
                    proxy: local_host,
                    watchTask: true
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-autoprefixer');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-browser-sync');

    grunt.registerTask('default', ['sass','autoprefixer','cssmin']);

    grunt.registerTask('dev', [
        'browserSync',
        'watch'
    ]);

};
