# ./Guardfile
#
# More info at https://github.com/guard/guard#readme

guard 'shell' do
    watch(/.*\.js/) do
      `cake build:browser`
    end

  watch(%r{^/_sass/.*\.s[ac]ss}) do
    `compass compile`
  end

end

guard 'livereload' do
  watch(%r{.+\.(css|js|html?|php|inc)$})
end
