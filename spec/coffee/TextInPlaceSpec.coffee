
describe 'TextInPlace widget', ->
  n     = 0
  todel = []

  ## Some utilities.

  # This creates a new div with a unique ID and adds it to the DOM. It queues
  # it to be destroyed after the current test.
  createDiv = (text='', options={}, setup=null) ->
    div = jQuery "<div id='tip#{n}'>#{text}</div>"

    jQuery('body').append(div)
    todel.push div

    setup(div) if setup?
    jQuery(div).textinplace(options)

  getTextNodes = (el) ->
    # jQuery(el).find(':not(iframe)').andSelf().contents().filter( ->
    jQuery(el).contents().filter( ->
      this.nodeType == 3
    )

  triggerEditing = (div) ->
    val = div.find 'div.value'
    val.each ->
      jQuery(this).click()
    val

  editBlur = (div, text) ->
    input = div.find ':text'
    input.val text
    input.each -> jQuery(this).blur()
    input

  ## Before- and after-handlers.

  beforeEach ->
    n += 1

  afterEach ->
    jQuery(id).remove() for id in todel
    todel.length = 0

  ## And finally, the specs themselves.

  it 'should create a hidden input element.', ->
    div = createDiv()
    expect(div.find('input[type="hidden"]').size()).toBe(1)

  it 'should create a hidden input element with a default name.', ->
    div = createDiv()
    expect(div.find('input[type="hidden"]').attr('name')).toBe(div.attr('id'))

  it 'should create a hidden input element with a custom name.', ->
    div = createDiv('', { form_name: 'customname' })
    expect(div.find('input[type="hidden"]').attr('name')).toBe('customname')

  it 'should create a hidden input element with the value of the div.', ->
    div = createDiv("initial text #{n}")
    expect(div.find('input[type="hidden"]').val()).toBe("initial text #{n}")

  it 'should remove the value from the wrapper div.', ->
    div = createDiv("initial text #{n}")
    texts = (n.nodeValue for n in getTextNodes(div)).join('')
    expect(texts).toBe('')

  it 'should wrap the initial value in a new div.', ->
    div = createDiv("initial text #{n}")
    expect(div.find('div.value').html()).toBe("initial text #{n}")

  it 'should add a .textinplace class to the container div.', ->
    div = createDiv()
    expect(div.hasClass('textinplace')).toBeTruthy()

  it 'should maintain existing classes on the container div.', ->
    div = createDiv('', {}, (d) -> d.addClass('something'))
    expect(div.hasClass('something')).toBeTruthy()

  it "should hide the value div it's clicked on.", ->
    div = createDiv("initial text #{n}")
    text = triggerEditing div
    expect(text.is ':visible').toBeFalsy()

  it 'should make a visible text input when the value div is clicked on.', ->
    div = createDiv("initial text #{n}")
    triggerEditing div
    text = div.find(':text')
    expect(text.size()).toBe 1
    expect(text.val() ).toBe "initial text #{n}"

  it 'should hide the text input when it loses focus.', ->
    div = createDiv("initial text #{n}")
    triggerEditing div
    text = editBlur div, ''
    expect(text.is(':visible')).toBeFalsy()

  it 'should update the hidden input when the text input loses focus.', ->
    div = createDiv("initial text #{n}")
    triggerEditing div
    editBlur div, "updated text #{n}"
    expect(div.find('input[type="hidden"]').attr('value')).toBe("updated text #{n}")

  it 'should update the visible div when the text input loses focus.', ->
    div = createDiv("initial text #{n}")
    triggerEditing div
    editBlur div, "updated text #{n}"
    expect(div.find('div.value').html()).toBe("updated text #{n}")

  it 'should show the value div when the text input loses focus.', ->
    div = createDiv("initial text #{n}")
    triggerEditing div
    editBlur div, "updated text #{n}"
    expect(div.find('div.value').is(':visible')).toBeTruthy()



