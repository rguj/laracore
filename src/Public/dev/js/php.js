
/*	

CREDITS to: Locutus https://locutus.io/php/
-- All your standard libraries will be assimilated into our JavaScript collective. Resistance is futile.

-----------------------------------------------------------------------------------

Table of Contents

-- VARIABLE HANDLING --
	isset(args)
	empty(mixedVar)
	gettype(mixedVar)
	is_null(mixedVar)
	is_bool (mixedVar)
	is_numeric(mixedVar)
	is_long(mixedVar)
	is_int(mixedVar) or is_integer(mixedVar)
	is_double(mixedVar)
	is_float(mixedVar)
	is_string(mixedVar)
	is_array(mixedVar)
	is_object(mixedVar)

-- Arrays/Objects
	count(mixedVar, mode)
	array_key_exists(key, search)
	in_array(needle, haystack, argStrict)
	explode(delimiter, string, limit)
	implode(glue, pieces)
	array_merge(...arr)

-- String
	trim(str, charlist)
	rtrim(str, charlist)
	ltrim(str, charlist)
	strtoupper: function(str)
	lcfirst(str)
	ucfirst(str)
	ucwords: function(str)
	str_replace(search, replace, subject, countObj)

-- JSON
	json_encode(mixedVal)
	json_decode(strJson)

-- URL string
	base64_decode(encodedData)
	base64_encode(stringToEncode)

	

*/

'use strict';

const php = {




	// ---------------------------------------------------------------------------------------------
	// DATA TYPE
	// ---------------------------------------------------------------------------------------------

	isset : function() {
		// can have multiple arguments
		//   example 1: isset( undefined, true)
		//   returns 1: false
		//   example 2: isset( 'Kevin van Zonneveld' )
		//   returns 2: true

		var a = arguments;
		var l = a.length;
		var i = 0;
		var undef;
		if (l === 0) {
			throw new Error('Empty isset');
		}
		while (i !== l) {
			if (a[i] === undef || a[i] === null) {
				return false;
			}
			i++;
		}
		return true;
	},

	empty: function(mixedVar) {
		//   example 1: empty(null)
		//   returns 1: true
		//   example 2: empty(undefined)
		//   returns 2: true
		//   example 3: empty([])
		//   returns 3: true
		//   example 4: empty({})
		//   returns 4: true
		//   example 5: empty({'aFunc' : function () { alert('humpty'); } })
		//   returns 5: false

		var undef
		var key
		var i
		var len
		var emptyValues = [undef, null, false, 0, '', '0']

		for (i = 0, len = emptyValues.length; i < len; i++) {
			if (mixedVar === emptyValues[i]) {
			  return true
			}
		}

		if (typeof mixedVar === 'object') {
			for (key in mixedVar) {
			  if (mixedVar.hasOwnProperty(key)) {
			    return false
			  }
			}
			return true
		}

		return false
	},

	gettype : function(mixedVar) {
		//      note 1: 1.0 is simplified to 1 before it can be accessed by the function, this makes
		//      note 1: it different from the PHP implementation. We can't fix this unfortunately.
		//   example 1: gettype(1)
		//   returns 1: 'integer'
		//   example 2: gettype(undefined)
		//   returns 2: 'undefined'
		//   example 3: gettype({0: 'Kevin van Zonneveld'})
		//   returns 3: 'object'
		//   example 4: gettype('foo')
		//   returns 4: 'string'
		//   example 5: gettype({0: function () {return false;}})
		//   returns 5: 'object'
		//   example 6: gettype({0: 'test', length: 1, splice: function () {}})
		//   returns 6: 'object'
		//   example 7: gettype(['test'])
		//   returns 7: 'array'
	  
		//var isFloat = require('../var/is_float')
	  
		var s = typeof mixedVar
		var name
		var _getFuncName = function (fn) {
			var name = (/\W*function\s+([\w$]+)\s*\(/).exec(fn);
			if (!name) {
				return '(Anonymous)';
			}
			return name[1];
		}
	  
		if (s === 'object') {
		  if (mixedVar !== null) {
			// From: https://javascript.crockford.com/remedial.html
			// @todo: Break up this lengthy if statement
			if (typeof mixedVar.length === 'number' &&
			  !(mixedVar.propertyIsEnumerable('length')) &&
			  typeof mixedVar.splice === 'function') {
			  s = 'array'
			} else if (mixedVar.constructor && _getFuncName(mixedVar.constructor)) {
			  name = _getFuncName(mixedVar.constructor)
			  if (name === 'Date') {
				// not in PHP
				s = 'date'
			  } else if (name === 'RegExp') {
				// not in PHP
				s = 'regexp'
			  } else if (name === 'LOCUTUS_Resource') {
				// Check against our own resource constructor
				s = 'resource'
			  }
			}
		  } else {
			s = 'null'
		  }
		} else if (s === 'number') {
		  //s = isFloat(mixedVar) ? 'double' : 'integer'
		  s = PHP.is_float(mixedVar) ? 'double' : 'integer'
		}
	  
		return s
	},

	is_null : function(mixedVar) {
		//   example 1: is_null('23')
		//   returns 1: false
		//   example 2: is_null(null)
		//   returns 2: true

		return (mixedVar === null);
	},

	is_bool : function(mixedVar) {
		//   example 1: is_bool(false)
		//   returns 1: true
		//   example 2: is_bool(0)
		//   returns 2: false

		return (mixedVar === true || mixedVar === false) // Faster (in FF) than type checking
	},

	is_numeric : function(mixedVar) {
		//   example 1: is_numeric(186.31)
		//   returns 1: true
		//   example 2: is_numeric('Kevin van Zonneveld')
		//   returns 2: false
		//   example 3: is_numeric(' +186.31e2')
		//   returns 3: true
		//   example 4: is_numeric('')
		//   returns 4: false
		//   example 5: is_numeric([])
		//   returns 5: false
		//   example 6: is_numeric('1 ')
		//   returns 6: false

		var whitespace = [
		' ',
		'\n',
		'\r',
		'\t',
		'\f',
		'\x0b',
		'\xa0',
		'\u2000',
		'\u2001',
		'\u2002',
		'\u2003',
		'\u2004',
		'\u2005',
		'\u2006',
		'\u2007',
		'\u2008',
		'\u2009',
		'\u200a',
		'\u200b',
		'\u2028',
		'\u2029',
		'\u3000'
		].join('')

		// @todo: Break this up using many single conditions with early returns
		return (typeof mixedVar === 'number' ||
			(typeof mixedVar === 'string' &&
			whitespace.indexOf(mixedVar.slice(-1)) === -1)) &&
			mixedVar !== '' &&
			!isNaN(mixedVar)
	},

	is_long : function(mixedVar) {
		//      note 1: 1.0 is simplified to 1 before it can be accessed by the function, this makes
		//      note 1: it different from the PHP implementation. We can't fix this unfortunately.
		//   example 1: is_long(186.31)
		//   returns 1: true

		return PHP.is_float(mixedVar)
	},

	is_int : function(mixedVar) {
		//      note 1: 1.0 is simplified to 1 before it can be accessed by the function, this makes
		//      note 1: it different from the PHP implementation. We can't fix this unfortunately.
		//   example 1: is_int(23)
		//   returns 1: true
		//   example 2: is_int('23')
		//   returns 2: false
		//   example 3: is_int(23.5)
		//   returns 3: false
		//   example 4: is_int(true)
		//   returns 4: false

		return mixedVar === +mixedVar && isFinite(mixedVar) && !(mixedVar % 1);
	},

	is_integer : function(mixedVar) {
		return PHP.is_int(mixedVar);
	},

	is_double : function(mixedVar) {
		//      note 1: 1.0 is simplified to 1 before it can be accessed by the function, this makes
		//      note 1: it different from the PHP implementation. We can't fix this unfortunately.
		//   example 1: is_double(186.31)
		//   returns 1: true

		return PHP.is_float(mixedVar)
	},

	is_float : function(mixedVar) { 
		//      note 1: 1.0 is simplified to 1 before it can be accessed by the function, this makes
		//      note 1: it different from the PHP implementation. We can't fix this unfortunately.
		//   example 1: is_float(186.31)
		//   returns 1: true
	  
		return +mixedVar === mixedVar && (!isFinite(mixedVar) || !!(mixedVar % 1))
	},

	is_string : function(mixedVar) {
		//   example 1: is_string('23')
		//   returns 1: true
		//   example 2: is_string(23.5)
		//   returns 2: false

		return (typeof mixedVar === 'string');
	},

	is_array : function(mixedVar) {
		//      note 1: In Locutus, javascript objects are like php associative arrays,
		//      note 1: thus JavaScript objects will also
		//      note 1: return true in this function (except for objects which inherit properties,
		//      note 1: being thus used as objects),
		//      note 1: unless you do ini_set('locutus.objectsAsArrays', 0),
		//      note 1: in which case only genuine JavaScript arrays
		//      note 1: will return true
		//   example 1: is_array(['Kevin', 'van', 'Zonneveld'])
		//   returns 1: true
		//   example 2: is_array('Kevin van Zonneveld')
		//   returns 2: false
		//   example 3: is_array({0: 'Kevin', 1: 'van', 2: 'Zonneveld'})
		//   returns 3: true
		//   example 4: ini_set('locutus.objectsAsArrays', 0)
		//   example 4: is_array({0: 'Kevin', 1: 'van', 2: 'Zonneveld'})
		//   returns 4: false
		//   example 5: is_array(function tmp_a (){ this.name = 'Kevin' })
		//   returns 5: false

		var _getFuncName = function (fn) {
			var name = (/\W*function\s+([\w$]+)\s*\(/).exec(fn)
			if (!name) {
				return '(Anonymous)'
			}
			return name[1]
		}
		var _isArray = function (mixedVar) {
			// return Object.prototype.toString.call(mixedVar) === '[object Array]';
			// The above works, but let's do the even more stringent approach:
			// (since Object.prototype.toString could be overridden)
			// Null, Not an object, no length property so couldn't be an Array (or String)
			if (!mixedVar || typeof mixedVar !== 'object' || typeof mixedVar.length !== 'number') {
				return false
			}
			var len = mixedVar.length
			mixedVar[mixedVar.length] = 'bogus'
			// The only way I can think of to get around this (or where there would be trouble)
			// would be to have an object defined
			// with a custom "length" getter which changed behavior on each call
			// (or a setter to mess up the following below) or a custom
			// setter for numeric properties, but even that would need to listen for
			// specific indexes; but there should be no false negatives
			// and such a false positive would need to rely on later JavaScript
			// innovations like __defineSetter__
			if (len !== mixedVar.length) {
				// We know it's an array since length auto-changed with the addition of a
				// numeric property at its length end, so safely get rid of our bogus element
				mixedVar.length -= 1
				return true
			}
			// Get rid of the property we added onto a non-array object; only possible
			// side-effect is if the user adds back the property later, it will iterate
			// this property in the older order placement in IE (an order which should not
			// be depended on anyways)
			delete mixedVar[mixedVar.length]
			return false
		}
		if (!mixedVar || typeof mixedVar !== 'object') {
			return false
		}
		var isArray = _isArray(mixedVar)

		if (isArray) {
			return true
		}
		var iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.objectsAsArrays') : undefined) || 'on'
		if (iniVal === 'on') {
			var asString = Object.prototype.toString.call(mixedVar)
			var asFunc = _getFuncName(mixedVar.constructor)

			if (asString === '[object Object]' && asFunc === 'Object') {
			// Most likely a literal and intended as assoc. array
				return true
			}
		}
		return false
	},

	is_object (mixedVar) {
		//   example 1: is_object('23')
		//   returns 1: false
		//   example 2: is_object({foo: 'bar'})
		//   returns 2: true
		//   example 3: is_object(null)
		//   returns 3: false

		if (Object.prototype.toString.call(mixedVar) === '[object Array]') {
			return false
		}
		return mixedVar !== null && typeof mixedVar === 'object'
	},






	// ---------------------------------------------------------------------------------------------
	// ARRAY / OBJECTS
	// ---------------------------------------------------------------------------------------------
	
	count : function(mixedVar, mode) {
		//   example 1: count([[0,0],[0,-4]], 'COUNT_RECURSIVE')
		//   returns 1: 6
		//   example 2: count({'one' : [1,2,3,4,5]}, 'COUNT_RECURSIVE')
		//   returns 2: 6
	  
		var key
		var cnt = 0
	  
		if (mixedVar === null || typeof mixedVar === 'undefined') {
		  return 0
		} else if (mixedVar.constructor !== Array && mixedVar.constructor !== Object) {
		  return 1
		}
	  
		if (mode === 'COUNT_RECURSIVE') {
		  mode = 1
		}
		if (mode !== 1) {
		  mode = 0
		}
	  
		for (key in mixedVar) {
		  if (mixedVar.hasOwnProperty(key)) {
			cnt++
			if (mode === 1 && mixedVar[key] &&
			  (mixedVar[key].constructor === Array ||
				mixedVar[key].constructor === Object)) {
			  cnt += count(mixedVar[key], 1)
			}
		  }
		}
	  
		return cnt
	},

	array_key_exists : function(key, search) {
		//   example 1: array_key_exists('kevin', {'kevin': 'van Zonneveld'})
		//   returns 1: true
		if (!search || (search.constructor !== Array && search.constructor !== Object)) {
			return false
		}
		return key in search
	},

	in_array : function(needle, haystack, argStrict) {
		//   example 1: in_array('van', ['Kevin', 'van', 'Zonneveld'])
		//   returns 1: true
		//   example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'})
		//   returns 2: false
		//   example 3: in_array(1, ['1', '2', '3'])
		//   example 3: in_array(1, ['1', '2', '3'], false)
		//   returns 3: true
		//   returns 3: true
		//   example 4: in_array(1, ['1', '2', '3'], true)
		//   returns 4: false

		var key = ''
		var strict = !!argStrict

		// we prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] === ndl)
		// in just one for, in order to improve the performance
		// deciding wich type of comparation will do before walk array
		if (strict) {
		for (key in haystack) {
		  if (haystack[key] === needle) {
		    return true
		  }
		}
		} else {
		for (key in haystack) {
		  if (haystack[key] == needle) { // eslint-disable-line eqeqeq
		    return true
		  }
		}
		}

		return false
	},

	explode : function(delimiter, string, limit) {
		//   example 1: explode(' ', 'Kevin van Zonneveld')
		//   returns 1: [ 'Kevin', 'van', 'Zonneveld' ]
	  
		if (arguments.length < 2 ||
		  typeof delimiter === 'undefined' ||
		  typeof string === 'undefined') {
		  return null
		}
		if (delimiter === '' ||
		  delimiter === false ||
		  delimiter === null) {
		  return false
		}
		if (typeof delimiter === 'function' ||
		  typeof delimiter === 'object' ||
		  typeof string === 'function' ||
		  typeof string === 'object') {
		  return {
			0: ''
		  }
		}
		if (delimiter === true) {
		  delimiter = '1'
		}
	  
		// Here we go...
		delimiter += ''
		string += ''
	  
		var s = string.split(delimiter)
	  
		if (typeof limit === 'undefined') return s
	  
		// Support for limit
		if (limit === 0) limit = 1
	  
		// Positive limit
		if (limit > 0) {
		  if (limit >= s.length) {
			return s
		  }
		  return s
			.slice(0, limit - 1)
			.concat([s.slice(limit - 1)
			  .join(delimiter)
			])
		}
	  
		// Negative limit
		if (-limit >= s.length) {
		  return []
		}
	  
		s.splice(s.length + limit)
		return s
	},

	implode : function(glue, pieces) {
		//   example 1: implode(' ', ['Kevin', 'van', 'Zonneveld'])
		//   returns 1: 'Kevin van Zonneveld'
		//   example 2: implode(' ', {first:'Kevin', last: 'van Zonneveld'})
		//   returns 2: 'Kevin van Zonneveld'

		var i = ''
		var retVal = ''
		var tGlue = ''

		if (arguments.length === 1) {
			pieces = glue
			glue = ''
		}

		if (typeof pieces === 'object') {
			if (Object.prototype.toString.call(pieces) === '[object Array]') {
			  return pieces.join(glue)
			}
			for (i in pieces) {
			  retVal += tGlue + pieces[i]
			  tGlue = glue
			}
			return retVal
		}

		return pieces
	},
	
	array_merge : function() { // eslint-disable-line camelcase
	  //  discuss at: https://locutus.io/php/array_merge/
	  // original by: Brett Zamir (https://brett-zamir.me)
	  // bugfixed by: Nate
	  // bugfixed by: Brett Zamir (https://brett-zamir.me)
	  //    input by: josh
	  //   example 1: var $arr1 = {"color": "red", 0: 2, 1: 4}
	  //   example 1: var $arr2 = {0: "a", 1: "b", "color": "green", "shape": "trapezoid", 2: 4}
	  //   example 1: array_merge($arr1, $arr2)
	  //   returns 1: {"color": "green", 0: 2, 1: 4, 2: "a", 3: "b", "shape": "trapezoid", 4: 4}
	  //   example 2: var $arr1 = []
	  //   example 2: var $arr2 = {1: "data"}
	  //   example 2: array_merge($arr1, $arr2)
	  //   returns 2: {0: "data"}
	  const args = Array.prototype.slice.call(arguments)
	  const argl = args.length
	  let arg
	  const retObj = {}
	  let k = ''
	  let argil = 0
	  let j = 0
	  let i = 0
	  let ct = 0
	  const toStr = Object.prototype.toString
	  let retArr = true
	  for (i = 0; i < argl; i++) {
		if (toStr.call(args[i]) !== '[object Array]') {
		  retArr = false
		  break
		}
	  }
	  if (retArr) {
		retArr = []
		for (i = 0; i < argl; i++) {
		  retArr = retArr.concat(args[i])
		}
		return retArr
	  }
	  for (i = 0, ct = 0; i < argl; i++) {
		arg = args[i]
		if (toStr.call(arg) === '[object Array]') {
		  for (j = 0, argil = arg.length; j < argil; j++) {
			retObj[ct++] = arg[j]
		  }
		} else {
		  for (k in arg) {
			if (arg.hasOwnProperty(k)) {
			  if (parseInt(k, 10) + '' === k) {
				retObj[ct++] = arg[k]
			  } else {
				retObj[k] = arg[k]
			  }
			}
		  }
		}
	  }
	  return retObj
	},





	// ---------------------------------------------------------------------------------------------
	// STRING
	// ---------------------------------------------------------------------------------------------

	trim : function(str, charlist) {
		//   example 1: trim('    Kevin van Zonneveld    ')
		//   returns 1: 'Kevin van Zonneveld'
		//   example 2: trim('Hello World', 'Hdle')
		//   returns 2: 'o Wor'
		//   example 3: trim(16, 1)
		//   returns 3: '6'

		var whitespace = [
			' ',
			'\n',
			'\r',
			'\t',
			'\f',
			'\x0b',
			'\xa0',
			'\u2000',
			'\u2001',
			'\u2002',
			'\u2003',
			'\u2004',
			'\u2005',
			'\u2006',
			'\u2007',
			'\u2008',
			'\u2009',
			'\u200a',
			'\u200b',
			'\u2028',
			'\u2029',
			'\u3000'
		].join('')
		var l = 0
		var i = 0
		str += ''

		if (charlist) {
			whitespace = (charlist + '').replace(/([[\]().?/*{}+$^:])/g, '$1')
		}

		l = str.length
		for (i = 0; i < l; i++) {
			if (whitespace.indexOf(str.charAt(i)) === -1) {
			  str = str.substring(i)
			  break
			}
		}

		l = str.length
		for (i = l - 1; i >= 0; i--) {
			if (whitespace.indexOf(str.charAt(i)) === -1) {
			  str = str.substring(0, i + 1)
			  break
			}
		}

		return whitespace.indexOf(str.charAt(0)) === -1 ? str : ''
	},
	
	rtrim: function (str, charlist) {
		//  discuss at: https://locutus.io/php/rtrim/
		// original by: Kevin van Zonneveld (https://kvz.io)
		//    input by: Erkekjetter
		//    input by: rem
		// improved by: Kevin van Zonneveld (https://kvz.io)
		// bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
		// bugfixed by: Brett Zamir (https://brett-zamir.me)
		//   example 1: rtrim('    Kevin van Zonneveld    ')
		//   returns 1: '    Kevin van Zonneveld'
		charlist = !charlist
		? ' \\s\u00A0'
		: (charlist + '').replace(/([[\]().?/*{}+$^:])/g, '\\$1')
		const re = new RegExp('[' + charlist + ']+$', 'g')
		return (str + '').replace(re, '')
	},

	ltrim: function (str, charlist) {
		//  discuss at: https://locutus.io/php/ltrim/
		// original by: Kevin van Zonneveld (https://kvz.io)
		//    input by: Erkekjetter
		// improved by: Kevin van Zonneveld (https://kvz.io)
		// bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
		//   example 1: ltrim('    Kevin van Zonneveld    ')
		//   returns 1: 'Kevin van Zonneveld    '
		charlist = !charlist
		? ' \\s\u00A0'
		: (charlist + '').replace(/([[\]().?/*{}+$^:])/g, '$1')
		const re = new RegExp('^[' + charlist + ']+', 'g')
		return (str + '')
		.replace(re, '')
	},

	strtoupper: function(str) {
		//   example 1: strtoupper('Kevin van Zonneveld')
		//   returns 1: 'KEVIN VAN ZONNEVELD'

		return (str + '').toUpperCase();
	},
	
	lcfirst: function(str) {
		//   example 1: lcfirst('Kevin Van Zonneveld')
		//   returns 1: 'kevin Van Zonneveld'

		str += '';
		const f = str.charAt(0).toLowerCase();
		return f + str.substr(1);
	},

	ucfirst: function(str) {
		//   example 1: ucfirst('kevin van zonneveld')
		//   returns 1: 'Kevin van zonneveld'

		str += '';
		var f = str.charAt(0).toUpperCase();
		return f + str.substr(1);
	},
	
	ucwords: function(str) {
		//   example 1: ucwords('kevin van  zonneveld')
		//   returns 1: 'Kevin Van  Zonneveld'
		//   example 2: ucwords('HELLO WORLD')
		//   returns 2: 'HELLO WORLD'
		//   example 3: ucwords('у мэри был маленький ягненок и она его очень любила')
		//   returns 3: 'У Мэри Был Маленький Ягненок И Она Его Очень Любила'
		//   example 4: ucwords('τάχιστη αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός')
		//   returns 4: 'Τάχιστη Αλώπηξ Βαφής Ψημένη Γη, Δρασκελίζει Υπέρ Νωθρού Κυνός'

		return (str + '')
			.replace(/^(.)|\s+(.)/g, function ($1) {
			return $1.toUpperCase()
		});
	},

	str_replace : function(search, replace, subject, countObj) {
		//      note 1: The countObj parameter (optional) if used must be passed in as a
		//      note 1: object. The count will then be written by reference into it's `value` property
		//   example 1: str_replace(' ', '.', 'Kevin van Zonneveld')
		//   returns 1: 'Kevin.van.Zonneveld'
		//   example 2: str_replace(['{name}', 'l'], ['hello', 'm'], '{name}, lars')
		//   returns 2: 'hemmo, mars'
		//   example 3: str_replace(Array('S','F'),'x','ASDFASDF')
		//   returns 3: 'AxDxAxDx'
		//   example 4: var countObj = {}
		//   example 4: str_replace(['A','D'], ['x','y'] , 'ASDFASDF' , countObj)
		//   example 4: var $result = countObj.value
		//   returns 4: 4
	  
		var i = 0
		var j = 0
		var temp = ''
		var repl = ''
		var sl = 0
		var fl = 0
		var f = [].concat(search)
		var r = [].concat(replace)
		var s = subject
		var ra = Object.prototype.toString.call(r) === '[object Array]'
		var sa = Object.prototype.toString.call(s) === '[object Array]'
		s = [].concat(s)
	  
		var $global = (typeof window !== 'undefined' ? window : global)
		$global.$locutus = $global.$locutus || {}
		var $locutus = $global.$locutus
		$locutus.php = $locutus.php || {}
	  
		if (typeof (search) === 'object' && typeof (replace) === 'string') {
		  temp = replace
		  replace = []
		  for (i = 0; i < search.length; i += 1) {
			replace[i] = temp
		  }
		  temp = ''
		  r = [].concat(replace)
		  ra = Object.prototype.toString.call(r) === '[object Array]'
		}
	  
		if (typeof countObj !== 'undefined') {
		  countObj.value = 0
		}
	  
		for (i = 0, sl = s.length; i < sl; i++) {
		  if (s[i] === '') {
			continue
		  }
		  for (j = 0, fl = f.length; j < fl; j++) {
			temp = s[i] + ''
			repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0]
			s[i] = (temp).split(f[j]).join(repl)
			if (typeof countObj !== 'undefined') {
			  countObj.value += ((temp.split(f[j])).length - 1)
			}
		  }
		}
		return sa ? s : s[0]
	},





	// ---------------------------------------------------------------------------------------------
	// JSON
	// ---------------------------------------------------------------------------------------------

	json_encode : function(mixedVal) {
		//        example 1: json_encode('Kevin')
		//        returns 1: '"Kevin"'

		/*
		https://www.JSON.org/json2.js
		2008-11-19
		Public Domain.
		NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.
		See https://www.JSON.org/js.html
		*/

		var $global = (typeof window !== 'undefined' ? window : global)
		$global.$locutus = $global.$locutus || {}
		var $locutus = $global.$locutus
		$locutus.php = $locutus.php || {}

		var json = $global.JSON
		var retVal
		try {
			if (typeof json === 'object' && typeof json.stringify === 'function') {
				// Errors will not be caught here if our own equivalent to resource
				retVal = json.stringify(mixedVal)
				if (retVal === undefined) {
					throw new SyntaxError('json_encode')
				}
				return retVal
			}

			var value = mixedVal

			var quote = function (string) {
				var escapeChars = [
					'\u0000-\u001f',
					'\u007f-\u009f',
					'\u00ad',
					'\u0600-\u0604',
					'\u070f',
					'\u17b4',
					'\u17b5',
					'\u200c-\u200f',
					'\u2028-\u202f',
					'\u2060-\u206f',
					'\ufeff',
					'\ufff0-\uffff'
				].join('')
				var escapable = new RegExp('[\\"' + escapeChars + ']', 'g')
				var meta = {
					// table of character substitutions
					'\b': '\\b',
					'\t': '\\t',
					'\n': '\\n',
					'\f': '\\f',
					'\r': '\\r',
					'"': '\\"',
					'\\': '\\\\'
				}

				escapable.lastIndex = 0
				return escapable.test(string) ? '"' + string.replace(escapable, function (a) {
					var c = meta[a]
					return typeof c === 'string' ? c : '\\u' + ('0000' + a.charCodeAt(0)
					.toString(16))
					.slice(-4)
				}) + '"' : '"' + string + '"'
			}

			var _str = function (key, holder) {
				var gap = ''
				var indent = '    '
				// The loop counter.
				var i = 0
				// The member key.
				var k = ''
				// The member value.
				var v = ''
				var length = 0
				var mind = gap
				var partial = []
				var value = holder[key]

				// If the value has a toJSON method, call it to obtain a replacement value.
				if (value && typeof value === 'object' && typeof value.toJSON === 'function') {
					value = value.toJSON(key)
				}

				// What happens next depends on the value's type.
				switch (typeof value) {
					case 'string':
						return quote(value)

					case 'number':
						// JSON numbers must be finite. Encode non-finite numbers as null.
						return isFinite(value) ? String(value) : 'null'

					case 'boolean':
						// If the value is a boolean or null, convert it to a string.
						return String(value)

					case 'object':
						// If the type is 'object', we might be dealing with an object or an array or
						// null.
						// Due to a specification blunder in ECMAScript, typeof null is 'object',
						// so watch out for that case.
						if (!value) {
							return 'null'
						}

						// Make an array to hold the partial results of stringifying this object value.
						gap += indent
						partial = []

						// Is the value an array?
						if (Object.prototype.toString.apply(value) === '[object Array]') {
							// The value is an array. Stringify every element. Use null as a placeholder
							// for non-JSON values.
							length = value.length
							for (i = 0; i < length; i += 1) {
								partial[i] = _str(i, value) || 'null'
							}

							// Join all of the elements together, separated with commas, and wrap them in
							// brackets.
							v = partial.length === 0 ? '[]' : gap
							? '[\n' + gap + partial.join(',\n' + gap) + '\n' + mind + ']'
							: '[' + partial.join(',') + ']'
							// gap = mind // not used
							return v
						}

						// Iterate through all of the keys in the object.
						for (k in value) {
							if (Object.hasOwnProperty.call(value, k)) {
								v = _str(k, value)
								if (v) {
									partial.push(quote(k) + (gap ? ': ' : ':') + v)
								}
							}
						}

						// Join all of the member texts together, separated with commas,
						// and wrap them in braces.
						v = partial.length === 0 ? '{}' : gap
							? '{\n' + gap + partial.join(',\n' + gap) + '\n' + mind + '}'
							: '{' + partial.join(',') + '}'
						// gap = mind // Not used
						return v
					case 'undefined':
					case 'function':
					default:
						throw new SyntaxError('json_encode')
				}
			}

			// Make a fake root object containing our value under the key of ''.
			// Return the result of stringifying the value.
			return _str('', {
				'': value
			})
		} catch (err) {
			// @todo: ensure error handling above throws a SyntaxError in all cases where it could
			// (i.e., when the JSON global is not available and there is an error)
			if (!(err instanceof SyntaxError)) {
				throw new Error('Unexpected error type in json_encode()')
			}
			// usable by json_last_error()
			$locutus.php.last_error_json = 4
			return null
		}
	},

	json_decode : function(strJson) {
		//           note 1: If node or the browser does not offer JSON.parse,
		//           note 1: this function falls backslash
		//           note 1: to its own implementation using eval, and hence should be considered unsafe
		//        example 1: json_decode('[ 1 ]')
		//        returns 1: [1]

		/*
		https://www.JSON.org/json2.js
		2008-11-19
		Public Domain.
		NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.
		See https://www.JSON.org/js.html
		*/

		var $global = (typeof window !== 'undefined' ? window : global)
		$global.$locutus = $global.$locutus || {}
		var $locutus = $global.$locutus
		$locutus.php = $locutus.php || {}

		var json = $global.JSON
		if (typeof json === 'object' && typeof json.parse === 'function') {
			try {
				return json.parse(strJson)
			} catch (err) {
				if (!(err instanceof SyntaxError)) {
					throw new Error('Unexpected error type in json_decode()')
				}

				// usable by json_last_error()
				$locutus.php.last_error_json = 4
				return null
			}
		}

		var chars = [
			'\u0000',
			'\u00ad',
			'\u0600-\u0604',
			'\u070f',
			'\u17b4',
			'\u17b5',
			'\u200c-\u200f',
			'\u2028-\u202f',
			'\u2060-\u206f',
			'\ufeff',
			'\ufff0-\uffff'
		].join('')
		var cx = new RegExp('[' + chars + ']', 'g')
		var j
		var text = strJson

		// Parsing happens in four stages. In the first stage, we replace certain
		// Unicode characters with escape sequences. JavaScript handles many characters
		// incorrectly, either silently deleting them, or treating them as line endings.
		cx.lastIndex = 0
		if (cx.test(text)) {
			text = text.replace(cx, function (a) {
				return '\\u' + ('0000' + a.charCodeAt(0)
					.toString(16))
					.slice(-4)
			})
		}

		// In the second stage, we run the text against regular expressions that look
		// for non-JSON patterns. We are especially concerned with '()' and 'new'
		// because they can cause invocation, and '=' because it can cause mutation.
		// But just to be safe, we want to reject all unexpected forms.
		// We split the second stage into 4 regexp operations in order to work around
		// crippling inefficiencies in IE's and Safari's regexp engines. First we
		// replace the JSON backslash pairs with '@' (a non-JSON character). Second, we
		// replace all simple value tokens with ']' characters. Third, we delete all
		// open brackets that follow a colon or comma or that begin the text. Finally,
		// we look to see that the remaining characters are only whitespace or ']' or
		// ',' or ':' or '{' or '}'. If that is so, then the text is safe for eval.

		var m = (/^[\],:{}\s]*$/)
			.test(text.replace(/\\(?:["\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@')
			.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?/g, ']')
			.replace(/(?:^|:|,)(?:\s*\[)+/g, ''))

		if (m) {
			// In the third stage we use the eval function to compile the text into a
			// JavaScript structure. The '{' operator is subject to a syntactic ambiguity
			// in JavaScript: it can begin a block or an object literal. We wrap the text
			// in parens to eliminate the ambiguity.
			j = eval('(' + text + ')') // eslint-disable-line no-eval
			return j
		}

		// usable by json_last_error()
		$locutus.php.last_error_json = 4
		return null
	},














	// ---------------------------------------------------------------------------------------------
	// URL STRING
	// ---------------------------------------------------------------------------------------------

	base64_decode : function (encodedData) {
		//   example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==')
		//   returns 1: 'Kevin van Zonneveld'
		//   example 2: base64_decode('YQ==')
		//   returns 2: 'a'
		//   example 3: base64_decode('4pyTIMOgIGxhIG1vZGU=')
		//   returns 3: '✓ à la mode'

		// decodeUTF8string()
		// Internal function to decode properly UTF8 string
		// Adapted from Solution #1 at https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/Base64_encoding_and_decoding
		var decodeUTF8string = function (str) {
			// Going backwards: from bytestream, to percent-encoding, to original string.
			return decodeURIComponent(str.split('').map(function (c) {
				return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
			}).join(''))
		}

		if (typeof window !== 'undefined') {
			if (typeof window.atob !== 'undefined') {
				return decodeUTF8string(window.atob(encodedData))
			}
		} else {
			return new Buffer(encodedData, 'base64').toString('utf-8')
		}

		var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/='
		var o1
		var o2
		var o3
		var h1
		var h2
		var h3
		var h4
		var bits
		var i = 0
		var ac = 0
		var dec = ''
		var tmpArr = []

		if (!encodedData) {
			return encodedData
		}

		encodedData += ''

		do {
			// unpack four hexets into three octets using index points in b64
			h1 = b64.indexOf(encodedData.charAt(i++))
			h2 = b64.indexOf(encodedData.charAt(i++))
			h3 = b64.indexOf(encodedData.charAt(i++))
			h4 = b64.indexOf(encodedData.charAt(i++))

			bits = h1 << 18 | h2 << 12 | h3 << 6 | h4

			o1 = bits >> 16 & 0xff
			o2 = bits >> 8 & 0xff
			o3 = bits & 0xff

			if (h3 === 64) {
				tmpArr[ac++] = String.fromCharCode(o1)
			} else if (h4 === 64) {
				tmpArr[ac++] = String.fromCharCode(o1, o2)
			} else {
				tmpArr[ac++] = String.fromCharCode(o1, o2, o3)
			}
		} while (i < encodedData.length)

		dec = tmpArr.join('')

		return decodeUTF8string(dec.replace(/\0+$/, ''))
	},

	base64_encode : function (stringToEncode) {
		//   example 1: base64_encode('Kevin van Zonneveld')
		//   returns 1: 'S2V2aW4gdmFuIFpvbm5ldmVsZA=='
		//   example 2: base64_encode('a')
		//   returns 2: 'YQ=='
		//   example 3: base64_encode('✓ à la mode')
		//   returns 3: '4pyTIMOgIGxhIG1vZGU='

		// encodeUTF8string()
		// Internal function to encode properly UTF8 string
		// Adapted from Solution #1 at https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/Base64_encoding_and_decoding
		var encodeUTF8string = function (str) {
			// first we use encodeURIComponent to get percent-encoded UTF-8,
			// then we convert the percent encodings into raw bytes which
			// can be fed into the base64 encoding algorithm.
			return encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
				function toSolidBytes (match, p1) {
					return String.fromCharCode('0x' + p1)
				}
			)
		}

		if (typeof window !== 'undefined') {
			if (typeof window.btoa !== 'undefined') {
				return window.btoa(encodeUTF8string(stringToEncode))
			}
		} else {
			return new Buffer(stringToEncode).toString('base64')
		}

		var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/='
		var o1
		var o2
		var o3
		var h1
		var h2
		var h3
		var h4
		var bits
		var i = 0
		var ac = 0
		var enc = ''
		var tmpArr = []

		if (!stringToEncode) {
			return stringToEncode
		}

		stringToEncode = encodeUTF8string(stringToEncode)

		do {
			// pack three octets into four hexets
			o1 = stringToEncode.charCodeAt(i++)
			o2 = stringToEncode.charCodeAt(i++)
			o3 = stringToEncode.charCodeAt(i++)

			bits = o1 << 16 | o2 << 8 | o3

			h1 = bits >> 18 & 0x3f
			h2 = bits >> 12 & 0x3f
			h3 = bits >> 6 & 0x3f
			h4 = bits & 0x3f

			// use hexets to index into b64, and append result to encoded string
			tmpArr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4)
		} while (i < stringToEncode.length)

		enc = tmpArr.join('')

		var r = stringToEncode.length % 3

		return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3)
	}






}, PHP = php;
