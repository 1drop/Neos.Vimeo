# Require any additional compass plugins here.

# Tell compass where to find local extensions
# If you followed directions and ran 'gem install modular-scale' comment the next two lines out:
#extensions_dir = "Private/sass/extensions"
#Compass::Frameworks.register('modular-scale', :path => File.expand_path("#{extensions_dir}/Private/sass/modular-scale"))

# Uncomment these to use regular Ruby gems.
require 'modular-scale'
require 'sassy-math'

# Set this to the root of your project when deployed:
http_path = "/"
css_dir = "Public/Stylesheet"
sass_dir = "Private/Sass"
images_dir = "Public/Images"
fonts_dir = "Public/Fonts"

# You can select your preferred output style here (can be overridden via the command line) :nested or :expanded or :compact or :compressed:
output_style = :expanded

# To enable relative paths to assets via compass helper functions. Uncomment:
relative_assets = true

# To disable debugging comments that display the original location of your selectors. Uncomment:
line_comments = false

# If you prefer the indented syntax, you might want to regenerate this
# project again passing --syntax sass, or you can uncomment this:
# preferred_syntax = :sass
# and then run:
# sass-convert -R --from scss --to sass sass scss && rm -rf sass && mv scss sass

#sourcemaps
sass_options = {:sourcemap => true}
enable_sourcemaps = true