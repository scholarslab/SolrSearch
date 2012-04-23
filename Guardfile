# ./Guardfile
#
# More info at https://github.com/guard/guard#readme

group :frontend do

    guard 'compass' do
      watch(%r{^/_sass/(.*)\.s[ac]ss})
    end

  guard 'shell' do
    watch(/.*\.js/) do
      `cake build:browser`
    end
  end

  guard 'livereload' do
    watch(%r{.+\.(css|js|html?|php|inc)$})
  end
end


