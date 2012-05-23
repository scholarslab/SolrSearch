(function() {

  describe('In the TextInPlace widget,', function() {
    var createDiv, editBlur, getTextNodes, n, todel, triggerEditing;
    n = 0;
    todel = [];
    createDiv = function(text, options, setup) {
      var div;
      if (text == null) {
        text = '';
      }
      if (options == null) {
        options = {};
      }
      if (setup == null) {
        setup = null;
      }
      div = jQuery("<div id='tip" + n + "'>" + text + "</div>");
      jQuery('body').append(div);
      todel.push(div);
      if (setup != null) {
        setup(div);
      }
      return jQuery(div).textinplace(options);
    };
    getTextNodes = function(el) {
      return jQuery(el).contents().filter(function() {
        return this.nodeType === 3;
      });
    };
    triggerEditing = function(div) {
      var val;
      val = div.find('div.value');
      val.each(function() {
        return jQuery(this).click();
      });
      return val;
    };
    editBlur = function(div, text) {
      var input;
      input = div.find(':text');
      input.val(text);
      input.each(function() {
        return jQuery(this).blur();
      });
      return input;
    };
    beforeEach(function() {
      return n += 1;
    });
    afterEach(function() {
      var id, _i, _len;
      for (_i = 0, _len = todel.length; _i < _len; _i++) {
        id = todel[_i];
        jQuery(id).remove();
      }
      return todel.length = 0;
    });
    describe('the container div', function() {
      it('should have no contents;', function() {
        var div, n, texts;
        div = createDiv("initial text " + n);
        texts = ((function() {
          var _i, _len, _ref, _results;
          _ref = getTextNodes(div);
          _results = [];
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            n = _ref[_i];
            _results.push(n.nodeValue);
          }
          return _results;
        })()).join('');
        return expect(texts).toBe('');
      });
      it('should add a .textinplace class to the container div;', function() {
        var div;
        div = createDiv();
        return expect(div.hasClass('textinplace')).toBeTruthy();
      });
      return it('should maintain existing classes on the container div;', function() {
        var div;
        div = createDiv('', {}, function(d) {
          return d.addClass('something');
        });
        return expect(div.hasClass('something')).toBeTruthy();
      });
    });
    describe('the hidden input element', function() {
      it('should be created;', function() {
        var div;
        div = createDiv();
        return expect(div.find('input[type="hidden"]').size()).toBe(1);
      });
      it('should have a default name;', function() {
        var div;
        div = createDiv();
        return expect(div.find('input[type="hidden"]').attr('name')).toBe(div.attr('id'));
      });
      it('should use a custom name;', function() {
        var div;
        div = createDiv('', {
          form_name: 'customname'
        });
        return expect(div.find('input[type="hidden"]').attr('name')).toBe('customname');
      });
      return it('should have the value of the initial div;', function() {
        var div;
        div = createDiv("initial text " + n);
        return expect(div.find('input[type="hidden"]').val()).toBe("initial text " + n);
      });
    });
    describe('the visible div', function() {
      return it('should wrap the initial value;', function() {
        var div;
        div = createDiv("initial text " + n);
        return expect(div.find('div.value').html()).toBe("initial text " + n);
      });
    });
    return describe('the text input', function() {
      it("should hide the value div it's clicked on.", function() {
        var div, text;
        div = createDiv("initial text " + n);
        text = triggerEditing(div);
        return expect(text.is(':visible')).toBeFalsy();
      });
      it('should make a visible text input when the value div is clicked on;', function() {
        var div, text;
        div = createDiv("initial text " + n);
        triggerEditing(div);
        text = div.find(':text');
        expect(text.size()).toBe(1);
        return expect(text.val()).toBe("initial text " + n);
      });
      it('should hide the text input when it loses focus;', function() {
        var div, text;
        div = createDiv("initial text " + n);
        triggerEditing(div);
        text = editBlur(div, '');
        return expect(text.is(':visible')).toBeFalsy();
      });
      it('should update the hidden input when the text input loses focus;', function() {
        var div;
        div = createDiv("initial text " + n);
        triggerEditing(div);
        editBlur(div, "updated text " + n);
        return expect(div.find('input[type="hidden"]').attr('value')).toBe("updated text " + n);
      });
      it('should update the visible div when the text input loses focus;', function() {
        var div;
        div = createDiv("initial text " + n);
        triggerEditing(div);
        editBlur(div, "updated text " + n);
        return expect(div.find('div.value').html()).toBe("updated text " + n);
      });
      return it('should show the value div when the text input loses focus.', function() {
        var div;
        div = createDiv("initial text " + n);
        triggerEditing(div);
        editBlur(div, "updated text " + n);
        return expect(div.find('div.value').is(':visible')).toBeTruthy();
      });
    });
  });

}).call(this);
