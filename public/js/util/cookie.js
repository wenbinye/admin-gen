define(["jquery"], function($) {
    var cookie = {};
    cookie.defaults = {};

    var pluses = /\+/g;

    function encode(s) {
        return cookie.raw ? s : encodeURIComponent(s);
    }

    function decode(s) {
        return cookie.raw ? s : decodeURIComponent(s);
    }

    function stringifyCookieValue(value) {
        return encode(cookie.json ? JSON.stringify(value) : String(value));
    }

    function parseCookieValue(s) {
        if (s.indexOf('"') === 0) {
	        // This is a quoted cookie as according to RFC2068, unescape...
	        s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
        }

        try {
	        // Replace server-side written pluses with spaces.
	        // If we can't decode the cookie, ignore it, it's unusable.
	        // If we can't parse the cookie, ignore it, it's unusable.
	        s = decodeURIComponent(s.replace(pluses, ' '));
	        return cookie.json ? JSON.parse(s) : s;
        } catch(e) {
            return undefined;
        }
    }

    function read(s, converter) {
        var value = cookie.raw ? s : parseCookieValue(s);
        return $.isFunction(converter) ? converter(value) : value;
    }

    cookie.read = function(key, converter) {
        // Read

        var result = key ? undefined : {};

        // To prevent the for loop in the first place assign an empty array
        // in case there are no cookies at all. Also prevents odd result when
        // calling $.cookie().
        var cookies = document.cookie ? document.cookie.split('; ') : [];

        for (var i = 0, l = cookies.length; i < l; i++) {
	        var parts = cookies[i].split('=');
	        var name = decode(parts.shift());
	        var value = parts.join('=');

	        if (key && key === name) {
	            // If second argument (value) is a function it's a converter...
	            result = read(value, converter);
	            break;
	        }

	        // Prevent storing a cookie that we couldn't decode.
	        if (!key && (value = read(value)) !== undefined) {
	            result[name] = value;
	        }
        }

        return result;
    }

    cookie.write = function(key, value, options) {
        options = $.extend({}, cookie.defaults, options);

        if (typeof options.expires === 'number') {
	        var seconds = options.expires, t = options.expires = new Date();
	        t.setTime(+t + seconds * 1000);
        }

        return (document.cookie = [
	        encode(key), '=', stringifyCookieValue(value),
	        options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
	        options.path    ? '; path=' + options.path : '',
	        options.domain  ? '; domain=' + options.domain : '',
	        options.secure  ? '; secure' : ''
        ].join(''));
    }

    cookie.remove =  function (key, options) {
        if (cookie.read(key) === undefined) {
	        return false;
        }

        // Must not alter options, thus extending a fresh object...
        cookie.write(key, '', $.extend({}, options, { expires: -1 }));
        return !cookie.read(key);
    };

    return cookie;
});
