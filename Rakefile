require 'rubygems'
require 'rake'
require 'echoe'
require 'fileutils'

Echoe.new('trivial') do |p|
	p.summary = "Ultra-lightweight website framework for PHP"
	p.description = <<-EOT
		For those who are using PHP to build their sites and want a very simple framework
		in which to organize their files, trivial is the solution. It's one PHP file
		that can include a few other pre-determined PHP and HTML files based on the
		request URI. This very simple division of content, actions (controllers), and
		views allows for multiple people to easily work on a smaller project without
		the overhead of a larger framework.
	EOT
	p.author = "John Bintz"
	p.email = "john@coswelproductions.com"
	p.url = "http://github.com/johnbintz/trivial"
end

namespace :blueprint do
  desc "Include the latest Blueprint CSS files"
  task :download do
    FileUtils.rm_r 'blueprint' if File.directory? 'blueprint'
    FileUtils.mkdir 'blueprint'
    Dir.chdir 'blueprint'
    system 'git clone git://github.com/joshuaclayton/blueprint-css.git'
    FileUtils.cp_r File.join('blueprint-css', 'blueprint'), File.join('..', 'styles')
    FileUtils.cp File.join('blueprint-css', 'LICENSE'), File.join('..', 'styles', 'blueprint')
    Dir.chdir '..'
    FileUtils.rm_r 'blueprint'
  end
end

namespace :php do
  desc "Syntax check trivial.php"
  task :syntax_check do
    system %{php -l lib/trivial.php}
  end
end
