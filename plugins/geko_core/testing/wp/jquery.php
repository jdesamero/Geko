<?php

require_once realpath( '../../../../../wp-load.php' );
require_once realpath( '../../../../../wp-admin/includes/admin.php' );

wp_enqueue_script( 'geko-jquery-geko_util' );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	<!-- <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /> -->
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>jQuery</title>
	<?php
		do_action( 'wp_head' );
	?>
	<script type="text/javascript">

( function( $ ) {

	$.fn.doNodeAction = function() {
		return this.length;
		// return this.html().toLowerCase();
		// return this.prop( 'tagName' ).toLowerCase();
	};
	
	
	
} )( jQuery );

		jQuery( document ).ready( function( $ ) {
			
			var form = $( '#foo' );
			
			form.find( '#first_name' ).setFormElemVal( 'Jonathan' );
			form.find( '#last_name' ).setFormElemVal( 'Pryce' );
			form.find( '#details' ).setFormElemVal( 'These are the details' );
			// form.find( '#is_cool' ).setFormElemVal( true );
			// form.find( '#is_cool' ).setFormElemVal( 1 );
			form.find( '#is_cool' ).setFormElemVal( '1' );
			// form.find( '#is_hot' ).setFormElemVal( false );
			// form.find( '#is_hot' ).setFormElemVal( 0 );
			form.find( '#is_hot' ).setFormElemVal( '0' );
			form.find( '#read_only' ).setFormElemVal( 'This is read-only data' );
			
			
			
			
			//// trigger and bind
			
			$( 'span.real' ).bind( 'do_something', function( evt, param1, param2 ) {
				alert( param1 + ':' + param2 + ':' + $( this ).html() );
			} );
			
			
			//// buttons
			
			
			$( '#test' ).click( function() {
				alert( form.find( '#is_cool' ).getFormElemVal() );
				return false;
			} );
			
			$( '#test2' ).click( function() {
				// alert( $( '#a' ).doNodeAction() );
				// alert( $( '.real' ).doNodeAction() );
				// alert( $( '.foo' ).doNodeAction() );
				// alert( $( '.goo' ).doNodeAction() );
				$( 'span.real' ).trigger( 'do_something', [ 'foo', 'bar' ] );
				return false;
			} );
			
		} );
		
	</script>
</head>

<body>

<h1>jQuery</h1>

<form id="foo">
	<table>
		<tr>
			<th>First Name</th>
			<td><input type="text" id="first_name" /></td>
		</tr>
		<tr>
			<th>Last Name</th>
			<td><input type="text" id="last_name" /></td>
		</tr>
		<tr>
			<th>Details</th>
			<td><textarea id="details"></textarea></td>
		</tr>
		<tr>
			<th>Is Cool?</th>
			<td><input type="checkbox" id="is_cool" value="cool" /></td>
		</tr>
		<tr>
			<th>Is Hot?</th>
			<td><input type="checkbox" id="is_hot" value="hot" /></td>
		</tr>
		<tr>
			<th>Read Only</th>
			<td><span id="read_only"></span></td>
		</tr>
	</table>
</form>

<ul>
	<li><a id="test" href="#">Test</a></li>
	<li><a id="test2" href="#">Test 2</a></li>
	<li><a id="test3" href="#">Test 3</a></li>
</ul>

<div>
	<span id="a" class="real">Hay</span>
	<span id="b" class="real">Bee</span>
	<span id="c" class="real">See</span>
	<span id="d" class="foo">Dee</span>
	<span id="e" class="foo">Eee</span>
</div>

</body>

</html>