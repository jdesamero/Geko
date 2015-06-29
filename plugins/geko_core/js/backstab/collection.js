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
	
	
	
	//// main
	
	Backstab.setNamespace( 'Collection', Backbone.Collection.extend( {
		
		_prevLen: null,
		
		constructor: function() {
			
			this.sharedSetup();					// from Backstab.Shared
			
			
			Backstab.Util.mergeValues( 'data', this, arguments[ 1 ] );
			
			this.on( 'add', this.lengthChanged );
			this.on( 'remove', this.lengthChanged );
			this.on( 'reset', this.lengthChanged );
			
			Backbone.Collection.apply( this, arguments );
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
		},
		
		
		// re-organize the existing collection using the provided oEdited collection
		// add, edit, or delete the collection's models, all in one go
		
		batchEdit: function( oEdited, fWhereCb, fExistingCb, fNewCb ) {
			
			var _this = this;
			
			var aNewModels = [];											// track all new models that are created
			
			// create a temporary collection
			var oTempCollection = new this.constructor();
			
			// transfer all my models to oTempCollection
			this.transfer( oTempCollection );
			
			// go through each of the edit items
			oEdited.each( function( oEditItem ) {
				
				var oWhereVals = fWhereCb.call( oEditItem );				// values used for finding existing model
				var oExistingVals = fExistingCb.call( oEditItem );			// values used to modify existing model
				
				// determine if model already exists
				var oExistingItem = oTempCollection.findWhere( oWhereVals );
				
				if ( oExistingItem ) {
					
					// modify values of existing model
					oExistingItem.set( oExistingVals );
					
					oTempCollection.transfer( oExistingItem, _this );
					
				} else {
					
					// create a new model
					
					var oNewVals;
					
					if ( fNewCb ) {
						
						oNewVals = fNewCb.call( oEditItem );				// values used for creating a new model
					
					} else {
						
						// if fNewCb was not provided, then merge values from fWhereCb and fExistingCb
						
						oNewVals = {};
						$.extend( oNewVals, oWhereVals );
						$.extend( oNewVals, oExistingVals );
						
					}
					
					var oModel = new _this.model( oNewVals );
					
					aNewModels.push( oNewVals );
					
					_this.add( oModel );
				}
				
			} );
			
			// destroy all leftovers
			oTempCollection.destroyEach();
			
			// return all the new models
			return aNewModels;
		},
		
		
		
		// locate the given model in a collection and do stuff accordingly
		// usage:
		// 		fMatchCb( collection, model ), "this" context is model
		// 		fNoMatchCb( collection, model ), "this" context is model
		
		handleMatch: function( oToMatch, fMatchCb, fNoMatchCb ) {
			
			var _this = this;
			var bMatched = false;
			
			this.each( function( oModel ) {
				
				if ( oModel === oToMatch ) {
					fMatchCb.call( oModel, _this, oModel );
					bMatched = true;			// indicates there was a match
				} else {
					fNoMatchCb.call( oModel, _this, oModel );
				}
				
			} );
			
			this.trigger( 'afterMatch', oToMatch, bMatched );
		}
		
		
	} ) );
	
	
	// mix-in Backstab.Shared
	$.extend( Backstab.Collection.prototype, Backstab.Shared );
	
	
	
} ).call( this );



