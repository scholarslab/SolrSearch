(function() {

  (function($) {
    return $.widget('solrsearch.textinplace', {
      options: {
        form_name: null,
        revert_to: null
      },
      _create: function() {
        var form_name, revert_to, text;
        this.element.addClass('textinplace');
        form_name = this._initFormName();
        text = this.element.html();
        revert_to = this.options.revert_to;
        if (revert_to == null) {
          revert_to = text;
        }
        this.element.html('');
        this.hidden = $("<input type='hidden' name='" + form_name + "' value='" + text + "'\n       data-revertto='" + revert_to + "'\n       />");
        this.div = $("<div class='valuewrap'>\n  <span class='value'>" + text + "</span>\n  <span class='icons'>\n    <i class='icon-pencil'></i>\n    <i class='icon-repeat'></i>\n  </span>\n</div>");
        this.text = null;
        this.element.append(this.hidden);
        this.element.append(this.div);
        return this._bindEvents();
      },
      _setOption: function(key, val) {
        switch (key) {
          case 'revert_to':
            this.hidden.attr('data-revertto', val);
        }
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
        }).find('.icon-repeat').click(function(ev) {
          _this._revert();
          return ev.stopPropagation();
        });
      },
      _click: function() {
        this.div.hide();
        if (this.text == null) {
          this.text = this._createTextInput();
        }
        this.text.show();
        return this.text.focus();
      },
      _revert: function() {
        var value;
        value = this.hidden.attr('data-revertto');
        return this._setValue(value);
      },
      _createTextInput: function() {
        var name, text, value,
          _this = this;
        name = this.options.form_name + "_text";
        value = this.hidden.val();
        text = $("<input type='text' name='" + name + "' value='" + value + "' form='' />");
        this.element.append(text);
        text.blur(function(ev) {
          return _this._textDone();
        });
        text.keyup(function(ev) {
          if (ev.key === 'Enter' || ev.keyCode === 13) {
            _this._textDone();
            ev.preventDefault();
            ev.stopImmediatePropagation();
            return ev.stopPropagation();
          }
        });
        return text;
      },
      _textDone: function() {
        this.text.hide();
        this._setValue(this.text.val());
        return this.div.show();
      },
      _setValue: function(value) {
        var _ref;
        jQuery('.value', this.div).html(value);
        if ((_ref = this.text) != null) {
          _ref.val(value);
        }
        return this.hidden.attr('value', value);
      }
    });
  })(jQuery);

}).call(this);
