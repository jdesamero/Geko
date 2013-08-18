;(function ($) {
	
	$.fn.sfHover = function() {
		return this.each( function() {
			$(this).find('li').each( function() {
				$(this).mouseover( function() {
					$(this).addClass('sfhover');
				} ).mouseout( function() {
					$(this).removeClass('sfhover');
				} );
			} );
		} );
	};
	
})(jQuery);

