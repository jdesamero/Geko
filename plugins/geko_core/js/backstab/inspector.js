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
			'localDispatcher:displayResults this': 'displayResults'
		},
		
		localDispatcher: oLocalDispatcher,
		
		createElement: function() {
			
			return $(
				'<form>' + 
					'<div class="controls"><\/div>' + 
					'<hr \/>' + 
					'<div class="result"><\/div>' + 
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
			
			oCollection.each( function( oModel ) {
				
				if ( bTitle ) {
	
					var eTitleTr = $( '<tr></tr>' );
	
					$.each( oModel.toJSON(), function( k, v ) {
						
						var eTh = $( '<th></th>' );
						eTh.html( k );
						
						eTitleTr.append( eTh );
						
						aKeys.push( k );
					} );
					
					eTable.find( 'thead' ).append( eTitleTr );
					
					bTitle = false;
				}
				
				var eTr = $( '<tr></tr>' );
				
				if ( bAlternate ) {
					eTr.addClass( 'alternate' );
					bAlternate = false;
				} else {
					bAlternate = true;
				}
				
				$.each( aKeys, function( i, v ) {
					
					var eTd = $( '<td></td>' );
					eTd.html( oModel.get( v ) );
					
					eTr.append( eTd );
				} );
				
				eTable.find( 'tbody' ).append( eTr );
				
			} );
			
			// replace old results
			this.$( '> div.result' ).html( eTable );
			
		}
		
	} );
	
	
	
} ).call( this );