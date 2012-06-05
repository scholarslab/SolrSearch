( ($) ->

  $.widget 'solrsearch.textinplace',
    options: {
      form_name: null
    }

    _create: ->
      @element.addClass('textinplace')
      form_name = this._initFormName()
      text = @element.html()
      @element.html('')

      @hidden = $("""
        <input type='hidden' name='#{form_name}' value='#{text}' />
        """)
      @div    = $("""
        <div class='valuewrap'>
          <span class='value'>#{text}</span>
          <span class='icons'>
            <i class='icon-pencil'></i>
          </span>
        </div>
        """)
      @text   = null

      @element.append @hidden
      @element.append @div

      this._bindEvents()

    _setOption: (key, val) ->
      # switch key
      #   when 'form_name' then

      # for jQuery UI <= 1.8
      $.Widget.prototype._setOption.apply this, arguments
      # for jQuery UI >= 1.9
      # this._super '_setOption', key, val

    destroy: ->
      this._destroy()
      # for jQuery UI <= 1.8
      $.Widget.prototype.destroy.call this

    # for jQuery UI >= 1.9
    _destroy: ->

    _initFormName: ->
      @options.form_name ?= @element.attr('id')

    # This escapes the input by replacing all non-alphanumeric characters with
    # underscores and by normalizing sequences of underscores.
    _escape: (input) ->
      input.replace(/\W/, '_').replace(/_+/, '_')

    _bindEvents: ->
      @div.on('click', (ev) => this._click(ev))

    _click: ->
      @div.hide()
      @text ?= this._createTextInput()
      @text.show()
      @text.focus()

    _createTextInput: ->
      name  = @options.form_name + "_text"
      value = @hidden.val()

      text = $("""
        <input type='text' name='#{name}' value='#{value}' form='' />
        """)
      @element.append text

      # TODO: preventDefault does not stop 'Enter' from triggering form
      # submission.

      text.blur (ev) =>
        this._textDone()
      text.keyup (ev) =>
        if ev.key == 'Enter' || ev.keyCode == 13
          this._textDone()
          ev.preventDefault()
          ev.stopImmediatePropagation()
          ev.stopPropagation()

      text

    _textDone: ->
      val = @text.val()
      @text.hide()
      jQuery('.value', @div).html val
      @hidden.attr 'value', val
      @div.show()

)(jQuery)

