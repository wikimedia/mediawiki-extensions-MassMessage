/* eslint-env node, es6 */

module.exports = function ( grunt ) {
	var conf = grunt.file.readJSON( 'extension.json' );

	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		eslint: {
			all: [
				'modules/*.js',
				'!node_modules/**'
			]
		},
		banana: conf.MessagesDirs,
		jsonlint: {
			all: [
				'**/*.json',
				'.stylelintrc',
				'!node_modules/**'
			]
		},
		stylelint: {
			all: [
				'**/*.css',
				'!node_modules/**'
			]
		}
	} );

	grunt.registerTask( 'test', [ 'eslint', 'jsonlint', 'banana', 'stylelint' ] );
	grunt.registerTask( 'default', 'test' );
};
