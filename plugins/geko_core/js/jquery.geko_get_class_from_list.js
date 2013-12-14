;(function ($) {

	$.fn.extend({
		getClassFromList: function( aClassList ) {
			
			var len = aClassList.length;
			var i = 0;
			
			for ( i = 0; i < len; i++ ) {
				if ( this.hasClass( aClassList[ i ] ) ) {
					return aClassList[ i ];
				}
			}
			
			return '';
		}
	});
	
})(jQuery);
