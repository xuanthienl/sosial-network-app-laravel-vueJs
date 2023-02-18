const { join, resolve } = require('path')
const mix = require('laravel-mix')
require('laravel-mix-versionhash')

mix.js('resources/js/app.js', 'public/js').vue({extractStyles: false})
   .sass('resources/sass/app.scss', 'public/css')
   .disableNotifications()

if (mix.inProduction()) {
	mix.versionHash()
} else {
	mix.sourceMaps()
}

mix.webpackConfig({
	plugins: [

	],
	resolve: {
		extensions: ['.js', '.json', '.vue'],
		alias: {
			'~': join(__dirname, './resources/js')
		}
	},
	output: {
		chunkFilename: 'js/[chunkhash].js',
		path: resolve(__dirname, './public')
	}
})