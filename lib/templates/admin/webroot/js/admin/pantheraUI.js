/**
  * Confirmation box
  *
  * @author Damian Kęska
  */
 
$(function() {
    $( document ).tooltip({
      position: {
        my: "center bottom-20",
        at: "center top",
        using: function( position, feedback ) {
          $( this ).css( position );
          $( "<div>" )
            .addClass( "arrow" )
            .addClass( feedback.vertical )
            .addClass( feedback.horizontal )
            .appendTo( this );
        }
      }
    });
});

panthera.confirmBox = function () {};
panthera.alertBox = function () {};

/**
  * Alias to panthera.confirmBox.create with isAlertBox argument set to True as default
  *
  * @param string text
  * @param function callback
  * @return mixed 
  * @author Damian Kęska
  */

panthera.alertBox.create = function (text, callback) {
    return panthera.confirmBox.create(text, callback, true);
}

/**
  * Create a confirmation box
  *
  * @author Damian Kęska
  */

panthera.confirmBox.create = function (text, callback, isAlertBox) {
    panthera.confirmBox.close();

    html = '<div id="popupQuestion" class="popupOverlay popupQuestionOverlay" style="z-index: 9999;">';
    html += '<div class="popupQuestion">';
    html += '<p class="popupHeader">'+text+'</p>';
    html += '<div class="separatorHorizontal"></div>';
    html += '<div style="margin: 20px;">';
    
    // alert box support
    if (isAlertBox)
    {
        html += '<input type="button" value="'+panthera.locale.get('Close')+'" style="float: right;" id="confirmBox_btn_close">';
    } else {
        html += '<input type="button" value="'+panthera.locale.get('No')+'" id="confirmBox_btn_no"> <input type="button" value="'+panthera.locale.get('Yes')+'" id="confirmBox_btn_yes" style="float: right;">';
    }
    
    html += '</div></div></div>';

    // append the code
    $('#titleBar').after(html);
    
    // now add callbacks
    if (isAlertBox)
    {
        // Button: Close
        $('#confirmBox_btn_close').click(function () {
            if (callback)
            {
                callback('Close');
            }
            
            panthera.confirmBox.close();
        });
        
    } else {
        // Button: No
        $('#confirmBox_btn_no').click(function () {
            if (callback)
            {
                callback('No');
            }
            
            panthera.confirmBox.close();
        });
        
        // Button: Yes
        $('#confirmBox_btn_yes').click(function () {
            if (callback)
            {
                callback('Yes');
            }
            
            panthera.confirmBox.close();
        });
    }
}

/**
  * Close a confirmation box
  *
  * @author Damian Kęska
  */

panthera.confirmBox.close = function () {
    $('#popupQuestion').remove();
}

panthera.alertBox.close = panthera.confirmBox.close;

/**
  * Compatibility with old w2ui interface
  *
  * @author Damian Kęska
  */

function w2confirm(msg, callback) { panthera.confirmBox.create(msg, callback) };

panthera.popup.slots = {};
panthera.popup.i = 0;

/**
  * Panthera UI popups
  *
  * @author Damian Kęska
  */

panthera.popup.create = function (link, slot) {
    if (slot && slot != '__default__' && !is_numeric(slot))
    {
        elementName = 'popupOverlay_'+slot;
    } else {
        slot = '__default__';
        elementName = 'popupOverlay';
    }

    panthera.logging.output('Creating popup with id='+elementName);

    if (!document.getElementById(elementName))
    {
        panthera.popup.i++;
        panthera.logging.output('Inserted #'+elementName+' right after #topContent');
        
        
        if (document.getElementById('topContent'))
        {
            $('#topContent').after('<div id="'+elementName+'" style="z-index: '+(100+panthera.popup.i)+';" class="popupOverlay"></div>');
        } else if (document.getElementById('ajax-content')) {
            $('#ajax-content').before('<div id="'+elementName+'" style="z-index: '+(100+panthera.popup.i)+';" class="popupOverlay"></div>');
        }
    }
    
    panthera.popup.restoreContent(slot);
    data = '';
    
    if (typeof link == "string") 
    {
        panthera.popup.slots[slot] = link;
        
        if (link.substr(0, 8) == 'element:')
        {
            panthera.logging.output('Getting content of popup source element, its '+link.replace('element:', ''));
            link = $(link.replace('element:', ''));
        }
    }

    // setting content by jQuery object (from content or value of other HTML tag)
    if (typeof link == "object")
    {
        panthera.popup.slots[slot] = 'element:#'+link.attr('id');
    
        if (link.val())
        {
            data = link.val();
            link.val('');
        } else {
            data = link.html();
            link.html('');
        }
        
    } 
    
    if (typeof link == "string")
    {
        panthera.logging.output('Got link: '+link);
    
        panthera.htmlGET({ url: link, success: function(data) {
                panthera.logging.output('Inserting ajax HTML result into #'+elementName);
                
		        $('#'+elementName).html(data);
			    $('#'+elementName).slideDown(200);
		    }
	    });
	    
	} else {
	    panthera.logging.output('Copying raw HTML code into #'+elementName);
	    $('#'+elementName).html(data);
        $('#'+elementName).slideDown(200);
	}

    return true;
}

/**
  * Close a popup
  *
  * @author Damian Kęska
  */

panthera.popup.close = function (slot) {
    if (slot && slot != '__default__')
    {
        elementName = 'popupOverlay_'+slot;
    } else {
        slot = '__default__';
        elementName = 'popupOverlay';
    }

    $('#'+elementName).slideUp(150);
    panthera.logging.output('Closing popup id=#'+elementName);
    panthera.popup.restoreContent(slot);
    panthera.popup.slots[slot] = false;
}

/**
  * Toggle popup opened/closed
  *
  * @author Damian Kęska
  */

panthera.popup.toggle = function (link, slot) {
    if (!slot)
    {
        slot = '__default__';
    }


    if (typeof link == "string") 
    {
        type = link;
    } else {
        type = $(link).attr('id');
    }
    
    console.log('Toggle Panthera popup, id:'+type+', previous id='+panthera.popup.slots[slot]);

    if (panthera.popup.slots[slot] && panthera.popup.slots[slot] == type)
    {
        panthera.popup.close(slot);
    } else {
        panthera.popup.create(link, slot);
    }
}

/**
  * Move popup's content back to original HTML tag
  *
  * @author Damian Kęska
  */

panthera.popup.restoreContent = function (slot) {
    if (slot && slot != '__default__')
    {
        elementName = 'popupOverlay_'+slot;
    } else {
        slot = '__default__';
        elementName = 'popupOverlay';
    }

    panthera.logging.output('Restoring content for id='+panthera.popup.slots[slot]+', slot='+slot);

    if (panthera.popup.slots[slot])
    {
        if (panthera.popup.slots[slot].substr(0, 8) == 'element:')
        {
            id = $(panthera.popup.slots[slot].replace('element:', ''));
            $(id).html($('#'+elementName).html());
            $('#'+elementName).html('');
            panthera.popup.slots[slot] = false;
        }
    }
}

/**
  * Reload existing popup
  *
  * @author Damian Kęska
  */

panthera.popup.reload = function (slot) {
    if (!slot)
    {
        slot = '__default__';
    }

    panthera.popup.create(panthera.popup.slots[slot], slot);
}

/**
  * Backwards compatibility with old template that was using w2ui
  *
  * @param string message
  * @return void 
  * @author Damian Kęska
  */

function w2alert(message)
{
    return panthera.alertBox.create(message);
}

panthera.hooks.add('tabPrepareContent', panthera.popup.restoreContent);
panthera.defaultMessageHandler = 'w2ui';
panthera.defaultSpinner = 'nprogress';

/*! NProgress (c) 2013, Rico Sta. Cruz
 *  http://ricostacruz.com/nprogress */

;(function(factory) {

  if (typeof module === 'function') {
    module.exports = factory(this.jQuery || require('jquery'));
  } else {
    this.NProgress = factory(this.jQuery);
  }

})(function($) {
  var NProgress = {};

  NProgress.version = '0.1.2';

  var Settings = NProgress.settings = {
    minimum: 0.08,
    easing: 'ease',
    positionUsing: '',
    speed: 200,
    trickle: true,
    trickleRate: 0.02,
    trickleSpeed: 800,
    showSpinner: true,
    template: '<div class="bar" role="bar"><div class="peg"></div></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
  };

  /**
   * Updates configuration.
   *
   *     NProgress.configure({
   *       minimum: 0.1
   *     });
   */
  NProgress.configure = function(options) {
    $.extend(Settings, options);
    return this;
  };

  /**
   * Last number.
   */

  NProgress.status = null;

  /**
   * Sets the progress bar status, where `n` is a number from `0.0` to `1.0`.
   *
   *     NProgress.set(0.4);
   *     NProgress.set(1.0);
   */

  NProgress.set = function(n) {
    var started = NProgress.isStarted();

    n = clamp(n, Settings.minimum, 1);
    NProgress.status = (n === 1 ? null : n);

    var $progress = NProgress.render(!started),
        $bar      = $progress.find('[role="bar"]'),
        speed     = Settings.speed,
        ease      = Settings.easing;

    $progress[0].offsetWidth; /* Repaint */

    $progress.queue(function(next) {
      // Set positionUsing if it hasn't already been set
      if (Settings.positionUsing === '') Settings.positionUsing = NProgress.getPositioningCSS();
      
      // Add transition
      $bar.css(barPositionCSS(n, speed, ease));

      if (n === 1) {
        // Fade out
        $progress.css({ transition: 'none', opacity: 1 });
        $progress[0].offsetWidth; /* Repaint */

        setTimeout(function() {
          $progress.css({ transition: 'all '+speed+'ms linear', opacity: 0 });
          setTimeout(function() {
            NProgress.remove();
            next();
          }, speed);
        }, speed);
      } else {
        setTimeout(next, speed);
      }
    });

    return this;
  };

  NProgress.isStarted = function() {
    return typeof NProgress.status === 'number';
  };

  /**
   * Shows the progress bar.
   * This is the same as setting the status to 0%, except that it doesn't go backwards.
   *
   *     NProgress.start();
   *
   */
  NProgress.start = function() {
    if (!NProgress.status) NProgress.set(0);

    var work = function() {
      setTimeout(function() {
        if (!NProgress.status) return;
        NProgress.trickle();
        work();
      }, Settings.trickleSpeed);
    };

    if (Settings.trickle) work();

    return this;
  };

  /**
   * Hides the progress bar.
   * This is the *sort of* the same as setting the status to 100%, with the
   * difference being `done()` makes some placebo effect of some realistic motion.
   *
   *     NProgress.done();
   *
   * If `true` is passed, it will show the progress bar even if its hidden.
   *
   *     NProgress.done(true);
   */

  NProgress.done = function(force) {
    if (!force && !NProgress.status) return this;

    return NProgress.inc(0.3 + 0.5 * Math.random()).set(1);
  };

  /**
   * Increments by a random amount.
   */

  NProgress.inc = function(amount) {
    var n = NProgress.status;

    if (!n) {
      return NProgress.start();
    } else {
      if (typeof amount !== 'number') {
        amount = (1 - n) * clamp(Math.random() * n, 0.1, 0.95);
      }

      n = clamp(n + amount, 0, 0.994);
      return NProgress.set(n);
    }
  };

  NProgress.trickle = function() {
    return NProgress.inc(Math.random() * Settings.trickleRate);
  };

  /**
   * (Internal) renders the progress bar markup based on the `template`
   * setting.
   */

  NProgress.render = function(fromStart) {
    if (NProgress.isRendered()) return $("#nprogress");
    $('html').addClass('nprogress-busy');

    var $el = $("<div id='nprogress'>")
      .html(Settings.template);

    var perc = fromStart ? '-100' : toBarPerc(NProgress.status || 0);

    $el.find('[role="bar"]').css({
      transition: 'all 0 linear',
      transform: 'translate3d('+perc+'%,0,0)'
    });

    if (!Settings.showSpinner)
      $el.find('[role="spinner"]').remove();

    $el.appendTo(document.body);

    return $el;
  };

  /**
   * Removes the element. Opposite of render().
   */

  NProgress.remove = function() {
    $('html').removeClass('nprogress-busy');
    $('#nprogress').remove();
  };

  /**
   * Checks if the progress bar is rendered.
   */

  NProgress.isRendered = function() {
    return ($("#nprogress").length > 0);
  };

  /**
   * Determine which positioning CSS rule to use.
   */

  NProgress.getPositioningCSS = function() {
    // Sniff on document.body.style
    var bodyStyle = document.body.style;

    // Sniff prefixes
    var vendorPrefix = ('WebkitTransform' in bodyStyle) ? 'Webkit' :
                       ('MozTransform' in bodyStyle) ? 'Moz' :
                       ('msTransform' in bodyStyle) ? 'ms' :
                       ('OTransform' in bodyStyle) ? 'O' : '';

    if (vendorPrefix + 'Perspective' in bodyStyle) {
      // Modern browsers with 3D support, e.g. Webkit, IE10
      return 'translate3d';
    } else if (vendorPrefix + 'Transform' in bodyStyle) {
      // Browsers without 3D support, e.g. IE9
      return 'translate';
    } else {
      // Browsers without translate() support, e.g. IE7-8
      return 'margin';
    }
  };

  /**
   * Helpers
   */

  function clamp(n, min, max) {
    if (n < min) return min;
    if (n > max) return max;
    return n;
  }

  /**
   * (Internal) converts a percentage (`0..1`) to a bar translateX
   * percentage (`-100%..0%`).
   */

  function toBarPerc(n) {
    return (-1 + n) * 100;
  }


  /**
   * (Internal) returns the correct CSS for changing the bar's
   * position given an n percentage, and speed and ease from Settings
   */

  function barPositionCSS(n, speed, ease) {
    var barCSS;

    if (Settings.positionUsing === 'translate3d') {
      barCSS = { transform: 'translate3d('+toBarPerc(n)+'%,0,0)' };
    } else if (Settings.positionUsing === 'translate') {
      barCSS = { transform: 'translate('+toBarPerc(n)+'%,0)' };
    } else {
      barCSS = { 'margin-left': toBarPerc(n)+'%' };
    }

    barCSS.transition = 'all '+speed+'ms '+ease;

    return barCSS;
  }

  return NProgress;
});

