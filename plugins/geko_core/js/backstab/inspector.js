/*
 * "backstab/view.js"
 * https://github.com/jdesamero/Backstab
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * depends on "backstab/core.js"
 */

( function() {
	
	var $ = this.jQuery;
	var Backbone = this.Backbone;
	var Backstab = this.Backstab;
	
	
	
	
	var oLocalDispatcher = new Backstab.Dispatcher();
	
	
	// the controls
	var Control = Backstab.View.extend( {
		
		events: {
			'click': 'displayResults'
		},
		
		localDispatcher: oLocalDispatcher,
		
		createElement: function() {
			return $( '<input type="button" class="button-primary" value="" \/>' );
		},
		
		initialize: function() {
			
			this.$el.val( this.data.title );
			this.$el.addClass( this.data.slug );
			
		},
		
		displayResults: function() {
			this.localDispatcher.trigger( 'displayResults', this.collection );
		}
		
	} );
	
	
	
	// the view
	Backstab.Inspector = Backstab.View.extend( {
		
		events: {
			'localDispatcher:displayResults this': 'displayResults',
			'click a.toggle': 'togglePanel'
		},
		
		localDispatcher: oLocalDispatcher,
		
		createElement: function() {
			
			return $(
				'<form>' + 
					'<p><strong>Backstab Inspector<\/strong> &nbsp; <a href="#" class="toggle">Hide<\/a><\/p>' + 
					'<div class="wrap">' + 
						'<div class="controls"><\/div>' + 
						'<div class="options">' +
							'<input type="checkbox" class="include_type" /> <label>Include Type<\/label>' +
						'<\/div>' + 
						'<hr \/>' + 
						'<div class="result"><\/div>' + 
					'<\/div>' + 
				'<\/form>'
			);
		},
		
		initialize: function() {
			
			var _this = this;
			
			var oCollectionHash = this.data.collectionHash;
			
			if ( oCollectionHash ) {
				
				var bFirst = true;
				
				$.each( oCollectionHash, function( k, v ) {
					
					var sTitle = ( v.title ) ? v.title : k ;
					var oCollection = ( v.model ) ? new Backstab.Collection( [ v.model ] ) : v.collection ;
					
					var oControl = new Control( {
						collection: oCollection,
						data: {
							slug: k,
							title: sTitle,
							formView: _this
						}
					} );
					
					if ( bFirst ) {
						bFirst = false;
					} else {
						_this.$( '.controls' ).append( ' &nbsp; ' );
					}
					
					_this.$( '.controls' ).append( oControl.render().$el );
					
				} );
				
			}
			
		},
		
		displayResults: function( e, oCollection ) {
			
			// replace old results
			this.$( '> div.wrap > div.result' ).html( this.createResultsTable( oCollection ) );
			
		},
		
		togglePanel: function() {
			
			var eWrap = this.$( '> div.wrap' );
			var eToggle = this.$( 'a.toggle' );
			
			if ( eWrap.is( ':hidden' ) ) {
				
				eWrap.show();
				eToggle.html( 'Hide' );
				
			} else {
				
				eWrap.hide();
				eToggle.html( 'Show' );
			}
			
			
			return false;
		},
		
		// helpers
		
		createResultsTable: function( oCollection ) {
			
			if ( !oCollection.length ) {
				return null;
			}
			
			var _this = this;
			
			// create a blank results table
			var eTable = $(
				'<table class="current wp-list-table widefat">' + 
					'<thead></thead>' + 
					'<tbody></tbody>' + 
				'</table>'
			);
			
			var bTitle = true;
			var bAlternate = true;
			var aKeys = [];
			var bIncludeType = this.$( '.include_type' ).is( ':checked' ) ? true : false ;
						
			oCollection.each( function( oModel ) {
				
				// these are row titles
				
				if ( bTitle ) {
	
					var eTitleTr = $( '<tr></tr>' );
					
					$.each( oModel.toJSON(), function( k, v ) {
						_this.createColumnTitle( k, eTitleTr, aKeys );
					} );
					
					if ( oCollection.assertColumns ) {
						
						$.each( oCollection.assertColumns, function( i, v ) {
							_this.createColumnTitle( v, eTitleTr, aKeys );
						} );
					}
					
					eTable.find( '> thead' ).append( eTitleTr );
					
					bTitle = false;
				}
				
				
				// these are the rows
				
				var eTr = $( '<tr></tr>' );
				
				if ( bAlternate ) {
					eTr.addClass( 'alternate' );
					bAlternate = false;
				} else {
					bAlternate = true;
				}
				
				$.each( aKeys, function( i, v ) {
					
					var eTd = $( '<td></td>' );
					
					var mModelVal = oModel.get( v );
					var sColVal = mModelVal;
					var sType = $.type( mModelVal );
					
					if ( 'object' === sType ) {
						
						eTd.append( _this.createResultsTable( mModelVal ) );
						
					} else {
					
						if ( bIncludeType ) {
							sColVal += ' &nbsp; [%s]'.printf( sType );
						}
						
						eTd.html( sColVal );
					}
					
					eTr.append( eTd );
				} );
				
				eTable.find( '> tbody' ).append( eTr );
				
			} );
			
			return eTable;
		},
		
		
		createColumnTitle: function( sKey, eTr, aKeys ) {
			
			var eTh = $( '<th></th>' );
			eTh.html( sKey );
						
			eTr.append( eTh );
			
			aKeys.push( sKey );
			
		}
		
		
	} );
	
	
	
} ).call( this );