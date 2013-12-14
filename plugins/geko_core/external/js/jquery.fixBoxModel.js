(function($) {

/**
 * Implements W3C's Box Model on non-W3C's box model compliant browsers (IE for example).
 *  IE doesn't implement the W3C's box model, so its behaviour is different from FireFox and other (pseudo)-standard-compliant browsers.
 *  This plugin autodetects if the user's browser needs a fix and, if needed, it fixes all elements of selection.
 *
 *  I just know Dean Edwards's script and I love it, but his solution is very long.
 *    So I have developed this small script.
 *    Actually this script is fully compatible with Dean's script and both can coexist in the same page.
 *
 *  If you use Dean's script you can simply call
 *    $('div').fixBoxModel(); // from $('document').ready(fn);
 *  or you can delete Dean's solution lines from IE*.js (find "// box-model")
 *  and call fixBoxModel with force option enabled
 *    $('div').fixBoxModel( {force: true} );
 *
 *  @example $('div').fixBoxModel(); // note: this have to be the FIRST thing you have to do on $('document').ready
 *  @example Note: next example is not correct because it creates problems at the images with border...
 *    $('*').fixBoxModel(); // Not correct, do not use it, use div in place of *
 *
 *  @param Map options
 *  @option boolean force Force fix even if IE7 fix js is detected (otherwise it preserves compatibility with Dean's script)
 *  
 *  @license You can use it free of charge for private and commercial websites.
 *      You can't sell this code.
 *      You have to leave the @license and the @author name and website even in minified (or similar) js files
 *      Thanks.
 *  @name fixBoxModel
 *  @type jQuery
 *  @cat Plugins/Fixes
 *  @author Alessandro Coscia (php_staff [/\] ya hoo [-] it || http://www.programmatorephp.it/jquery)
 */
$.fn.fixBoxModel = function(options) {
    settings = jQuery.extend({
      force: false // Force fix even if IE7 fix js is detected
    }, options);
    
    // this browser not implements W3C implementation and (forced by setting or IE7 fix js detected)
    if (!jQuery.support.boxModel && (settings.force || typeof(IE7) != 'object')) {
      // For each div
      return this.each(function() {
        // Power of jQuery helps us to solve the problem in handful of lines!
        widthDiff = $(this).outerWidth() - $(this).width();
        heightDiff = $(this).outerHeight() - $(this).height();
        $(this).width($(this).outerWidth() + widthDiff);
        $(this).height($(this).outerHeight() + heightDiff);
      });
    }
    return this;
  }
})(jQuery);