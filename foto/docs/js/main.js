// Версия 0.1.7
// Дата 15.03.18

var __hash_save	= '';
var __debug		= false;
var __print_arr	= null;
var __mf_class	= 'm-fx';
var __ms_class	= 'm-ms';
var __fix_sel = '.m-nav';
var __ajax_count = 0;
//var __scroll = 0;
var __part_prop	= 'part_id';
var __part_pos	= 'part_pos';
var __part_pp = 'p';
var __part_ps = 'ps';
var __part_te = 'te';
var __part_tp = 'tp';

// Установить хэш данных
function _set_hash_save(hash_in)
{
	__hash_save = hash_in;
}

// Сгенерировать хэш данных
function _gen_hash_save(mode_set_in)
{
	var str_data = 0;
	var base = $("input[type!='submit'],select,textarea", "form[fsave]").get();
	var val, name;
	
	if(base) 
	{
		var arr_skip = ["act", "step", "p0", "p1", "p2", "p3", "p4", "ps", "pg"];
		var	str_skip = "," + arr_skip.join(",") + ",";
		var reg = /p[0-9]{1,3}/;
		
		for(i = 0; i < base.length; i ++)
		{
			name = String($(base[i]).attr("name"));
			class_name = String($(base[i]).attr("class"));
			type = String($(base[i]).attr("type"));
			
			a = name.match(reg);

			if(
				(a != null && a.length == 1 && a[0] == name)
				|| str_skip.indexOf("," + name + ",")	!= -1
				|| class_name.indexOf("__skip") 		!= -1 
				|| class_name.indexOf("__nosave") 		!= -1
				)
				{
					continue;
				}
				
			if(type == "radio" || type == "checkbox")
			{
				if(!$(base[i]).is(':checked') || class_name.indexOf("__save") == -1)
					continue;
			}
				
			val = base[i].value;
			
			if(val == "__skip" || val == "__nosave")
				continue;
			
			str_data += base[i].name + "=";
			str_data += val + "-";
		}
		
	}
	
	if(mode_set_in == true)
		_set_hash_save(str_data);
		
	return String(str_data);
}

// Обработчик клика
function form_click(obj_in, event_in)
{
	if(typeof(obj_in) != 'object')
		return;
	
	for(var k in obj_in)
	{
		var target = event_in.target || event_in.srcElement;
		var form = $(target).closest('form');
		var el = $(form).find('input[name="' + k + '"]');
		var v = obj_in[k];

		if(el && el.length)
			el.val(v);
		else if(form && form.length)
			form.append('<input class="__skip" type="hidden" name="' + k + '" value="' + v + '" />');
	}
}

String.prototype.pad = function(l, s, t)
{
	if ((l -= this.length) > 0)
	{
		if (!s) s = ' '; // по умолчанию строка заполнитель - пробел
		if (t == null) t = 1; // по умолчанию тип заполнения справа

		s = s.repeat(Math.ceil(l / s.length));
		var i = t==0 ? l : (t == 1? 0 : Math.floor(l / 2));
		s = s.substr(0, i) + this + s.substr(0, l - i);

		return s;
	}
	
	else return this;
}

// повторить заданную строку n раз
String.prototype.repeat = function(n)
{
	return new Array(n + 1).join(this);
}

function print_elem(sel_in)
{
	var arr = $(sel_in).get();
	k = 0;
	__print_arr = [];
	
	for(i = 0; i < arr.length; i ++)
	{
		cur = arr[i];		
		k = 0;
		
		while(1)
		{
			sel = $(cur).parent().get(0);
			
			if(!sel || sel.tagName == 'BODY')
				break;

			$(sel).contents().filter(function(){
				
				if(this != cur && $(this).is(':visible'))
				{
					$(this).hide();
					__print_arr.push(this);
				}
			
			});	
			
			k ++;
			
			if(k > 100)
				break;
				
			cur = sel;	
		}
	}
}

function print_part_exit()
{
	for(i = 0; __print_arr && i < __print_arr.length; i ++)
		$(__print_arr[i]).show();

	$('body').children().show();	
	$('.print-part').remove();
}

function print_part_menu()
{
	return '<div class="menu-print-part"><button onclick="print_part_exit(); return false;">Выход</button><button onclick="window.print(); return false;">Печать</button><div>';
}

function print_part(code_in)
{
	if(code_in)
	{
		print_elem(code_in);
		$(code_in).after(print_part_menu());
	}
	else 
	{	
		if(typeof(print_data) == 'function')
			code = print_data();
		else
			code = 'Ошибка';

		$('body').children().hide().end().append(code);
	}
}

function get_cookie(name) 
{ 
    var dc = document.cookie;
	var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    
	if (begin == -1) 
	{
        begin = dc.indexOf(prefix);
        if (begin != 0) 
			return false;
    } 
	else 
	{
        begin += 2;
    }
    
	var end = document.cookie.indexOf(";", begin);
    if (end == -1) 
	{
        end = dc.length;
    }
    
	return unescape(dc.substring(begin + prefix.length, end));
}

function set_cookie(cookieName, cookieValue, nDays) 
{ 
	var today = new Date();
	var expire = new Date();
	
	if (nDays == null || nDays == 0 || nDays == undefined) 
		nDays = 1;
	
	expire.setTime(today.getTime() + 3600000*24*nDays);
	document.cookie = cookieName + "=" + escape(cookieValue) + "; path=/; expires=" + expire.toGMTString();
}

function ext_prep()
{
	$('.ext-frame').each(function(){
		
		if(!$(this).parents('.no-plus').length)
			$(this).closest('.ext-title').find(':first').append('<span class="next">' + (($(this).closest('.ext-title').hasClass('ext-sel')  || $(this).is(':visible')) ? '-' : '+') + '</span>').find('span').on('click', function(){ $(this).text(this.innerText == '+' ? '-' : '+').parent().next('.ext-frame').toggle(); });
		
	});
}

function fix_update(sel_in, mode_in)
{
	var s = $(window).scrollTop();
	var e = $(sel_in).offset().top;
	var m = 0;
	
	if(s <= e)
		m = 1;
	else
	{
		if($('.m-fxp .m-fxp-temp').length)
		{
			if(mode_in < 1)
				return;

			m = 1;
		}
		
		m |= 2;
		
	}

	if(m & 1)
	{
		$('.m-fxp').find('.m-fxp-base').removeAttr('style').css({ position: 'static' }).end().find('.m-fxp-temp').remove();
		$('body').removeClass(__mf_class);
	}

	if(m & 2)
	{
		$('.m-fxp').each(function(){
			
			var sel = $(this);
			var w = sel.width();
			var h = sel.height();			
			var ct = sel.offset().top - e;
			var wh = $(window).height();
			var hh = 100*(wh - ct)/wh + '%';
			
			if(!$('body').hasClass(__ms_class))
			{
				sel.append('<div class="m-fxp-temp">&nbsp;</div>').find('.m-fxp-temp').width(w).height(h);
				sel.find('.m-fxp-base').css({ position: 'fixed', top: ct, width: w, 'max-height': hh});
			}
			
			$('body').addClass(__mf_class);
			
		});
	}
	
	if(is_small_screen())
	{
		menu_mob_update();
		//if($('body').hasClass(__ms_class))
		//	$(window).scrollTop(__scroll);
	}
}

function fix_prep()
{
	$(window).scroll(function(){ fix_update(__fix_sel, 0); });
	$(window).resize(function(){ fix_update(__fix_sel, 1); });
}

function dbg(data_in)
{
	console.log(data_in);
}

function msg(data_in)
{
	dlg(data_in);
}

function ajax_ok(replay_in, params_in)
{
	__ajax_count = 0;
	
	var arr_json 	= eval(replay_in);
	var stl			= arr_json.stl;
	var code 		= arr_json.code;
	var str_msg		= arr_json.msg;
	var debug 		= arr_json.debug;
	var script 		= arr_json.script;
	var title 		= arr_json.title;
	var kw			= 'keywords' in arr_json ? arr_json.keywords : '';
	var desc 		= 'description' in arr_json ? arr_json.description : '';
	
	var pos;
	var te, tp, ts;
	
	if(stl && (pos = stl.indexOf('>')) != -1)
	{
		var start = '\n/* ---start--- */\n', end = '\n/* ---end--- */\n', pos_start, pos_end;
		
		buf = $('style').text();		
		pos_start = buf.indexOf(start);
		pos_end = buf.indexOf(end);
		
		if(pos_start >= 0 && pos_end >= 0)
			buf = buf.substr(pos_start + start.length, pos_end - (pos_start + start.length));
		
		pos += 1;
		stl = stl.substr(0, pos) +  start + buf + end + stl.substr(pos);
		
		$('style').remove();
		$('head').append(stl);
	}
	
	if(typeof(params_in) == 'object')
	{
		te = params_in['target'];
		ts = params_in['step'];		
		tp = !ts ? 0 : params_in['pos'];
	}
	else
		te = params_in;
	
	if(typeof(te) == 'string' && te.length && te[0] != '#' && te[0] != '.')
		te = '#' + te;
	
	if(code)
	{
		var sel = $(te ? te : '.m-content-ins').last();
		
		dbg(tp);		
		code = $.trim(code);
		
		if(code && code.length >= 2 && code[0] == '{' && code[code.length - 1] == '}')
		{
			var arr_res = JSON.parse ? JSON.parse(code) : {}, code, nav;

			code = 'code' in arr_res ? arr_res.code : '';
			nav = 'nav' in arr_res ? arr_res.nav : '';

			if(nav.length)
				sel.next('div').html(nav);
		}

		if(tp == 1)
			sel.append(code);
		else if(tp == 2)
			sel.prepend(code);
		else
			sel.html(code);
	}
	
	$('.m-debug').remove();

	if(title != undefined && String(title).length)
	{
		document.title = title;
		$('meta[name="keywords"]').prop('content', kw);
		$('meta[name="description"]').prop('content', desc);
	}
	
	if(script && script.length > 0)
	{
		$('script').remove(); // ?
		
		var arr_script = script.split(',');
		for(i = 0; i < arr_script.length; i ++)
		{
			var str_script = "<script language=\"javascript\" src=\"" + arr_script[i]+ "\" type=\"text/javascript\"><" + "/script>";
			try 
			{
				$('head').append(str_script);
			}
			catch(e) 
			{
				dbg(e.name + ': ' + e.message);
			}
			
		}
	}
	
	// вывести сообщение
	if(str_msg)
		msg(str_msg);
	
	// обработать загруженную страницу
	if(typeof(startp) == 'function')
		startp();
}

function ajax_url(url_in, data_in, type_in)
{
	var url = url_in;
	var data = data_in ? data_in : {};
	var type = type_in ? type_in : 'json';
	var target = null, pos = 0, step = 0;

	if(__part_te in data_in)
		target = data_in[__part_te];	
	if(__part_tp in data_in)
		pos = data_in[__part_tp];
	if(__part_ps in data_in)
		step = data_in[__part_ps];

	//dbg(url);

	$.ajax({

		type:		'POST',
		url:		url,
		data:		data,
		dataType: 	type,

		success:	function(replay){
		
			ajax_ok(replay, { target: target, pos: pos, step: step });

		},
	   
		error:	function(replay){
			
			if(__ajax_count < 3)
			{
				//window.setInterval(ajax_url(url_in, data_in, type_in), 3000);
				window.setTimeout(ajax_url(url_in, data_in, type_in), 3000);
				__ajax_count ++;
			}
			else
			{
				alert('Сервер не ответил на отправленные запросы. Попробуйте повторить запрос.\n\nЕсли это сообщение появится повторно, попробуйте закрыть программу и запустить заново');
				__ajax_count = 0;
			}

			console.log(replay.status + ' - ' + replay.statusText);
		}
	});
	
	return false;
}

function ajax_start(event_in)
{
	var target = event_in.target || event_in.srcElement;
	var data = {}, url;
	var sel = $(target).closest('form');
	
	if(!sel[0].checkValidity())
	{
		dlg('Ошибка. Проверьте правильность ввода значений полей', 'red');		
		return false;
	}
	
	sel.find('input, textarea, select').each(function(){
	
		data[this.name] = this.value;
	
	});
	
	url = sel.attr('action');
	
	return ajax_url(url, data, 'json');
}

// Вывести сообщение
function dlg(data_in, cl_in)
{
	if($('.main-dlg').length)
		return;
	
	var str = '', url = null, cl = null, title = 'Сообщение', onclose = '', data = null;
	
	if(typeof(data_in) == 'object')
	{
		if('code' in data_in) str = data_in['code'];
		if('url' in data_in) url = data_in['url'];
		if('class' in data_in) cl = data_in['class'];
		if('title' in data_in) title = data_in['title'];
		if('onclose' in data_in) onclose = data_in['onclose'];
		if('data' in data_in) data = data_in['data'];
	}
	else
	{
		str = data_in ? data_in : '';
		cl = cl_in;
	}
	
	if(url && (!str || !str.length))
		str = 'Загрузка ...';
	
	if(onclose.length)
		onclose += ';';
	onclose += 'dlg_close();'
	
	var code = '';
	code = '<div class="main-dlg-bg"></div>';
	code += '<div class="main-dlg">';	
	code += '<div class="main-dlg-title">' + title + '<div class="main-dlg-title-close" onclick="' + onclose + '">x</div></div>';
	code += '<div class="main-dlg-body m-content-ins' + (cl && cl.length ? ' ' + cl : '') + '">' + str + '</div>';
	code += '<div class="main-dlg-nav"><button onclick="' + onclose + '">Ok</button></div>';	
	code += '</div>';
	
	$('body').append(code);	
	
	if(url)
		ajax_url(url, data);
	
	//alert($('.main-dlg').width());

	//var w = $('.main-dlg').width();
	var m = $('.main-dlg').width()/2;  //($(window).width() - $('.main-dlg').width())/2;
	
	//alert(screen.width);
	//alert($('.main-dlg').width());
	
	$('.main-dlg').css('margin-left', -m);
}

function dlg_close()
{
	$('.main-dlg').remove();
	$('.main-dlg-bg').remove();
}

function prep_save()
{
	_gen_hash_save(true);
		
	$('input[type="submit"], button', 'form[fsave]').on('click', function(event) {

		var res = false;
		var cur = _gen_hash_save(false);
		var target = event.target || event.srcElement;
		
		if($(target).is('[fsave], [fskip]'))
			return true;
		
		if(cur != __hash_save || $(this).closest('form[finsert]').length)
		{
			if(__debug)
			{
				alert(String(__hash_save).length + " " + String(cur).length);					
				alert(__hash_save + "\n---\n" + cur);
			}
			
			$(this).closest('form[fsave]').find('[fsave]').css({'border': 'solid 1px red', 'background-color': '#FEE'});				
			res = confirm('Данные формы были изменены.\n\nНажмите OK, если хотите вернуться и сохранить изменения.\n');
		}
		
		if(res)
		{
			event.preventDefault();
			return false;
		}
		
	});
}

function prep_ajax()
{
	//$('form input[name="rt"]').closest('form').find('[type="submit"]').on('click', function(e){ ajax_start(e); return false; })
	$('form input[name="rt"]').closest('form').on('submit', function(e){ ajax_start(e); return false; });
}

function prep_format()
{
	wh = $(window).height();
	bh = $('body').height();
	mbh = $('.m-body').height();
	
	$('.m-body').css('height', wh > bh ? mbh + (wh - bh) + 'px' : 'auto');
}

function is_small_screen()
{
	return $('body').width() <= 800;
}

function menu_mob_update()
{
	//var wh = $(window).height();
	//$('.m-nav .menu').first().height(wh + 'px');
	
	var sel, code;
	
	sel = $(__fix_sel + ' .path'), cl = 'menu-mob';	
	if(!sel.find('.' + cl).length)
	{
		code = '<span class="' + cl + '" title="Показать/скрыть меню" onclick="menu_mob(1);">≡</span>';
		sel.prepend(code);
	}
	
	sel = $(__fix_sel + ' .menu:first'), cl = 'mob-menu-close';	
	if(!sel.find('.' + cl).length)
	{
		code = '<div class="' + cl + '"><span onclick="menu_mob(1);">x</span></div>';	
		sel.prepend(code).append(code);
	}
	
}

function menu_mob(mode_in)
{
	if(!is_small_screen())
	{
		var st = is_small_screen() ? Number(get_cookie('ms')) : 0;
		//var st = Number(get_cookie('ms'));
		
		sv = st;
		
		if(mode_in)
		{
			st = st ? 0 : 1;
			set_cookie('ms', st);
			
			//console.log(st);
		}
		
		if(st)
			$('body').addClass(__ms_class);
		else
			$('body').removeClass(__ms_class);
		
		//fix_update(__fix_sel, 1);
	}
	else if(mode_in)
	{
		//__scroll = $(window).scrollTop();
		$('body').toggleClass(__ms_class);
	}
	
	fix_update(__fix_sel, 1);
	
	//console.log(sv + ' ' + st + ' ' + mode_in);
}

function startup()
{
	ext_prep();
	fix_prep();
	
	//$(window).resize(function(){ menu_mob(1); });	
	//$('.menu-mob').on('click', function(){ menu_mob(1); });
	menu_mob(0);
}

function get_elem_pnt(elem) 
{
    var box = elem.getBoundingClientRect();
    var body = document.body;
    var docElem = document.documentElement;
    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
    var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
    var clientTop = docElem.clientTop || body.clientTop || 0;
    var clientLeft = docElem.clientLeft || body.clientLeft || 0;
    var top  = box.top +  scrollTop - clientTop;
    var left = box.left + scrollLeft - clientLeft;
    
    return { top: Math.round(top), left: Math.round(left) };
}

function first_mark() 
{
	var str = location.search;
	var arr = str.match(/ht=([\d]+)/);
	if(arr)
	{
		var id = "ht_" + arr[1];
		el = document.getElementById(id);
		
		if(el)
		{
			var y = get_elem_pnt(el).top;
			var h = 100;
			
			if(y > h)
				y -= h;
				
			setTimeout(function() { window.scrollTo(0, y); }, 1)
		}
	}
}

function prep_out() 
{
	var el = $('input[name="asc"]'); //, v = el.val().split('').reverse().join('');	
	
	//alert(el.val());	
	//el.val(v);
	el.attr('name', 'acs');
	
	first_mark();
}

function uniqid()
{
	return  'id_' + Math.random().toString(16).slice(2);
}

function set_part(mode_in)
{
	$('*[' + __part_prop + ']').each(function(){
		
		var data = {}, url = window.location.href;

		if(!this.id) this.id = uniqid();
		
		data[__part_pp] = $(this).attr(__part_prop);
		data[__part_ps] = mode_in;
		data[__part_te] = this.id;
		data[__part_tp] = $(this).attr(__part_pos);

		ajax_url(url, data);
		
	});
}

function prep_part()
{
	if($('*[' + __part_prop + ']').length)
	{
		set_part(0);
		window.setInterval(function(){ set_part(1); }, 5000);
	}
}

$(document).ready(

	function() {
	
		prep_save();
		prep_ajax();
		prep_format();
		
		if(typeof(startup) == 'function')
			startup();
		
		if(typeof(startf) == 'function')
			startf();
		
		prep_out();
		prep_part();
	}
);
