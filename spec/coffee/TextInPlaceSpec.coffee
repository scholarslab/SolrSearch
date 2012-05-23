
describe 'TextInPlace widget', ->
  n     = 0
  todel = []

  # This creates a new div with a unique ID and adds it to the DOM. It queues
  # it to be destroyed after the current test.
  createDiv = (text='') ->
    n += 1
    div = jQuery "<div id='tip#{n}'>#{text}</div>"

    jQuery('body').append(div)
    todel.push div

    div

  afterEach ->
    jQuery(id).remove() for id in todel
    todel.length = 0

  it 'should create a hidden input element.', ->
    div = jQuery(createDiv())
    div.textinplace()

    expect(div.find('input[type="hidden"]').size()).toBe(1)

  it 'should create a hidden input element with a default name.', ->
    div = jQuery(createDiv())
    div.textinplace()
    expect(div.find('input[type="hidden"]').attr('name')).toBe(div.attr('id'))

  it 'should create a hidden input element with a custom name.', ->
    div = jQuery(createDiv())
    div.textinplace(
      form_name: 'customname'
    )
    expect(div.find('input[type="hidden"]').attr('name')).toBe('customname')

  it 'should create a hidden input element with the value of the div.', ->
    div = jQuery(createDiv('initial text'))
    div.textinplace()
    expect(div.find('input[type="hidden"]').val()).toBe('initial text')


