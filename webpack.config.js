let webpack = require('webpack');

const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const { EsbuildPlugin } = require('esbuild-loader');
const CopyPlugin = require("copy-webpack-plugin");
const HTMLWebpackPlugin = require('html-webpack-plugin');
const ESLintPlugin = require('eslint-webpack-plugin');

const path = require('path');

const mode = process.env.NODE_ENV || 'development';
const prod = mode === 'production';
const drop_console = false;
console.log( 'svelte production mode: ' + prod );

module.exports = {
	entry: {
		'bundle': ['./src/main.js']
	},
    resolve: {
        alias: {
            svelte: path.resolve('node_modules', 'svelte/src/runtime')
        },
        extensions:  ['.mjs', '.js', '.svelte' ],
        mainFields: [ 'svelte', 'browser', 'module', 'main' ],
        conditionNames: [ 'svelte', 'browser', 'require', 'import' ]
    },
	output: {
		path: path.join(__dirname, '/public'),
		filename: '[name]-[fullhash].js',
		chunkFilename: '[name]-[fullhash].[id].js',
		clean: true
	},
	module: {
		rules: [
			{
			  test: /\.m?js/,
			  resolve: {
			    fullySpecified: false,
			  },
			},
			{
				test: /\.svelte$/,
				use: {
					loader: 'svelte-loader',
					options: {
						compilerOptions: {
							dev: !prod
						},
						emitCss: prod,
						hotReload: !prod,
						onwarn(warning, onwarn) {
  							if (!/A11y:/.test(warning.message)) {
								onwarn(warning);
							}
						},
					}
				}
			},
			{
				test: /\.css$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
						options: {
							publicPath: ''
						}
					},
					{ loader: 'css-loader' }
				]
			},
			{
				// required to prevent errors from Svelte on Webpack 5+
				test: /node_modules\/svelte\/.*\.mjs$/,
				resolve: {
					fullySpecified: false
				}
			},
			{
				test: /\.(png|svg|jpg|gif)$/,
				type: 'asset/inline'
			},
			{
        test: /\.(woff(2)?|ttf|eot|otf)(\?v=\d+\.\d+\.\d+)?$/,
				type: 'asset/inline'
      }
		]
	},
    optimization: {
        runtimeChunk: 'single',
	    splitChunks: {
    	  chunks: 'all',
		  minSize: 500000,
	      cacheGroups: {
        	vendor: {
    	      test: /[\\/]node_modules[\\/]/, // targets modules in node_modules directory
	          name: 'vendors', // names the resulting chunk 'vendors'
    	      chunks: 'all',
        	},
	      },
    	},
        minimize: !!prod,
        minimizer: [
            new EsbuildPlugin({
                target: 'es2015',
                css: true,
                exclude: /(worker\.20250613\.js|exceljs\.min\.20250613\.js|polyfill\.20250613\.js)/,
                drop: ( !!prod && drop_console ) ? ['console', 'debugger'] : [],
            })
        ]
    },
	mode,
	plugins: [
		new ESLintPlugin(),
		new webpack.DefinePlugin({
			GAMEMODE: JSON.stringify('SINGLE')
		}),
		new MiniCssExtractPlugin({
			filename: '[name]-[fullhash].css'
		}),
		new CopyPlugin({
			patterns: [
				{
					from: "**/*",
					to: path.join(__dirname, '/public/'),
					context: 'templates/',
		            globOptions: {
            			ignore: [
			              "**/index.php"
            			],
          			}
        		},
				{
					from: "*service/index.php",
					to: path.join(__dirname, '/public/service/index.php'),
					context: 'templates/'
        		},
				{
					from: "*graphql/index.php",
					to: path.join(__dirname, '/public/graphql/index.php'),
					context: 'templates/'
        		}
			]
		}),
        new HTMLWebpackPlugin({
        	template: path.join(__dirname, './templates/index.php'),
			filename: path.join(__dirname, './public/index.php'),
			minify: false,
			inject: 'head'
        })
	],
	devtool: prod ? false : 'source-map',
	devServer: {
		hot: false,
		static: {
			directory: path.join(__dirname, 'src'),
		},
		open: {
			target: ['http://127.0.0.1:8080/index.html'],
			app: {
        		name: 'chromium-browser',
				arguments: ['--new-window']
			}
		},
		port: 8080,
	    compress: false
	},
	performance: {
    	maxEntrypointSize: 200048000,
    	maxAssetSize: 200048000
	}
};