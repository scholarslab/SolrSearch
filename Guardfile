group :frontend do
  guard 'compass' do
    watch('^_sass/(/.*/)\.s[ac]ss')
  end

  guard 'shell' do
    watch(/.*\.js/) do
      `cake build:browser`
    end
  end
end
