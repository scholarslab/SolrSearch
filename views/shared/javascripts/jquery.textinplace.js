(function() {

  (function($) {
    return $.widget('solrsearch.textinplace', {
      options: {
        form_name: null
      },
      _create: function() {
        var form_name, input, text;
        text = this.element.html();
        form_name = this._initFormName();
        input = "<input type='hidden' name='" + form_name + "' value='" + text + "' />";
        return this.element.append(input);
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
