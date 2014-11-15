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
	
	
	//
	var getValueFormat = function( mValue ) {
		
		var sFormat = $.type( mValue );
		
		// try to be more specific is sFormat is "number"
		if ( 'number' === sFormat ) {
			if ( isInt( mValue ) ) {
				sFormat = 'int';
			} else if ( isFloat( mValue ) ) {
				sFormat = 'float';
			}
		}
		
		return sFormat;
	};
	
	
	
	// add new methods and properties
	var oOpts = {
		
		beforeInit: function() {
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
		},
		
		getIntCid: function() {
			// return a negative value so it doesn't collide with existing ids
			return - parseInt( this.cid.replace( 'c', '' ) );
		},
		
		isNew: function() {
			
			var mId = this.get( 'id' );
			
			if ( ( 'number' === $.type( mId ) ) && ( mId < 0 ) ) {
				return true;
			}
			
			return Backbone.Model.prototype.isNew.apply( this, arguments );
		},
		
		getFieldFormat: function( sKey ) {
			
			if ( this.fieldFormats && this.fieldFormats[ sKey ] ) {
				return this.fieldFormats[ sKey ];
			}
			
			return null;
		}
		
	};
	
	
	// static properties
	var oStaticProps = {
		
		//
		modifyProps: function( obj ) {
			
			var _this = this;
			
			var oFields = obj.fields;
			
			var oDefaults = {};
			var aExtractFields = [];
			var aUniqueCheckFields = [];
			var oFormats = {};
			
			
			// reformat
			if ( oFields ) {
				
				$.each( oFields, function( k, v ) {
					
					var mDefaultValue = null;
					if ( 'undefined' !== $.type( v.value ) ) {
						mDefaultValue = v.value;
					}
					
					if ( v.extract ) {
						aExtractFields.push( k );
					}
					
					if ( v.uniqueCheck ) {
						aUniqueCheckFields.push( k );
					}
					
					var sFormat = v.format;
					if ( !sFormat ) {
						sFormat = getValueFormat( mDefaultValue );
					}
					
					oFormats[ k ] = sFormat;
					oDefaults[ k ] = mDefaultValue;
					
				} );
				
				if ( !obj.defaults ) {
					obj.defaults = oDefaults;
				}
				
				if ( !obj.extractFields && ( aExtractFields.length > 0 ) ) {
					obj.extractFields = aExtractFields;
				}
				
				if ( !obj.uniqueCheckFields && ( aUniqueCheckFields.length > 0 ) ) {
					obj.uniqueCheckFields = aUniqueCheckFields;
				}
				
				if ( !obj.fieldFormats && ( !$.isEmptyObject( oFormats ) ) ) {
					obj.fieldFormats = oFormats;
				}
				
			} else {
			
				// get field formats from default values
				
				if ( !obj.fieldFormats && obj.defaults ) {
					
					$.each( obj.defaults, function( k, v ) {
						oFormats[ k ] = getValueFormat( v );
					} );
					
					obj.fieldFormats = oFormats;
				}
				
			}
			
			
			return obj;
		}
		
	};
	
	
	//
	Backstab.createConstructor( 'Model', oOpts, oStaticProps, Backbone.Model );
	
	
	
} ).call( this );



