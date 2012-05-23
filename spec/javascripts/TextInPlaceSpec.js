(function() {

  describe('TextInPlace widget', function() {
    var createDiv, n, todel;
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
    return it('should create a hidden input element with the value of the div.', function() {
      var div;
      div = jQuery(createDiv('initial text'));
      div.textinplace();
      return expect(div.find('input[type="hidden"]').val()).toBe('initial text');
    });
  });

}).call(this);
