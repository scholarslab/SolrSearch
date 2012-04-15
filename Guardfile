# ./Guardfile
#
# More info at https://github.com/guard/guard#readme

group :frontend do

  if File.exists?('./config.rb')
    guard 'compass' do
      watch(%r{^/_sass/(.*)\.s[ac]ss})
    end
  end

  guard 'shell' do
    watch(/.*\.js/) do
      `cake build:browser`
    end
  end
end
