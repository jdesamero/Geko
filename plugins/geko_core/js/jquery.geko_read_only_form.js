//// turn form elements to read-only
//// by Joel Desamero

;(function ($) {
	
	$.extend({
		readOnlyFormBaseSelector: 'input[type=text], input[type=radio], input[type=checkbox], select, textarea'
	});
	
	$.fn.readOnlyForm = function() {
		
		var oTrack = {};
		
		return this.each(function() {
			
			var sType = this.tagName.toLowerCase();
			var sName = $(this).attr('name');
			var sSubtype = 'none';
			
			// determine sSubType
			if ( 'input' == sType ) {
				sSubtype = $(this).attr('type');
				if ( !sSubtype ) sSubtype = 'text';
			} else if ( 'select' == sType ) {
				if ( $(this).attr('multiple') ) sSubtype = 'multiple';
				else sSubtype = 'single';
			}
			
			// alert( sType + ':' + sSubtype );
			
			// go
			if (
				(
					( 'input' == sType ) && 
					( 'text' == sSubtype || 'radio' == sSubtype || 'checkbox' == sSubtype )
				) || 
				( 'select' == sType ) || 
				( 'textarea' == sType )
			) {
				
				var aOut = [];
				var sOut = '';
				var i = 0;
				var bReplace = true;
				
				//// gather params
				if ( 'input' == sType ) {
					if ( 'text' == sSubtype ) {
						aOut.push( { val : $(this).val(), disp : $(this).val() } );
					} else if ( 'radio' == sSubtype ) {
						var oLabel = $('label[for="' + $(this).attr('id') + '"]');
						if ( $(this).attr('checked') ) {
							var sDisp = $(this).val();
							if ( oLabel.length ) sDisp = oLabel.text();
							aOut.push( { val : $(this).val(), disp : sDisp } );
						} else {
							bReplace = false;
							$(this).remove();								
						}
						if ( oLabel.length ) oLabel.hide();
					} else if ( 'checkbox' == sSubtype ) {
						var oLabel = $('label[for="' + $(this).attr('id') + '"]');
						if ( !oTrack[ sName ] ) {
							$('input[name="' + sName + '"]:checked').each(function() {
								var oLabel = $('label[for="' + $(this).attr('id') + '"]');
								var sDisp = $(this).val();
								if ( oLabel.length ) sDisp = oLabel.text();
								aOut.push( { val : $(this).val(), disp : sDisp } );									
							});
							oTrack[ sName ] = true;
						} else {
							bReplace = false;
							$(this).remove();								
						}
						if ( oLabel.length ) oLabel.hide();
					}
				} else if ( 'select' == sType ) {
					if ( 'multiple' == sSubtype ) {
						$(this).find('option:selected').each(function() {
							aOut.push( { val : $(this).val(), disp : $(this).text() } );							
						});								
					} else {
						aOut.push( { val : $(this).val(), disp : $(this).find('option:selected').text() } );							
					}
				} else if ( 'textarea' == sType ) {
					aOut.push( { val : $(this).text(), disp : $(this).text() } );							
				}
				
				//// generate replacement
				if ( 1 == aOut.length ) {
					sOut = '<input type="hidden" name="' + sName + '" value="' + aOut[0].val.replace( '"', '&quot;' ) + '" \/>';
					if ( 'textarea' == sType ) sOut += '<p>' + aOut[0].disp + '<\/p>';
					else sOut += '<span>' + aOut[0].disp + '<\/span>';
				} else if ( aOut.length > 1 ) {
					var sVals = '';
					var sDisps = '';
					for ( i = 0; i < aOut.length; i++ ) {
						sVals += '<input type="hidden" name="' + sName + '" value="' + aOut[i].val.replace( '"', '&quot;' ) + '" \/>';
						sDisps += '<li>' + aOut[i].disp + '<\/li>';
					}
					if ( sDisps ) sDisps = '<ul>' + sDisps + '<\/ul>';
					sOut = sVals + sDisps;
				}
				
				//// perform replacement
				if ( bReplace ) {
					$(this).replaceWith( sOut );							
				}
				
			}
		
		});
		
	}
	
})(jQuery);