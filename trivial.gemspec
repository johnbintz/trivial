# -*- encoding: utf-8 -*-

Gem::Specification.new do |s|
  s.name = %q{trivial}
  s.version = "0.0.1"

  s.required_rubygems_version = Gem::Requirement.new(">= 1.2") if s.respond_to? :required_rubygems_version=
  s.authors = ["John Bintz"]
  s.date = %q{2010-03-21}
  s.default_executable = %q{trivialize}
  s.description = %q{		For those who are using PHP to build their sites and want a very simple framework
		in which to organize their files, trivial is the solution. It's one PHP file
		that can include a few other pre-determined PHP and HTML files based on the
		request URI. This very simple division of content, actions (controllers), and
		views allows for multiple people to easily work on a smaller project without
		the overhead of a larger framework.
}
  s.email = %q{john@coswelproductions.com}
  s.executables = ["trivialize"]
  s.extra_rdoc_files = ["bin/trivialize", "lib/trivial.php"]
  s.files = ["Manifest", "Rakefile", "bin/trivialize", "dist/htaccess.dist", "lib/trivial.php", "trivial.gemspec"]
  s.homepage = %q{http://github.com/johnbintz/trivial}
  s.rdoc_options = ["--line-numbers", "--inline-source", "--title", "Trivial"]
  s.require_paths = ["lib"]
  s.rubyforge_project = %q{trivial}
  s.rubygems_version = %q{1.3.6}
  s.summary = %q{Ultra-lightweight website framework for PHP}

  if s.respond_to? :specification_version then
    current_version = Gem::Specification::CURRENT_SPECIFICATION_VERSION
    s.specification_version = 3

    if Gem::Version.new(Gem::RubyGemsVersion) >= Gem::Version.new('1.2.0') then
    else
    end
  else
  end
end
