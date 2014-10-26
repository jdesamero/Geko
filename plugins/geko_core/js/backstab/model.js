/*
 * "backstab/model.js"
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
	
	
	var isInt = function( n ) {
		return n % 1 === 0;
	};
	
	var isFloat = function( inputtxt ) {
		
		var decimal = /^[-+]?[0-9]+\.[0-9]+$/;   
		
		return decimal.exec( inputtxt ) ? true : false ;
	};
	
	
	// add new methods and properties
	var oOpts = {
		
		setup: function() {
			_.mergeValues( 'data', this, arguments[ 1 ] );
		},
		
		getDataValues: function() {
			
			var model = this;
			
			var ret = {};
			for ( var i = 0; i < arguments.length; i++ ) {
				var key = arguments[ i ];
				ret[ key ] = model.get( key );
			}
			
			return ret;
		},
		
		toggleValue: function( sKey ) {
			
			if ( 'boolean' === $.type( this.get( sKey ) ) ) {
				var bToggle = this.get( sKey ) ? false : true ;
				this.set( sKey, bToggle );
			}
			
			return this;
		}

	};
	
	
	// static properties
	var oStaticProps = {
		
		//
		modifyProps: function( obj ) {
			
			var _this = this;
			
			
			// reformat
			if ( obj.fields ) {
				
				var oFields = obj.fields;
				
				var oDefaults = {};
				var aExtractFields = [];
				var oFormats = {};
				
				$.each( oFields, function( k, v ) {
					
					var mDefaultValue = null;
					if ( 'undefined' !== $.type( v.value ) ) {
						mDefaultValue = v.value;
					}
					
					if ( v.extract ) {
						aExtractFields.push( k );
					}
					
					var sFormat = v.format;
					if ( !sFormat ) {
						
						sFormat = $.type( mDefaultValue );
						
						// try to be more specific is sFormat is "number"
						if ( 'number' === sFormat ) {
							if ( isInt( mDefaultValue ) ) {
								sFormat = 'int';
							} else if ( isFloat( mDefaultValue ) ) {
								sFormat = 'float';
							}
						}
					}
					
					oFormats[ k ] = sFormat;
					oDefaults[ k ] = mDefaultValue;
					
				} );
				
				if ( !obj.defaults ) {
					obj.defaults = oDefaults;
				}
				
				obj.extractFields = aExtractFields;
				obj.fieldFormats = oFormats;
			}
			
			
			return obj;
		}
		
	};
	
	
	//
	Backstab.createConstructor( 'Model', oOpts, oStaticProps, Backbone.Model );
	
	
	
} ).call( this );



