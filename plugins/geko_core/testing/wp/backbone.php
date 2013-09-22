<?php

ini_set( 'display_errors', 1 );
// ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
// error_reporting( E_ALL );

require_once realpath( '../../../../../wp-load.php' );
require_once realpath( '../../../../../wp-admin/includes/admin.php' );

// ---------------------------------------------------------------------------------------------- //

/* /
// do checks
if ( !is_user_logged_in() || !current_user_can( 'administrator' ) ) {
	die();
}

ini_set( 'display_errors', 1 );
ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
error_reporting( E_ALL );
/* */

$oLoader = Geko_Loader_ExternalFiles::getInstance();
$oLoader
	
	->setBaseUrl( 'http://dev.geekoracle.com/template' )
	
	->registerFromXmlConfigFile( '../conf/register_extra.xml' )
	->registerFromXmlConfigFile( '../conf/register.xml' )
	
	->enqueueScript( 'geko-backbone' )
	->enqueueScript( 'geko-jquery-tmpl' )
;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">

<head profile="http://gmpg.org/xfn/11">
	
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<title>backbone.js</title>
	
	<?php $oLoader->renderScriptTags(); ?>
	<?php $oLoader->renderStyleTags(); ?>

	<script type="text/javascript">

		var SomeModel = Backbone.Model.extend( {
			defaults: {
				id: 12,
				rank: 999,
				symbol: 'XXX',
				mentions: 69,
				title: '(Untitled)',
				author: [ 'John', 'Paul', 'George', 'Ringo' ]
			}
		} );
		
		var SomeModels = Backbone.Collection.extend( {
			model: SomeModel
		} );
		
		var SomeView = Backbone.View.extend( {
			
			itemTpl: null,
			inner: null,
			
			// short form idea...
			change: {
				'title': 'span.title'
			},
			
			events: {
				'click .mentions a': 'foo'
			},
			
			initialize: function() {
				
				var _this = this;
				var _model = this.model;
				
				this.itemTpl = $( '#company-tmpl' );
				
				_model.on( 'change:title', function() {
					_this.inner.find( '.title' ).html( _model.get( 'title' ) );
				} );
				
				// this.model.bind( 'change', _.bind( this.update, this ) );
			},
			
			/* /
			update: function() {
				if ( this.inner ) this.inner.remove();
				this.render();
				return this;
			},
			/* */
			
			render: function() {
				this.inner = this.itemTpl.tmpl( this.model.toJSON() );
				this.$el.append( this.inner );
				return this;
			},
			
			foo: function() {
				alert( 'yes yes yo' );
				return false;
			}
			
		} );
		
		jQuery( document ).ready( function( $ ) {
			
			var someModel = new SomeModel( { title: 'Apple' } );
			var sm2 = new SomeModel( { id: '_1', title: 'Banana' } );
			var sm3 = new SomeModel( { id: 14, title: 'Orange' } );
			
			var someModels = new SomeModels( [ someModel, sm2, sm3 ] );
			
			var someView = new SomeView( { model: someModel } );
			
			$( '#main' ).append( someView.el );
			someView.render();
			
			$( '#test' ).click( function() {
				someModel.set( 'title', 'New Title!!!' );
				return false;
			} );
			
			$( '#test2' ).click( function() {
				someModel.set( 'title', 'Change Me' );
				return false;
			} );

			$( '#test3' ).click( function() {
				
				/* /
				var sm = someModels.get( '_1' );
				alert( sm.get( 'title' ) + ' | ' + sm.cid + ' | ' + sm.idAttribute );
				/* */
				
				alert( someModels.pluck( 'title' ) );
				
				return false;
			} );
			
		} );
		
	</script>
	
</head>

<body>

<h1>backbone.js</h1>

<hr />

<ul id="main"></ul>

<script id="author-tmpl" type="text/x-jquery-tmpl">  
	<ul>{{each author}}<li>${$value}</li>{{/each}}</ul>
</script>

<script id="company-tmpl" type="text/x-jquery-tmpl">  
	<li id="${id}">
		<div class="rank">${rank}</div>
		<div><span class="title">${title}</span> (<span class="symbol">${symbol}</span>)</div>
		<div class="mentions"><a href="#">${mentions}</a></div>
		<div class="authors">{{tmpl($data) "#author-tmpl"}}</div>
		<div class="fix"></div>
	</li>
</script>

<a href="#" id="test">Test</a> | <a href="#" id="test2">Test</a> | <a href="#" id="test3">Test</a>

</body>

</html>
