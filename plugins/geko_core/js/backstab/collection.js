/*
 * "backstab/collection.js"
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
	
	
	
	// add new methods and properties
	var oOpts = {
		
		_prevLen: null,
		
		beforeInit: function() {
			
			_.mergeValues( 'data', this, arguments[ 1 ] );
			
			this.on( 'add', this.lengthChanged );
			this.on( 'remove', this.lengthChanged );
			this.on( 'reset', this.lengthChanged );
			
		},
		
		lengthChanged: function() {
			if ( this.length !== this._prevLen ) {
				this.trigger( 'lengthChanged', this, this.length, this._prevLen );
				this._prevLen = this.length;
			}
		},
		
		transfer: function() {
			
			var _this = this;
			
			var iArgLen = arguments.length;
			
			if ( 1 === iArgLen ) {
				
				// transfer "all" the models to another collection
				
				var oDestCollection = arguments[ 0 ];
				
				var aModels = [];
				
				this.each( function( oModel ) {
					aModels.push( oModel );
				} );
				
				$.each( aModels, function( i, v ) {
					_this.transfer( v, oDestCollection );
				} );
				
			} else {
				
				var oDestCollection = arguments[ 1 ];
				
				
				// transfer something (?) to another collection
				var mArg1 = arguments[ 0 ];
				var oModel, oParams;
				
				if ( 'number' === $.type( mArg1 ) ) {
					
					oModel = this.at( mArg1 );
				
				} else {
					
					// assume mArg1 is an object
					if ( mArg1._events && mArg1.cid && mArg1.collection && mArg1.attributes ) {
						oModel = mArg1;				// a model
					} else {
						oParams = mArg1;			// where params
					}
				}
				
				if ( oModel && this.contains( oModel ) ) {
					this.remove( oModel );
					oDestCollection.add( oModel );
				} else if ( oParams ) {
					
					var aModels = this.where( oParams );
					
					$.each( aModels, function( i, v ) {
						_this.transfer( v, oDestCollection );
					} );
					
				}
			
			}
			
			return this;
		},
		
		createPart: function( sKey, oPartedCollection, oFindParams ) {
			
			var oPart = new this.constructor( this.where( oFindParams ) );
			
			var oModelParams = {};
			oModelParams[ sKey ] = oPart;
			_.extend( oModelParams, oFindParams );
			
			oPartedCollection.add( new Backstab.Model( oModelParams ) );
			
			return oPart;
		},
		
		findAndGet: function( sGetKey, oFindParams ) {
			return this.findWhere( oFindParams ).get( sGetKey );
		},
		
		addIfUniqueValues: function( oModel ) {
			
			var _this = this;
			
			// collection already contains model
			
			if ( this.contains( oModel ) ) {
				return oModel;
			}
			
			
			// perform matching
			
			var oReturnModel = null;
			
			if ( oModel.uniqueCheckFields ) {
				
				$.each( _this.models, function( j, oCompareModel ) {
					
					var bMatched = true;
					
					$.each( oModel.uniqueCheckFields, function( i, v ) {
						
						var mOrigValue = oModel.get( v );
						var mCompareValue = oCompareModel.get( v );
						var sFormat = oModel.getFieldFormat( v );
						
						// To do: make this configurable
						
						if ( 'string' === sFormat ) {
							mOrigValue = mOrigValue.toLowerCase().replace( / /g, '' );
							mCompareValue = mCompareValue.toLowerCase().replace( / /g, '' );
						}
						
						if ( mOrigValue !== mCompareValue ) {
							bMatched = false;
							return false;
						}
						
						
					} );
					
					if ( bMatched ) {
						oReturnModel = oCompareModel;
						return false;
					}
					
				} );
				
				
			}
			
			
			// there is no match
			
			if ( !oReturnModel ) {
				
				this.add( oModel );
				oReturnModel = oModel;
			}
			
			
			return oReturnModel;
		},
		
		
		// explicitly destroy each model
		destroyEach: function() {
			
			var aEach = [];
			
			// destroying model here has weird effects
			this.each( function( oModel ) {
				aEach.push( oModel );
			} );
			
			$.each( aEach, function( i, v ) {
				v.destroy();
			} );
		}
		
	};
	
	
	
	//
	Backstab.createConstructor( 'Collection', oOpts, null, Backbone.Collection );
	
	
	
} ).call( this );



