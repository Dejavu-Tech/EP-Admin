define(['jquery', 'jquery.gcjs', 'foxui', 'tpl'], function ($, gc, FoxUI, tpl) {

	tpl.helper("timeformat", function (content) {
		if (isNaN(content)) {
			return content;
		}
		return $.format( new Date(content), true);
	});

	$.formatDate = function (now, hasTime) {
		var year = now.getYear();
		var month = now.getMonth() + 1;
		var date = now.getDate();
		var hour = now.getHours();
		var minute = now.getMinutes();

		if (hasTime) {
			return   year + "-" + month + "-" + date + "   " + hour + ":" + minute;
		}
		return year + "-" + month + "-" + date;
	};
	$.urlencode = function (clearString)
	{
		var output = '';
		var x = 0;

		clearString = utf16to8(clearString.toString());
		var regex = /(^[a-zA-Z0-9-_.]*)/;

		while (x < clearString.length)
		{
			var match = regex.exec(clearString.substr(x));
			if (match != null && match.length > 1 && match[1] != '')
			{
				output += match[1];
				x += match[1].length;
			}
			else
			{
				if (clearString[x] == ' ')
				{
					output += '+';
				}
				else
				{
					var charCode = clearString.charCodeAt(x);
					var hexVal = charCode.toString(16);
					output += '%' + (hexVal.length < 2 ? '0' : '') + hexVal.toUpperCase();
				}
				x++;
			}
		};

		function utf16to8(str) {
			var out, i, len, c;

			out = "";
			len = str.length;
			for (i = 0; i < len; i++)
			{
				c = str.charCodeAt(i);
				if ((c >= 0x0001) && (c <= 0x007F))
				{
					out += str.charAt(i);
				}
				else if (c > 0x07FF)
				{
					out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F));
					out += String.fromCharCode(0x80 | ((c >> 6) & 0x3F));
					out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
				}
				else
				{
					out += String.fromCharCode(0xC0 | ((c >> 6) & 0x1F));
					out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
				}
			}
			return out;
		}
		return output;
	};

	$.urldecode = function (encodedString)
	{
		var output = encodedString;
		var binVal, thisString;
		var myregexp = /(%[^%]{2})/;
		function utf8to16(str)
		{
			var out, i, len, c;
			var char2, char3;

			out = "";
			len = str.length;
			i = 0;
			while (i < len)
			{
				c = str.charCodeAt(i++);
				switch (c >> 4)
				{
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
						out += str.charAt(i - 1);
						break;
					case 12:
					case 13:
						char2 = str.charCodeAt(i++);
						out += String.fromCharCode(((c & 0x1F) << 6) | (char2 & 0x3F));
						break;
					case 14:
						char2 = str.charCodeAt(i++);
						char3 = str.charCodeAt(i++);
						out += String.fromCharCode(((c & 0x0F) << 12) |
								((char2 & 0x3F) << 6) |
								((char3 & 0x3F) << 0));
						break;
				}
			}
			return out;
		}
		while ((match = myregexp.exec(output)) != null
				&& match.length > 1
				&& match[1] != '')
		{
			binVal = parseInt(match[1].substr(1), 16);
			thisString = String.fromCharCode(binVal);
			output = output.replace(match[1], thisString);
		}

		output = output.replace(/\\+/g, " ");
		output = utf8to16(output);
		return output;
	};

	//商品组
	if ($('.fui-goods-group').length) {
		var resizeImages = function () {
			$('.fui-goods-group img').not(".exclude").each(function () {
				$(this).height($(this).width());
			})
		};
		window.onload = resizeImages;
		window.resize = resizeImages;
	}
	FoxUI.init();
});