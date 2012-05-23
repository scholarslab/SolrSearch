(function() {

  describe('TextInPlace widget', function() {
    var createDiv, getTextNodes, n, todel;
    n = 0;
    todel = [];
    createDiv = function(text) {
      var div;
      if (text == null) {
        text = '';
      }
      n += 1;
      div = jQuery("<div id='tip" + n + "'>" + text + "</div>");
      jQuery('body').append(div);
      todel.push(div);
      return div;
    };
    getTextNodes = function(el) {
      return jQuery(el).contents().filter(function() {
        return this.nodeType === 3;
      });
    };
    afterEach(function() {
      var id, _i, _len;
      for (_i = 0, _len = todel.length; _i < _len; _i++) {
        id = todel[_i];
        jQuery(id).remove();
      }
      return todel.length = 0;
    });
    it('should create a hidden input element.', function() {
      var div;
      div = jQuery(createDiv());
      div.textinplace();
      return expect(div.find('input[type="hidden"]').size()).toBe(1);
    });
    it('should create a hidden input element with a default name.', function() {
      var div;
      div = jQuery(createDiv());
      div.textinplace();
      return expect(div.find('input[type="hidden"]').attr('name')).toBe(div.attr('id'));
    });
    it('should create a hidden input element with a custom name.', function() {
      var div;
      div = jQuery(createDiv());
      div.textinplace({
        form_name: 'customname'
      });
      return expect(div.find('input[type="hidden"]').attr('name')).toBe('customname');
    });
    it('should create a hidden input element with the value of the div.', function() {
      var div;
      div = jQuery(createDiv('initial text'));
      div.textinplace();
      return expect(div.find('input[type="hidden"]').val()).toBe('initial text');
    });
    it('should wrap the initial value in a new div.', function() {
      var div, n, texts;
      div = jQuery(createDiv('initial text 2'));
      div.textinplace();
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
      expect(texts).toBe('');
      return expect(div.find('div.value').html()).toBe('initial text 2');
    });
    it('should add a .textinplace class to the container div.', function() {
      var div;
      div = jQuery(createDiv());
      div.textinplace();
      return expect(div.hasClass('textinplace')).toBeTruthy();
    });
    return it('should maintain existing classes on the container div.', function() {
      var div;
      div = jQuery(createDiv());
      div.addClass('something');
      div.textinplace();
      return expect(div.hasClass('something')).toBeTruthy();
    });
  });

}).call(this);
