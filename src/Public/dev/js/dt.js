/*
	This library is for DateTime functions.
	It heavily relies on `dayjs` & its corresponding plugins:
		utc, timezone, customParseFormat
*/

'use strict';

const dt = {

	translatePHPFormat : function(PHP_DT_format) {
		let translated_format = '';
		let bool1 = ($PHP.gettype(PHP_DT_format)=='string');
		if(!bool1) return dt;

		let subject = PHP_DT_format.split('');
		let translations = {
			'Y' : 'YYYY',   // 0000 or 9999

			'M' : 'MMM',    // Jan through Dec
			'm' : 'MM',     // 01 through 12
			'F' : 'MMMM',   // January through December
			'n' : 'M',      // 1 through 12

			'D' : 'ddd',    // Mon through Sun
			'd' : 'DD',     // 01 to 31
			'l' : 'dddd',   // Sunday through Saturday
			'j' : 'D',      // 1 to 31

			'A' : 'A',      // AM or PM
			'a' : 'a',      // am or pm

			'H' : 'HH',     // 00 through 23
			'h' : 'hh',     // 01 through 12
			'G' : 'H',      // 0 through 23
			'g' : 'h',      // 1 through 12

			'i' : 'mm',     // 00 to 59

			's' : 'ss',     // 00 through 59

			'u' : 'SSSSSS', // 000000 to 999999
			'v' : 'SSS',    // 000 to 999
		};

		$.each(subject, function(k, v){
			translated_format += ($PHP.isset(translations[v])) ? translations[v] : v;
		});
		return translated_format;
	},

	now : function() {
		let now = dayjs();
		return now;
	},

	isDayJSObject : function(dt) {
		return $PHP.gettype(dt)=='object' && dayjs(dt).isValid();
	},

	isDTString : function(php_dt_format, dt_str) {
		let is_valid = false;
		let bool1 = (
			$PHP.gettype(php_dt_format)=='string'
			&& $PHP.gettype(dt_str)=='string'
			&& !$PHP.empty($PHP.trim(php_dt_format))
			&& !$PHP.empty($PHP.trim(dt_str))
		);
		if(!bool1)
			return is_valid;
		let dt_format = $DT.translatePHPFormat(php_dt_format);
		try {
			bool1 = dayjs(dt_str, dt_format, true).isValid(); // strict mode
		} catch(err) {}
		return bool1;
	},

	isTZString : function(tz_str) {
		let bool1 = false;
		if($PHP.gettype(tz_str)!='string' || $PHP.empty($PHP.trim(tz_str)))
			return bool1;
		tz_str = $PHP.trim(tz_str);
		try {
			dt_test = dayjs("2000-01-01", "YYYY-MM-DD", true).tz(tz_str, true); // strict mode
			bool1 = (!$PHP.empty(dt_test) && $PHP.gettype(dt_test)=='object');
		} catch(err) {}
		return bool1;
	},

	createDateTime : function(php_dt_format, dt_str, tz_str='') {
		tz_str = $PHP.trim(tz_str);
		let clientTZ = $DT.clientTZ();
		let dt = null;
		let bool1 = (
			$PHP.gettype(php_dt_format)=='string' 
			&& $PHP.gettype(dt_str)=='string' 
			&& (!$PHP.empty(tz_str) ? $DT.isTZString(tz_str) : true)
			&& $DT.isTZString(clientTZ) // also validate client's current TZ
		);
		if(!bool1) return dt;

		let tz = (!$PHP.empty(tz_str) ? tz_str : clientTZ);
		let dt_format = $DT.translatePHPFormat(php_dt_format);
		let dt_test = dayjs.tz(dt_str, dt_format, tz); //strict mode
		bool1 = ($PHP.gettype(dt_test)=='object' && dt_test.isValid());
		if(!bool1) return dt;

		dt = dt_test;
		return dt;
	},

	format : function(php_dt_format, dt) {
		let output = '';
		if($PHP.gettype(php_dt_format)!='string' || !$DT.isDayJSObject(dt))
			return output;
		let format = $DT.translatePHPFormat(php_dt_format);
		return dt.format(format);
	},

	clientTZ : function() {
		let tz_str = dayjs.tz.guess();
		return tz_str;
	}




	
}, DT = dt;
