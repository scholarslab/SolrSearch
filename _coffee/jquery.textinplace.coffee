( ($) ->

  $.widget 'solrsearch.textinplace',
    options: {
      form_name: null
    }

    _create: ->
      text = this.element.html()
      form_name = this._initFormName()

      input = """
        <input type='hidden' name='#{form_name}' value='#{text}' />
        """

      this.element.append(input)

    _setOption: (key, val) ->
      # switch key
      #   when 'form_name' then

      # for jQuery UI <= 1.8
      $.Widget.prototype._setOption.apply this, arguments
      # for jQuery UI >= 1.9
      # this._super '_setOption', key, val

    destroy: () ->
      # for jQuery UI <= 1.8
      $.Widget.prototype.destroy.call this

    # for jQuery UI >= 1.9
    _destroy: () ->

    _initFormName: () ->
      this.options.form_name ?= this._escape(this.element.attr('id'))

    # This escapes the input by replacing all non-alphanumeric characters with
    # underscores and by normalizing sequences of underscores.
    _escape: (input) ->
      input.replace(/\W/, '_').replace(/_+/, '_')

)(jQuery)

