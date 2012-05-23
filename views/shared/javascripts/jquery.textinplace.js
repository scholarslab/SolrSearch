(function() {

  (function($) {
    return $.widget('solrsearch.textinplace', {
      options: {
        form_name: null
      },
      _create: function() {
        var div, form_name, input, text;
        this.element.addClass('textinplace');
        form_name = this._initFormName();
        text = this.element.html();
        this.element.html('');
        input = "<input type='hidden' name='" + form_name + "' value='" + text + "' />";
        div = "<div class='value'>" + text + "</div>";
        return this.element.append(input + div);
      },
      _setOption: function(key, val) {
        return $.Widget.prototype._setOption.apply(this, arguments);
      },
      destroy: function() {
        return $.Widget.prototype.destroy.call(this);
      },
      _destroy: function() {},
      _initFormName: function() {
        var _base, _ref;
        return (_ref = (_base = this.options).form_name) != null ? _ref : _base.form_name = this._escape(this.element.attr('id'));
      },
      _escape: function(input) {
        return input.replace(/\W/, '_').replace(/_+/, '_');
      }
    });
  })(jQuery);

}).call(this);
