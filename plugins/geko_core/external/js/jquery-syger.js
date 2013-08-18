;(function ($) {

	var syger = {
	
		/**
		 Checks the type of a given object.
		
		 @param obj the object to check.
		 @returns one of; "boolean", "number", "string", "object",
		  "function", or "null".
		*/
		
		typeOf : function (obj) {
		  type = typeof obj;
		  return type === "object" && !obj ? "null" : type;
		},
	
		/**
		 Checks if a property of a specified object has the given type.
		
		 @param obj the object to check.
		 @param name the property name.
		 @param type the property type (optional, default is "function").
		 @returns true if the property exists and has the specified type,
		  otherwise false.
		*/
		
		exists: function (obj, name, type) {
		  type = type || "function";
		  return (obj ? this.typeOf(obj[name]) : "null") === type;
		},
	
		/**
		 Introspects an object.
		
		 @param name the object name.
		 @param obj the object to introspect.
		 @param indent the indentation (optional, defaults to "").
		 @param levels the introspection nesting level (defaults to 1).
		 @returns a plain text analysis of the object.
		*/
		
		introspect : function (name, obj, indent, levels) {
		  indent = indent || "";
		  if (this.typeOf(levels) !== "number") levels = 1;
		  var objType = this.typeOf(obj);
		  var result = [indent, name, " ", objType, " :"].join('');
		  if (objType === "object") {
			if (levels > 0) {
			  indent = [indent, "  "].join('');
			  for (prop in obj) {
				var prop;
				try {
				  prop = this.introspect(prop, obj[prop], indent, levels - 1);
				} catch(e) {
				  prop = this.introspect(prop, e, indent, levels - 1);			  
				}
				result = [result, "\n", prop].join('');
			  }
			  return result;
			}
			else {
			  return [result, " ..."].join('');
			}
		  }
		  else if (objType === "null") {
			return [result, " null"].join('');
		  }
		  return [result, " ", obj].join('');
		}
		
	};
	
	$.extend({
		sygerDebug: function( name, obj, indent, levels, append ) {
			
			if ( 0 == $('#syger_debug_console').length ) {
				$('body').append('<div id="syger_debug_console"><pre></pre></div>');
				$('#syger_debug_console').css(
					'border', '1px solid #000'
				).css(
					'font-size', '9px'
				).css(
					'background-color', 'mistyrose'
				);
			}
			
			var s = '';
			if ( append ) {
				s = $('#syger_debug_console pre').html() + "\n\n";
			}
			
			$('#syger_debug_console pre').html(
				s + syger.introspect( name, obj, indent, levels )
			);
			
		}
	});
	
})(jQuery);
