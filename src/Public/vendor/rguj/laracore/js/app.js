
/*function log(_var, showcaller = false) {
	if(showcaller==true)
		console.trace(_var);
	else
		console.log(_var);
}*/

'use strict';

const log = console.log.bind(console);
const dd = console.log.bind(console);
var has_ignited = false;


const app = {
	
	
	
	
	
	swal2: {
		//obj: swal2,
		obj: config.obj.swal2,
		//icons: globals.page.alerts.categories.swal2,
		icons: config.category.swal2,
		queue: async function(msgs, mixin=null, callback=null) {
			let inIframe = app.validator.in_iframe();
			let SA_icons = app.swal2.icons;
			if(mixin === null) {
				mixin = {
					showConfirmButton: true,
					timerProgressBar: true,
					allowOutsideClick: false,
				};
			}

			let queue = [];          
			$.each(msgs, function(k, v) {          
				let icon = (PHP.in_array(v['type'], SA_icons) ? v['type'] : 'question');
				queue.push({ icon: icon, title: PHP.ucfirst(v['type']), html: v['msg'] });
			});

			if(inIframe === true) {
				window.parent.postMessage({
					is_saqueue: true,
					mixin: mixin,
					msgs: msgs,
					callback: callback,
				}, "*");
			} else {
				app.swal2.obj.mixin(mixin).queue(queue).then((result) => {
					if(typeof callback === 'function') {
						callback(result);
					}
				});
			}
		},
		quick: function(icon, title, content, clickable_outside=true) {
			let i = (PHP.in_array(icon, app.swal2.icons) ? icon : 'question');
			app.swal2.obj.mixin(config.swal2('simple', {icon: i, title: title, html:content, allowOutsideClick: clickable_outside})).fire();
		}
	},
	
	
	
	
	
	validator: {
		is_dev_mode: function() {
			return globals.config.is_dev_mode;
		},
		is_server_alert: function(data) {
			// checks the feedback data structure integrity
			return (
				!PHP.empty(data) && PHP.count(data) >= 2
				&& PHP.array_key_exists('type', data) && PHP.array_key_exists('msg', data)
				&& PHP.is_string(data.type) && !PHP.empty(data.type) && PHP.in_array(data.type, globals.page.alerts.categories.swal2)
				&& PHP.is_string(data.msg) && !PHP.empty(data.msg)
			);
		},
		is_dom_element: function(o) {
			return (o && o.nodeType && o.nodeType === 1);
		},
		is_cookies_enabled : function() {		
			if (navigator.cookieEnabled) return true;
			// set and read cookie
			document.cookie = "cookietest=1";
			var ret = document.cookie.indexOf("cookietest=") != -1;
			// delete cookie
			document.cookie = "cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT";
			return ret;
		},
		in_iframe: function() {
			try { return window.self !== window.top; }
			catch (e) { return true; }
		},
		is_blank_str : function(str1) {
			return PHP.empty(PHP.trim(str1));
		},
	},
	
	
	
	
	
	document: {
		sequence_alerts: function(alerts) {
			var categories = globals.page.alerts.categories;			
			// SWAL
			var queue = [];
			$.each((alerts.swal2 || []), function(k, v) {
				if(app.validator.is_server_alert(v)) {
					let icon = (PHP.in_array(v['type'], categories.swal2) ? v['type'] : 'question');
					queue.push({
						icon: icon, 
						title: PHP.ucfirst(v['type']), 
						html: v['msg'], 
						showConfirmButton: true, 
						timerProgressBar: true, 
						allowOutsideClick: false,
					});
				}
			});			
			// fire with order
			swal2.mixin(
				config.swal2('dialog', {
					showConfirmButton: true,
					timerProgressBar: true,
					allowOutsideClick: false,
				})
			).queue(queue).then(function(){
				// TOASTR
				toastr.options = config.toastr('simple', {});
				$.each((alerts.toastr || []), function(k, v) {
					if(app.validator.is_server_alert(v)) {
						let icon = (PHP.in_array(v['type'], categories.toastr) ? v['type'] : 'info');
						if(icon === 'success') toastr.success(v['msg']);
						else if(icon === 'info') toastr.info(v['msg']);
						else if(icon === 'warning') toastr.warning(v['msg']);
						else if(icon === 'error') toastr.error(v['msg']);
					}
				});
			});	
		},
		show_server_alerts: function() {
			app.document.sequence_alerts(globals.page.alerts);
		},
		animate_button_submit: function(el, isLoading) {
			if(!app.validator.is_dom_element(el))
				return false;
			if(PHP.gettype(isLoading) != 'boolean')
				return false;		
			if(isLoading == true) {
				$(el).addClass('disabled');
				$(el).find('#BTN_icon').hide();
				$(el).find('#BTN_spinner').removeClass('d-none');
				$(el).find('#BTN_text').html('Please wait ...');
			} else {
				$(el).find('#BTN_spinner').addClass('d-none');
				$(el).find('#BTN_icon').show();
				$(el).find('#BTN_text').html($(el).find('#BTN_original_name').html());
				$(el).removeClass('disabled');
			}			
			return true;
		},		
		reset_radio_button: function(el) {
			//$(function(){
				if($(el).attr('type') !== 'radio') return false;
				$(el).attr('checked',false);
			//})
		},
		serialize : function(form_el) {
			let obj = $(form_el).serializeToJSON({
				associativeArrays: true, // serialize the form using the Associative Arrays
				parseBooleans: true, // convert "true" and "false" to booleans true / false
				parseFloat: {
					condition: undefined, // the value can be a string or function
					nanToZero: true, // auto detect NaN value and changes the value to zero
					getInputValue: function($input) { // return the input value without commas
						return $input.val().split(",").join("");
					},
				}
			});
			return obj;
		},
		serialize2 : function(form_el) {
			let d = $(form_el).serializeArray();
			let d2 = {};
			$.each(d, function(k,v){
				let name = v['name'].trim();
				let value = v['value'];
				let isArray = name.endsWith('[]');
				name = isArray ? name.slice(0, -2) : name;
				if(!PHP.array_key_exists(name, d2)) {
					d2[name] = isArray ? [value] : value;
				} else {
					if(isArray) { d2[name].push(value); }
					else { d2[name] = value; }				
				}
			});
			return d2;
		},
		live_download : function(filePath){
			var link = document.createElement('a');
			link.href = filePath;
			link.download = filePath.substr(filePath.lastIndexOf('/') + 1);
			link.click();
		},
		update_shadow_elements : function(enable, SE_ids) {
			let bool1 = (PHP.is_bool(enable) && PHP.is_array(SE_ids));
			if(!bool1)
				return false;
			$.each(SE_ids, function(k, v){  // update shadow elements UI
				$(v).prop('disabled', !enable);
			});
		},
		evaluate_fields : function(frm) {
			//let output = [false, $(frm).serializeArray()];
			let output = [false, $(frm).serializeArray()];
			let el_unfilled = [];

			$.each(output[1], function(k, v) {
				let name = v['name'];
				let value = v['value'];
				let c_el = $(this).find('[name='+name+']');//$(frm+' [name='+name+']');
				if(c_el.length > 0) {        		
					if(c_el.prop('required')===true) {
						if(PHP.is_string(value) && app.validator.is_blank_str(value)) {
							el_unfilled.push(name);
						} else if(PHP.empty(value)) {
							el_unfilled.push(name);
						}
					}
				}
			});
			
			let c_el_unfilled = PHP.count(el_unfilled);
			if(c_el_unfilled > 0) {
				app.swal2.obj.fire({
					title: "Error",
					text: 'Please fill-out the required field'+(c_el_unfilled > 1 ? 's' : ''),
					icon: "error",
					confirmButtonText: "OK",
					onAfterClose: () => {
						//$('#'+el_unfilled[0]).scrollIntoView();
						$('#'+el_unfilled[0]).focus();
					}
				}).then(function(result) {
					if(result.isConfirmed) {

					}
				});
				output[0] = false;         
			} else {
				output[0] = true;
			}

			return output;
		},
		data_url_to_blob : function(dataurl) {
			var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
				bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
			while(n--){
				u8arr[n] = bstr.charCodeAt(n);
			}
			return new Blob([u8arr], {type:mime});
		}
	},
	
	
	
	
	
	datatable: {
		row_data: function(dt_table, el) {
			let row_data = {};
			try { row_data = dt_table.row($(el).closest('tr')).data(); } catch(err) {}
			if(row_data === undefined)
				throw new ReferenceError('Element is not a datatable data');
			return row_data;
		}
	},
	
	
	
	
	
	el: {
		trigger_event: function(el, ev) {
			document.querySelector(el).dispatchEvent(new Event(ev));
		}
	},
	
	
	
	
	
	json: {
		is_valid: function(item) {
			item = (typeof item !== "string") ? JSON.stringify(item) : item;
			try { item = JSON.parse(item); } catch (e) { return false; }
			if (typeof item === "object" && item !== null) { return true; }
			return false;
		},
		parse: function(vari)
		{
			return json_valid(vari)===true
				? (typeof(vari)!=='object' ? (JSON.parse(vari)) : vari)
				: (JSON.parse(vari));
		}
	},
	
	
	
	
	ajax: async function(url, method, purpose='', options={}, callback=function(){}, debug=false) {
		var methods = { 1:'GET', 2:'POST' };
		var o_m = PHP.array_key_exists('method', options) ? options.method : method;
		o_m = methods[o_m] || o_m;
		var o_u = PHP.array_key_exists('url', options) ? options.url : url;
		
		var o = {
			url: o_u,
			method: o_m,
			data: options['data'] || {},
		};
		o.data._token = globals.request.token;
		o.data._purpose = purpose;
		o.data._fullUrl = globals.request.fullUrl;
		o.data._url = globals.request.url;
		o = PHP.array_merge(options, o);
		
		$.ajax(o).always(function(dataOrjqXHR, textStatus, jqXHRorErrorThrown){
			app.ignite(dataOrjqXHR, textStatus, jqXHRorErrorThrown);
			app.document.sequence_alerts(dataOrjqXHR.m || {});
			callback(dataOrjqXHR, textStatus, jqXHRorErrorThrown);
			
			if(debug) {
				log(o);
				log(textStatus);
				log(dataOrjqXHR);
				log(jqXHRorErrorThrown);
			}
			
			// force redirect [ noActionOrLoad, isHrefOrReplace, url ]
			if (			
				PHP.is_object(dataOrjqXHR)
				&& PHP.isset(dataOrjqXHR.r)
				&& !PHP.empty(dataOrjqXHR.r)
				&& PHP.count(dataOrjqXHR.r) === 3
				&& PHP.is_bool(dataOrjqXHR.r[0])
				&& PHP.is_bool(dataOrjqXHR.r[1])
				&& PHP.is_string(dataOrjqXHR.r[2])
				&& !PHP.empty(dataOrjqXHR.r[2])
			) {
				if(dataOrjqXHR.r[0]) {
					if(dataOrjqXHR.r[1]) {
						window.location = dataOrjqXHR.r[2];
					} else {
						history.replaceState({}, globals.page.title, dataOrjqXHR.r[2]);
						window.location.href = dataOrjqXHR.r[2];
					}
				}
			}
			
		});
	},
	
	
	
	
	
	ignite: function(data1, text1, error1) {
		//if(!has_ignited && text1 !== 'success') {
		if(text1 !== 'success') {
			//log(data1);
			//return false;
			has_ignited = true;
			/*if(data1.responseText === undefined) {
				log('fired');
				app.swal2.quick('error', 'Error'+data1.status, data1.statusText, false)
				has_ignited = false;
			} else {*/
				if(globals.config.is_dev_mode) {
					if(!PHP.empty(data1.responseJSON.message) && !PHP.empty(data1.responseJSON.exception)) {
						var o = data1;
						app.ajax(
							'/', 
							2, 
							globals.form.purposes.ignite[0],
							{ data: { json: data1.responseJSON } },
							async function(d, t, j){
								o.responseText = d.data;
								app.modal.ignite(o);
								has_ignited = false;
							},
						);
					}
					
				} else {
					app.swal2.quick('error', 'Transmission Error', "("+data1.status+") "+data1.statusText, false)
				}				
			//}
		}
	},
	
	
	
	
	
	modal: {
		show: function(modal_id, title, content, footer, header_close=true, bypassable=true) {
			// header = [ true => default header_right, false => no header_right, string => custom header_right ]

			// defaults
			let def_header_close = `<button type="button" class="btn btn-icon btn-light-primary" data-bs-dismiss="modal" aria-label="Close"><i aria-hidden="true" class="bi bi-x fs-1"></i></button>`;

			// modal contents
			let html_header_title = '<h5 class="modal-title" id="'+modal_id+'Label">'+title+'</h5>';
			let html_header_close = '';
			let type_header_close = typeof(header_close);
			if(type_header_close == 'boolean') {
				if(header_close===true)
					html_header_close = def_header_close;
				else if(header_close===true)
					html_header_close = def_header_close;
			}
			else if(type_header_close == 'string') {
				html_header_close = header_close;
			}
			let html_header = html_header_title + html_header_close;
			
			$('#'+modal_id+' .modal-header').html(html_header);
			$('#'+modal_id+' .modal-body').html(content);
			$('#'+modal_id+' .modal-footer').html(footer);

			$('#'+modal_id).attr('data-bs-backdrop', (!bypassable ? 'static' : 'true'));
			$('#'+modal_id).attr('data-bs-keyboard', (!bypassable ? 'false' : 'true'));
			$('#'+modal_id).modal('show');
		},
		ignite: function(data, footer="", header_close=true, bypassable=false) {
			/*
				ADD THIS HTML AT THE BOTTOM OF YOUR BASE LAYOUT
				
				<div class="modal fade" id="ModalIgnition" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
					<div class="modal-dialog modal-dialog-scrollable" role="document" style="height: 90%; max-width: 90%;">
						<div class="modal-content" style="height: 100%;">
							<div class="modal-header"></div>
							<div class="modal-body p-0" style="overflow-y: hidden;"></div>
							<div class="modal-footer d-none"></div>
						</div>
					</div>
				</div>
			*/
			
			if($('#ModalIgnition').length <= 0) throw new Error('div#ModalIgnition is missing');
			
			let footer_html = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>';
			let footer_ = footer!='' ? footer : footer_html;

			let title = '('+data['status']+') '+data['statusText'];
			let content = '<iframe height="100%" width="100%" style="border: 0px;"></iframe>';
			app.modal.show('ModalIgnition', title, '', footer_, header_close, bypassable);

			$('#ModalIgnition .modal-body').html(content);
			$('#ModalIgnition .modal-body iframe').contents().find('body').html(data['responseText']);
		},
		quick: function (title, content, footer="", header_close=true, bypassable=true){
			let footer_html = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>';
			let footer_ = footer!='' ? footer : footer_html;
			app.modal.show('AppModal', title, content, footer_, header_close, bypassable);
		}
	}





}, APP = app;