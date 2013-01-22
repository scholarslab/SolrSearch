fs = require 'fs'
util = require 'util'
ini = require 'inireader'

files = [
  './views/shared/javascripts/facets.js',
  # './views/shared/javascripts/jquery.infinitescroll.js',
  './views/shared/javascripts/scroll.js'
]

plugin_file = './plugin.ini'
parser = new ini.IniReader()
parser.load(plugin_file)

application_name = parser.param('info.name')
version = parser.param('info.version')

builddir = './views/shared/javascripts'
targetfile = "#{application_name}-#{version}"

task 'build:browser', 'Compile and minify for use in browser', ->
	util.log "Creating browser file for #{application_name} version #{version}."
	contents = new Array
	remaining = files.length
	for file, index in files
		do (file, index) ->
			fs.readFile file, 'utf8', (err, cnt) ->
				util.log err if err
				contents[index] = cnt

				util.log "[#{index + 1}/#{files.length}] #{file}"

				process() if --remaining is 0
	process = ->
		util.log "Creating #{builddir}/#{targetfile}.js"

		code = contents.join "\n\n"
		fs.unlink builddir, ->
			fs.mkdir builddir, 0o0755, ->
				fs.writeFile "#{builddir}/#{targetfile}.js", code, 'utf8', (err) ->
					console.log err if err
					try
						util.log "Creating #{builddir}/#{targetfile}.min.js"
						{parser, uglify} = require 'uglify-js'
						ast = parser.parse code
						code = uglify.gen_code uglify.ast_squeeze uglify.ast_mangle ast, extra: yes
						fs.writeFile "#{builddir}/#{targetfile}-min.js", code

task 'build', 'Compile', ->
  invoke 'build:browser'


