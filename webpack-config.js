// Require path.
const path = require( 'path' );

// Configuration object.
const config = {
	mode: 'development',
	entry: {
		production: [
			'./assets/js/app/app.js',
			'./assets/scss/app.scss'
		]
	},
	output: {
		path: path.resolve(__dirname, './assets/dist/'),
		filename: 'app.min.js',
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loader: 'babel-loader',
				options: {
					presets: ["@babel/preset-env"]
				}
			},
			{
				test: /\.css$/,
				use: ['css-loader']
			},
			{
				test: /\.scss$/,
				include: [
					path.resolve(__dirname, "./assets/scss")
				],
				exclude: [
					'/node_modules/'
				],
				use: [
					{
						loader: 'file-loader',
						options: { outputPath: './', name: '[name].min.css'}
					},
					{
						loader: "sass-loader",
						options: {
							implementation: require("sass")
						}
					}
				]
			},
			{
				test: /\.scss$/,
				include: [],
				exclude: /node_modules/,
				use: [{
					loader: "style-loader"
				}, {
					loader: "css-loader"
				}, {
					loader: "sass-loader",
					options: {
						implementation: require("sass")
					}
				}]
			},
			{
				test: /\.(eot|ttf|woff|woff2)$/i,
				use: {
					loader: 'url-loader',
					options: {
						name: "[name]-[hash].[ext]"
					}
				}
			},
			{
				test: /\.(jpg|png|svg|gif)$/i,
				use: {
					loader: 'url-loader',
					options: {
						name: "[name]-[hash].[ext]"
					}
				}
			}
		]
	},
	optimization: {
		minimize: false
	}
};

const adminConfig = {
	mode: 'development',
	entry: {
		production: [
			'./admin/assets/scss/admin.scss'
		]
	},
	output: {
		path: path.resolve(__dirname, './admin/assets/dist/'),
		filename: 'admin.min.js',
	},
	module: {
		rules: [
			{
				test: /\.css$/,
				use: ['css-loader']
			},
			{
				test: /\.scss$/,
				include: [
					path.resolve(__dirname, "./admin/assets/scss")
				],
				exclude: [
					'/node_modules/'
				],
				use: [
					{
						loader: 'file-loader',
						options: { outputPath: './', name: '[name].min.css'}
					},
					{
						loader: "sass-loader",
						options: {
							implementation: require("sass")
						}
					}
				]
			},
			{
				test: /\.scss$/,
				include: [],
				exclude: /node_modules/,
				use: [{
					loader: "style-loader"
				}, {
					loader: "css-loader"
				}, {
					loader: "sass-loader",
					options: {
						implementation: require("sass")
					}
				}]
			},
			{
				test: /\.(eot|ttf|woff|woff2)$/i,
				use: {
					loader: 'url-loader',
					options: {
						name: "[name]-[hash].[ext]"
					}
				}
			},
			{
				test: /\.(jpg|png|svg|gif)$/i,
				use: {
					loader: 'url-loader',
					options: {
						name: "[name]-[hash].[ext]"
					}
				}
			}
		]
	},
	optimization: {
		minimize: false
	}
};

// Export the config object.
module.exports = [config, adminConfig];
