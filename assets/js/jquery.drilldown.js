/**
 * A simple jQuery plugin for creating animated drilldown menus.
 *
 * @name jQuery Drilldown
 * @version 1.0.0
 * @requires jQuery v1.7+
 * @author Aleksandras Nelkinas
 * @license [MIT]{@link http://opensource.org/licenses/mit-license.php}
 *
 * Copyright (c) 2015 Aleksandras Nelkinas
 */

;(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD
    define(['jquery'], factory);
  } else if (typeof exports === 'object') {
    // Node/CommonJS
    module.exports = factory(require('jquery'));
  } else {
    // Browser globals
    factory(jQuery);
  }
}(function ($, undefined) {

  'use strict';

  var pluginName = 'drilldown';
  var defaults = {
    event: 'click',
    selector: 'a',
    speed: 100,
    cssClass: {
      container: pluginName + '-container',
      root: pluginName + '-root',
      sub: pluginName + '-sub',
      back: pluginName + '-back'
    }
  };

  var Plugin = (function () {

    function Plugin(element, options) {
      var inst = this;

      this._name = pluginName;
      this._defaults = defaults;

      this.element = element;
      this.$element = $(element);

      this.options = $.extend({}, defaults, options);

      this._history = [];
      this._css = {
        float: 'left',
        width: null
      };

      this.$container = this.$element.find('.' + this.options.cssClass.container);

      this.$element.on(this.options.event + '.' + pluginName, this.options.selector, function (e) {
        handleAction.call(inst, e, $(this));
      });
    }

    Plugin.prototype = {

      /**
       * Destroys plugin instance.
       */
      destroy: function () {
        this.reset();

        this.$element.off(this.options.event + '.' + pluginName, this.options.selector);
      },

      /**
       * Resets drilldown to its initial state.
       */
      reset: function () {
        var $root;

        if (this._history.length) {
          $root = this._history[0];

          this.$container.empty().append($root);
          restoreState.call(this, $root);
        }

        this._history = [];
        this._css = {
          float: 'left',
          width: null
        };
      }

    };

    /**
     * Handles user action and decides whether or not and where to drill.
     *
     * @param {jQuery.Event} e
     * @param {jQuery}       $trigger
     * @private
     */
    function handleAction(e, $trigger) {
      var $next = $trigger.nextAll('.' + this.options.cssClass.sub);
      var preventDefault = true;

      if ($next.length) {
        down.call(this, $next);
      } else if ($trigger.closest('.' + this.options.cssClass.back).length) {
        up.call(this);
      } else {
        preventDefault = false;
      }

      if (preventDefault && $trigger.prop('tagName') === 'A') {
        e.preventDefault();
      }
    }

    /**
     * Drills down (deeper).
     *
     * @param {jQuery} $next
     * @private
     */
    function down($next) {
      if (!$next.length) {
        return;
      }

      this._css.width = this.$element.outerWidth();
      this.$container.width(this._css.width * 2);

      $next = $next.clone(true)
          .removeClass(this.options.cssClass.sub)
          .addClass(this.options.cssClass.root);

      this.$container.append($next);

      animateDrilling.call(this, -1 * this._css.width, function () {
        var $current = $next.prev();

        this._history.push($current.detach());

        restoreState.call(this, $next);
      }.bind(this));
    }

    /**
     * Drills up (back).
     *
     * @private
     */
    function up() {
      var $next = this._history.pop();

      this._css.width = this.$element.outerWidth();
      this.$container.width(this._css.width * 2);

      this.$container.prepend($next);

      animateDrilling.call(this, 0, function () {
        var $current = $next.next();

        $current.remove();

        restoreState.call(this, $next);
      }.bind(this));
    }

    /**
     * Animates drilling process.
     *
     * @param {Number}   marginLeft Target margin-left.
     * @param {Function} callback
     * @private
     */
    function animateDrilling(marginLeft, callback) {
      var $menus = this.$container.children('.' + this.options.cssClass.root);

      $menus.css(this._css);

      $menus.first().animate({
        marginLeft: marginLeft
      }, this.options.speed, callback);
    }

    /**
     * Restores initial menu's state.
     *
     * @param {jQuery} $menu
     * @private
     */
    function restoreState($menu) {
      $menu.css({
        float: '',
        width: '',
        marginLeft: ''
      });

      this.$container.css('width', '');
    }

    return Plugin;

  })();

  $.fn[pluginName] = function (options) {
    return this.each(function () {
      var inst = $.data(this, pluginName);
      var method = options;

      if (!inst) {
        $.data(this, pluginName, new Plugin(this, options));
      } else if (typeof method === 'string') {
        if (method === 'destroy') {
          $.removeData(this,  pluginName);
        }
        if (typeof inst[method] === 'function') {
          inst[method]();
        }
      }
    });
  };

}));
