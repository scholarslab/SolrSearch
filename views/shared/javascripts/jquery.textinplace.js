(function() {

  (function($) {
    return $.widget('solrsearch.textinplace', {
      options: {
        form_name: null
      },
      _create: function() {},
      _setOption: function(key, val) {
        return $.Widget.prototype._setOption.apply(this, arguments);
      },
      destroy: function() {
        return $.Widget.prototype.destroy.call(this);
      },
      _destroy: function() {}
    });
  })(jQuery);

}).call(this);
