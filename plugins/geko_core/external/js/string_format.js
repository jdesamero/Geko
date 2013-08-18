//// add commas to a numeric value

var addCommas = function( nStr ) {
	nStr += '';
	x = nStr.split( '.' );
	x1 = x[ 0 ];
	x2 = x.length > 1 ? '.' + x[ 1 ] : '';
	var rgx = /(\d+)(\d{3})/;
	while ( rgx.test( x1 ) ) {
		x1 = x1.replace( rgx, '$1' + ',' + '$2' );
	}
	return x1 + x2;
}

// for convenience...
String.prototype.addCommas = function() {
	return addCommas( this );
};

//// convert line breaks to <br /> and vice-versa

var br2nl = function( val ) {
	return val.replace( /\<br \/>/g, '\n' ).replace( /\<br>/g, '\n' );
}

var nl2br = function( val ) {
	return val.replace( /\n/g, '<br \/>' );
}

// for convenience...
String.prototype.br2nl = function() {
	return br2nl( this );
}

String.prototype.nl2br = function() {
	return nl2br( this );
}

//// convert string to slug

var convertToSlug = function( value ) {
	return value.toLowerCase().replace( /[^\w ]+/g, '' ).replace( / +/g, '-' );
}

String.prototype.convertToSlug = function() {
	return convertToSlug( this );
}

