(function() {

  (function($) {
    return $.widget('solrsearch.textinplace', {
      options: {
        form_name: null
      },
      _create: function() {
        var form_name, text;
        this.element.addClass('textinplace');
        form_name = this._initFormName();
        text = this.element.html();
        this.element.html('');
        this.hidden = $("<input type='hidden' name='" + form_name + "' value='" + text + "' />");
        this.div = $("<div class='value'>" + text + "</div>");
        this.text = null;
        this.element.append(this.hidden);
        this.element.append(this.div);
        return this._bindEvents();
      },
      _setOption: function(key, val) {
        return $.Widget.prototype._setOption.apply(this, arguments);
      },
      destroy: function() {
        this._destroy();
        return $.Widget.prototype.destroy.call(this);
      },
      _destroy: function() {},
      _initFormName: function() {
        var _base, _ref;
        return (_ref = (_base = this.options).form_name) != null ? _ref : _base.form_name = this.element.attr('id');
      },
      _escape: function(input) {
        return input.replace(/\W/, '_').replace(/_+/, '_');
      },
      _bindEvents: function() {
        var _this = this;
        return this.div.on('click', function(ev) {
          return _this._click(ev);
        });
      },
      _click: function() {
        this.div.hide();
        if (this.text == null) {
          this.text = this._createTextInput();
        }
        return this.text.show();
      },
      _createTextInput: function() {
        var name, text, value,
          _this = this;
        name = this.options.form_name + "_text";
        value = this.hidden.val();
        text = $("<input type='text' name='" + name + "' value='" + value + "' />");
        this.element.append(text);
        text.on('blur', function(ev) {
          _this._textDone();
          return ev.preventDefault();
        });
        text.on('keyup', function(ev) {
          if (ev.key === 'Enter' || ev.keyCode === 13) {
            _this._textDone();
            return ev.preventDefault();
          }
        });
        return text;
      },
      _textDone: function() {
        var val;
        val = this.text.val();
        this.text.hide();
        this.div.html(val);
        this.hidden.attr('value', val);
        return this.div.show();
      }
    });
  })(jQuery);

}).call(this);
