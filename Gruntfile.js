//The wrapper function
module.exports = function (grunt) {
    'use strict';
    // Project configuration & task configuration   
    grunt.initConfig({
        // Generate POT files.
        makepot: {
            target: {
                options: {
                    processPot: function (pot) {
                        pot.headers['language-team'] = 'Mladjo <mladen@milentijevic.com>';
                        var translation,
                                excluded_meta = [
                                    'Plugin Name of the plugin/theme',
                                    'Plugin URI of the plugin/theme',
                                    'Description of the plugin/theme',
                                    'Author of the plugin/theme',
                                    'Author URI of the plugin/theme',
                                    'Tags of the plugin/theme'
                                ];

                        for (translation in pot.translations['']) {
                            if ('undefined' !== typeof pot.translations[''][ translation ].comments.extracted) {
                                if (excluded_meta.indexOf(pot.translations[''][ translation ].comments.extracted) >= 0) {
                                    console.log('Excluded meta: ' + pot.translations[''][ translation ].comments.extracted);
                                    delete pot.translations[''][ translation ];
                                }
                            }
                        }

                        return pot;
                    },
                    type: 'wp-plugin',
                    domainPath: 'languages', // Where to save the POT file.          
                }
            }
        }

    })

    grunt.loadNpmTasks('grunt-wp-i18n');
};