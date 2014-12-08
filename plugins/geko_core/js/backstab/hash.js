/*
 * "backstab/foo.js"
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
	
	
	var getCount = function( oHash ) {
		
		var iLen = 0;
		
		$.each( oHash, function( k, v ) {
			if ( v ) iLen++;
		} );
		
		return iLen;
	};
	
	
	
	//// main
	
	Backstab.setNamespace( 'Hash', Backstab.Base.extend( {
		
		length: 0,						// length of hash
		aliasLength: 0,					// length of aliases
		
		hash: null,						// internal hash of members
		alias: null,					// hash of aliases
		
		// constructor for member object
		member: null,
		
		defaultKey: null,
		
		constructor: function( oMembers, oParams ) {
			
			// do this for instance
			this.hash = {};
			this.alias = {};
			
			if ( oParams && oParams[ 'default' ] ) {
				this.setDefault( oParams[ 'default' ] );
			}
			
			this.set( oMembers );
			
			Backstab.Base.apply( this, arguments );
		},
		
		'set': function() {
			
			var _this = this;
			
			var iArgLen = arguments.length;
			
			if ( 2 === iArgLen ) {
				
				// assume sKey and mMember were provided
				var sKey = arguments[ 0 ];
				var mMember = arguments[ 1 ];

				var oMember = null;
				
				
				if ( -1 !== $.inArray( $.type( mMember ), [ 'string', 'number' ] ) ) {
					
					if ( this.hash[ mMember ] ) {
						this.alias[ sKey ] = mMember;
					}
					
				} else {
				
					// regular
					if (
						$.isPlainObject( mMember ) && 
						this.member
					) {
						oMember = new this.member( mMember );
					} else {
						oMember = mMember;
					}
					
					if ( oMember ) {
						
						var oOldMember = this.hash[ sKey ];		// in case already set
						
						this.hash[ sKey ] = oMember;
						
						// trigger "set" event
						this.trigger( 'set', sKey, oMember, oOldMember, this );
						this.trigger( 'set:%s'.printf( sKey ), oMember, oOldMember, this );
						
					}
					
				}
				
				this.updateLength();
				
			} else {
				
				// assume hash of members were provided
				
				var oMembers = arguments[ 0 ];
				
				$.each( oMembers, function( k, v ) {
					_this.set( k, v );
				} );
				
			}
			
			return this;
		},
		
		'get': function( sKey ) {
			
			// resolve alias first
			var mAliasKey = this.alias[ sKey ];
			if ( mAliasKey ) sKey = mAliasKey;
			
			var oRes = this.hash[ sKey ];
			
			if ( !oRes && this.defaultKey ) {
				oRes = this.hash[ this.defaultKey ];
			}
			
			return oRes;
		},
		
		setDefault: function( sDefaultKey ) {
			
			this.defaultKey = sDefaultKey;
			
			return this;
		},
		
		unset: function( sKey ) {
			
			var _this = this;
			
			var mDelAlias = this.alias[ sKey ];
			var oDelMember = this.hash[ sKey ];
			
			if ( mDelAlias ) {
				
				// trigger "unset" event
				this.trigger( 'unsetAlias', sKey, mDelAlias, this.hash[ mDelAlias ], this );
				this.trigger( 'unsetAlias:%s'.printf( sKey ), mDelAlias, this.hash[ mDelAlias ], this );
				
				delete this.alias[ sKey ];
			}
			
			if ( oDelMember ) {
				
				// trigger "unset" event
				this.trigger( 'unset', sKey, oDelMember, this );
				this.trigger( 'unset:%s'.printf( sKey ), oDelMember, this );

				delete this.hash[ sKey ];
				
				// delete aliases
				
				$.each( this.alias, function( k, v ) {
					if ( v === sKey ) {
						_this.unset( k );
					}
				} );
			}
			
			this.updateLength();
			
			return this;
		},
		
		updateLength: function() {
			
			//// hash length
			
			var iOldLen = this.length;
			var iLen = getCount( this.hash );
			
			this.length = iLen;
			
			if ( iOldLen !== iLen ) {
				this.trigger( 'lengthChanged', this, iLen, iOldLen );
			}
			
			//// alias length
			
			var iOldAliasLen = this.aliasLength;
			var iAliasLen = getCount( this.alias );
			
			this.aliasLength = iAliasLen;
			
			if ( iOldAliasLen !== iAliasLen ) {
				this.trigger( 'aliasLengthChanged', this, iAliasLen, iOldAliasLen );
			}
			
		},
		
		hasMember: function( sKey ) {
			return ( this.hash[ sKey ] || this.alias[ sKey ] ) ? true : false ;
		},
		
		each: function( fEachCb ) {
			$.each( this.hash, fEachCb );
		}
		
	} ) );
	
	
} ).call( this );

