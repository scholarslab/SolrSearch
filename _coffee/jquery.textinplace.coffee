( ($) ->

  $.widget 'solrsearch.textinplace',
    options: {
      form_name: null
    }

    _create: ->

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

)(jQuery)

