(function(){
var _4="<!!!>";
var _5="<!!!>";
var _6 = 60;
var max_length = 1024;
var interface_messages={
	alt:             "Якщо Ви побачили граматичну або синтаксичну помилку, будь ласка, виділіть її мишкою та натисніть Ctrl + Enter",
	badbrowser:      "Ваш браузер не підтримує можливість перехоплення виділеного тексту.",
	toobig:          "Виділений фрагмент занадто великий.",
	docmsg:          "Документ:",
	intextmsg:       "Орфографічна помилка в тексті:",
	send:            "Відправити",
	cancel:          "Скасувати",
	entercmnt:       "Коментар для автора (необов'язково):",
	after_send_html: 'Дякуємо за уважність!'
};
var _a = 0;
var _f = null;
var _10 = {};
var _11 = false;
var _12 = "";
var _1b = function (element) {
	element.style.position = "absolute";
	element.style.top = "-10000px";
	if (document.body.lastChild) {
		document.body.insertBefore(element, document.body.lastChild);
	} else {
		document.body.appendChild(element);
	}
};
var send = function (obj, user_comm) {
	var data = {
		c_pre: obj.pre,
		c_sel: obj.text,
		c_suf: obj.suf,
		c_pos: obj.pos,
		c_tag1: _4,
		c_tag2: _5,
		url: top.location.href,
		charset: document.charset || document.characterSet || "",
		comment: user_comm
	};
	$.ajax({
		type: "POST",
		url: BASE_URL + "/ajax/json/error_report",
		data: data
	});
};
var _29 = function () {
	var clientWidth = 0,
		innerHeight = 0;
	if (typeof (window.innerWidth) == "number") {
		clientWidth = window.innerWidth;
		innerHeight = window.innerHeight;
	} else {
		if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
			clientWidth = document.documentElement.clientWidth;
			innerHeight = document.documentElement.clientHeight;
		} else {
			if (document.body && (document.body.clientWidth || document.body.clientHeight)) {
				clientWidth = document.body.clientWidth;
				innerHeight = document.body.clientHeight;
			}
		}
	}
	var _2c = 0,
		_2d = 0;
	if (typeof (window.pageYOffset) == "number") {
		_2d = window.pageYOffset;
		_2c = window.pageXOffset;
	} else {
		if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
			_2d = document.body.scrollTop;
			_2c = document.body.scrollLeft;
		} else {
			if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
				_2d = document.documentElement.scrollTop;
				_2c = document.documentElement.scrollLeft;
			}
		}
	}
	return {
		w: clientWidth,
		h: innerHeight,
		x: _2c,
		y: _2d
	};
};
_10.confirm = function (_2e, _2f, _30) {
	var ts = new Date().getTime();
	var _32 = confirm(interface_messages.docmsg + "\n   " + document.location.href + "\n" + interface_messages.intextmsg + "\n   \"" + _2e + "\"");
	var dt = new Date().getTime() - ts;
	if (_32) {
		_2f("");
	} else {
		if (!_30 && dt < 50) {
			var sv = document.onkeyup;
			document.onkeyup = function (e) {
				if (!e) {
					e = window.event;
				}
				if (e.keyCode == 17) {
					document.onkeyup = sv;
					_10.confirm(_2e, _2f, true);
				}
			};
		}
	}
};
_10.css = function (_36, _37) {
	if (_11) {
		return;
	}
	_11 = true;
	var div = document.createElement("div");
	var w = 550;
	if (w > document.body.clientWidth - 10) {
		w = document.body.clientWidth - 10;
	}
	div.style.zIndex = "10001";
	div.innerHTML = "<div class=\"error-iframe\" style=\"width:" + w + "px;\">"
	                   + "<div class=\"title\">" + interface_messages.intextmsg + "</div>"
	                   + "<div style=\"padding: 0 0 1em 1em\">" + _36.replace(_4, "<u style=\"color:red\">").replace(_5, "</u>") + "</div>"
	                   + "<div style=\"padding: 0 0 1em 0\"></div>"
	                   + "<form>"
		                  + "<div id=\"before_send_html\">"
		                       + "<div>" + interface_messages.entercmnt + "</div>"
		                       + "<input type=\"text\" style=\"width:"+ (w - 40) +"px;\" class=\"txt\" />"
		                       + "<div>"
			                       + "<div style=\"width:50%; float:right;\" class=\"align-right\">"
				                       + "<input type=\"submit\" onclick=\"$('#before_send_html').remove(); $('#after_send_html').css('display', 'block');\" value=\"" + interface_messages.send + "\" style=\"width:9em;\" class=\"btn green small bold\">"
				                   + "</div>"
			                       + "<div style=\"width:50%;\">"
				                       + "<input type=\"button\" value=\"" + interface_messages.cancel + "\" style=\"width:9em\" class=\"btn small bold\">"
				                   + "</div>"
			                   + "</div>"
		                   + "</div>"
		                   + '<div id="after_send_html" style="display:none; color:#01b671; font-weight:bold; font-size:16px;"><div>' + interface_messages.after_send_html + '</div><div><div style="width:100%; padding-top:58px; display:inline-block;" class="align-right"><input type="submit" value="Закрити" style="width:9em;" class="btn green small bold" onclick="$(\'.error-iframe\').parent().remove(); return false;" /></div><div></div>'
		               + "</form>"
		           + "</div>";
	_1b(div);
	var _3a = div.getElementsByTagName("input");
	var _3b = div.getElementsByTagName("form");
	var t = _3a[0];
	var _3d = null;
	var _3e = [];
	var _3f = function () {
		document.onkeydown = _3d;
		_3d = null;
		div.parentNode.removeChild(div);
		for (var i = 0; i < _3e.length; i++) {
			_3e[i][0].style.visibility = _3e[i][1];
		}
		_11 = false;
		_12 = t.value;
	};
	var pos = function (p) {
		var s = {
			x: 0,
			y: 0
		};
		while (p.offsetParent) {
			s.x += p.offsetLeft;
			s.y += p.offsetTop;
			p = p.offsetParent;
		}
		return s;
	};
	setTimeout(function () {
		var w = div.clientWidth;
		var h = div.clientHeight;
		var dim = _29();
		var x = (dim.w - w) / 2 + dim.x;
		if (x < 10) {
			x = 10;
		}
		var y = (dim.h - h) / 2 + dim.y - 10;
		if (y < 10) {
			y = 10;
		}
		div.style.left = x + "px";
		div.style.top = y + "px";
		if (navigator.userAgent.match(/MSIE (\d+)/) && RegExp.$1 < 7) {
			var _49 = document.getElementsByTagName("SELECT");
			for (var i = 0; i < _49.length; i++) {
				var s = _49[i];
				var p = pos(s);
				if (p.x > x + w || p.y > y + h || p.x + s.offsetWidth < x || p.y + s.offsetHeight < y) {
					continue;
				}
				_3e[_3e.length] = [s, s.style.visibility];
				s.style.visibility = "hidden";
			}
		}
		t.value = _12;
		t.focus();
		t.select();
		_3d = document.onkeydown;
		document.onkeydown = function (e) {
			if (!e) {
				e = window.event;
			}
			if (e.keyCode == 27) {
				_3f();
			}
		};
		_3b[0].onsubmit = function () {
			_37(t.value);
			_3f();
			_12 = "";
			return false;
		};
		_3a[2].onclick = function () {
			_3f();
		};
	}, 10);
};
var _4e = function (_4f) {
	return ("" + _4f).replace(/[\r\n]+/g, " ").replace(/^\s+|\s+$/g, "");
};
var _50 = function () {
	try {
		var _51 = null;
		var _52 = null;
		if (window.getSelection) {
			_52 = window.getSelection();
		} else {
			if (document.getSelection) {
				_52 = document.getSelection();
			} else {
				_52 = document.selection;
			}
		}
		var _53 = null;
		if (_52 != null) {
			var pre = "",
				_51 = null,
				suf = "",
				pos = -1;
			if (_52.getRangeAt) {
				var r = _52.getRangeAt(0);
				_51 = r.toString();
				var _58 = document.createRange();
				_58.setStartBefore(r.startContainer.ownerDocument.body);
				_58.setEnd(r.startContainer, r.startOffset);
				pre = _58.toString();
				var _59 = r.cloneRange();
				_59.setStart(r.endContainer, r.endOffset);
				_59.setEndAfter(r.endContainer.ownerDocument.body);
				suf = _59.toString();
			} else {
				if (_52.createRange) {
					var r = _52.createRange();
					_51 = r.text;
					var _58 = _52.createRange();
					_58.moveStart("character", -_6);
					_58.moveEnd("character", -_51.length);
					pre = _58.text;
					var _59 = _52.createRange();
					_59.moveEnd("character", _6);
					_59.moveStart("character", _51.length);
					suf = _59.text;
				} else {
					_51 = "" + _52;
				}
			}
			var p;
			var s = (p = _51.match(/^(\s*)/)) && p[0].length;
			var e = (p = _51.match(/(\s*)$/)) && p[0].length;
			pre = pre + _51.substring(0, s);
			suf = _51.substring(_51.length - e, _51.length) + suf;
			_51 = _51.substring(s, _51.length - e);
			if (_51 == "") {
				return null;
			}
			return {
				pre: pre,
				text: _51,
				suf: suf,
				pos: pos
			};
		} else {
			alert(interface_messages.badbrowser);
			return;
		}
	} catch (e) {
		return null;
	}
};
var _5d = function () {
	if (navigator.appName.indexOf("Netscape") != -1 && eval(navigator.appVersion.substring(0, 1)) < 5) {
		alert(interface_messages.badbrowser);
		return;
	}
	var _63 = _50();
	if (!_63)
		return;
	with(_63) {
		pre = pre.substring(pre.length - _6, pre.length).replace(/^\S{1,10}\s+/, "");
		suf = suf.substring(0, _6).replace(/\s+\S{1,10}$/, "");
	}
	var _64 = _4e(_63.pre + _4 + _63.text + _5 + _63.suf);
	if (_64.length > max_length) {
		alert(interface_messages.toobig);
		return;
	}
	_10['css'](_64, function (user_comm) {
		send(_63, user_comm);
	});
};
document.onkeypress = function (e) {
	var goodEvent = 0;
	if (window.event) {
		goodEvent = window.event.keyCode == 10 || (window.event.keyCode == 13 && window.event.ctrlKey);
	} else {
		if (e) {
			goodEvent = (e.which == 10 && e.modifiers == 2) || (e.keyCode == 0 && e.charCode == 106 && e.ctrlKey) || (e.keyCode == 13 && e.ctrlKey);
		}
	} if (goodEvent) {
		_5d();
		return false;
	}
};
})();