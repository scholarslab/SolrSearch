
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

  getTextNodes = (el) ->
    # jQuery(el).find(':not(iframe)').andSelf().contents().filter( ->
    jQuery(el).contents().filter( ->
      this.nodeType == 3
    )

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

  it 'should wrap the initial value in a new div.', ->
    div = jQuery(createDiv('initial text 2'))
    div.textinplace()

    texts = (n.nodeValue for n in getTextNodes(div)).join('')

    expect(texts).toBe('')
    expect(div.find('div.value').html()).toBe('initial text 2')

  it 'should add a .textinplace class to the container div.', ->
    div = jQuery(createDiv())
    div.textinplace()
    expect(div.hasClass('textinplace')).toBeTruthy()

  it 'should maintain existing classes on the container div.', ->
    div = jQuery(createDiv())
    div.addClass('something')
    div.textinplace()
    expect(div.hasClass('something')).toBeTruthy()


