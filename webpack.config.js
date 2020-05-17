const path = require('path');

const config = {
	mode: 'development',
	// context: path.join(__dirname, 'ts'),
	entry: {
		// 'php-support': './ts/main.js',
		'ml': './ml/mobilenet.npm.js',
	},
	output: {
		// path: path.join(__dirname, 'www'),
		path: path.join(__dirname, 'ml'),
		filename: 'mobilenet.bundle.js',
	},
	module: {},
	resolveLoader: {},
	resolve: {},
};
module.exports = config;
