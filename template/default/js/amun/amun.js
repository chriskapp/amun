/*
 *  $Id: amun.js 879 2012-10-03 17:46:43Z k42b3.x@googlemail.com $
 *
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of amun. amun is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * amun is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with amun. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * amun
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   js
 * @version    $Revision: 879 $
 */
(function(win){

	var amun = win.amun || {};

	amun.services = {};
	amun.store = {};
	amun.store.editors = {};
	amun.store.files = {};

	/**
	 * Class to make http requests. You can specify in the contructor a id of
	 * an <form />. If the form is submitted all data will be encoded as JSON
	 * and sended to the url in the action attribute with the given method.
	 *
	 * <code>
	 * var client = new amun.client('form');
	 *
	 * client.onSuccess(function(){
	 *     alert('Success!!!');
	 * });
	 * </code>
	 *
	 * You can also use this class to send direct http requests i.e.
	 *
	 * <code>
	 * var client = new amun.client();
	 *
	 * client.onSuccess(function(){
	 *     alert('Success!!!');
	 * });
	 *
	 * client.request('[url]', 'DELETE', {id: id});
	 * </code>
	 */
	amun.client = function(cId){

		var self = this;
		var method = 'POST';
		var overrideMethod = 'POST';
		var target;
		var contentType = 'application/x-www-form-urlencoded';
		var data = {};
		var editors = {};
		var processData = true;
		var containerId = cId;

		var successCallback;
		var errorCallback;
		var beforeSubmitCallback;
		var afterSubmitCallback;


		this.getContainerId = function(){
			return containerId;
		}

		this.getData = function(){
			return data;
		}

		this.setData = function(newData){
			data = newData;
		}

		this.addEditor = function(ref, editor){
			editors[ref] = editor;
		}

		this.getEditors = function(){
			return editors;
		}

		this.getContentType = function(){
			return contentType;
		}

		this.setContentType = function(newContentType){
			contentType = newContentType
		}

		this.setProcessData = function(newProcessData){
			processData = newProcessData
		}

		this.onSuccess = function(callback){
			successCallback = callback;
		}

		this.onError = function(callback){
			errorCallback = callback;
		}

		this.beforeSubmit = function(callback){
			beforeSubmitCallback = callback;
		}

		this.afterSubmit = function(callback){
			afterSubmitCallback = callback;
		}

		this.request = function(url, requestMethod, data){
			// set data
			this.setData(data);

			// call before submit
			if (beforeSubmitCallback) {
				beforeSubmitCallback.call(self);
			}

			// get the request method
			switch (requestMethod) {
				case 'GET':
					method = 'GET';
					overrideMethod = 'GET';
					break;

				case 'POST':
					method = 'POST';
					overrideMethod = 'POST';
					break;

				case 'PUT':
					method = 'POST';
					overrideMethod = 'PUT';
					break;

				case 'DELETE':
					method = 'POST';
					overrideMethod = 'DELETE';
					break;
			}

			// construct the ajax request
			$.ajax({
				type: method,
				url: url,
				contentType: contentType,
				data: data,
				processData: processData,
				dataType: 'json',
				beforeSend: function(xhr){
					xhr.setRequestHeader('X-Http-Method-Override', overrideMethod);
					xhr.setRequestHeader('Accept', 'application/json');
				},
				error: function(xhr, status, e){
					var message = JSON.parse(xhr.responseText);
					var text;

					if (typeof(message.text) != 'undefined') {
						text = message.text;
					} else if(status != null) {
						text = status;
					} else {
						text = 'An unknown error occured';
					}

					if (errorCallback) {
						errorCallback.call(self, text);
					}

					if (afterSubmitCallback) {
						afterSubmitCallback.call(self);
					}
				},
				success: function(data, status, xhr){
					if (data.success) {
						if (successCallback) {
							successCallback.call(self, data.text);
						}
					} else {
						if (errorCallback) {
							errorCallback.call(self, data.text);
						}
					}

					if (afterSubmitCallback) {
						afterSubmitCallback.call(self);
					}
				}
			});

		}

		// if cId is set assign the submit handler
		if (cId) {
			// remove before added listener
			$('#' + cId).unbind('submit');

			// add submit listener
			$('#' + cId).submit(function(){
				// get request method
				var method = $(this).attr('method').toUpperCase();

				// get target
				var url = $(this).attr('action');

				// get enctype
				var enctype = $(this).attr('enctype');

				// get all form fields
				var arr = $(this).serializeArray();
				var fields = {};

				for (var i = 0; i < arr.length; i++) {
					fields[arr[i].name] = arr[i].value;
				}

				// if we have an ace editor
				for (var k in editors) {
					var value = editors[k].getSession().getValue();
					var name = $('#' + k).data('name');
					fields[name] = value;
				}

				// handle data according to the enctype
				var data;

				switch (enctype) {
					case 'multipart/form-data':
						// data
						data = new FormData();

						for (var key in fields) {
							data.append(key, fields[key]);
						}

						// add file uploads
						for (var key in amun.store.files) {
							data.append(key, amun.store.files[key]);
						}

						// settings
						self.setContentType(false);
						self.setProcessData(false);
						break;

					case 'application/json':
						// data
						data = JSON.stringify(fields);

						// settings
						self.setContentType('application/json');
						break;

					default:
					case 'application/x-www-form-urlencoded':
						// data
						data = fields;

						// settings
						self.setContentType('application/x-www-form-urlencoded');
						break;
				}

				self.request(url, method, data);

				return false;

			});
		}

	}

	/**
	 * This class generates an html form based on JSON data received from the
	 * given url. The form will be inserted into the element with the id "cId".
	 * Here an example howot load a form:
	 *
	 * <code>
	 * var form = new amun.form('form', '[url]');
	 *
	 * form.onLoad(function(){
	 *     alert('Form loaded');
	 * });
	 * </code>
	 */
	amun.form = function(cId, url){

		var self = this;
		var containerId;
		var formUrl;
		var lastFile;

		var action;
		var method;
		var id;
		var ns = '';

		var loadCallback;
		var errorCallback;

		var buttons = [];
		var showButtons = true;

		this.getContainerId = function(){
			return containerId;
		}

		this.onLoad = function(callback){
			loadCallback = callback;
		}

		this.onError = function(callback){
			errorCallback = callback;
		}

		this.addButton = function(name, cssClass, callback){
			buttons.push({
				name: name,
				cssClass: cssClass,
				callback: callback
			});
		}

		this.displayButtons = function(value){
			showButtons = Boolean(value);
		}

		this.setNamespace = function(namespace){
			ns = namespace + '-';
		}

		this.transform = function(form){
			$('#' + this.getContainerId()).html(self.parseElements(form));

			// add file change listener if enctype multipart/form-data
			if (form.enctype == 'multipart/form-data') {
				$('#' + this.getContainerId()).find('input[type="file"]').each(function(){
					$(this).change(self.handleFileUpload);
				});
			}

			if (loadCallback) {
				loadCallback.call(self, ns + form.ref);
			}

		}

		this.parseElements = function(item){

			if (typeof(item['success']) != 'undefined' && item['success'] == false) {
				var p = document.createElement('p');
				p.setAttribute('class', 'alert alert-notice');
				p.appendChild(document.createTextNode(item.text));

				return p;
			}

			switch (item['class']) {
				case 'form':
					var form = document.createElement('form');
					form.setAttribute('id', ns + item.ref);
					form.setAttribute('method', item.method);
					form.setAttribute('action', item.action);
					form.setAttribute('enctype', item.enctype);

					for (var i = 0; i < item.item.children.item.length; i++) {
						form.appendChild(this.parseElements(item.item.children.item[i]));
					}

					// add buttons
					if (showButtons) {
						var div = document.createElement('div');
						div.setAttribute('class', 'form-actions');

						var input = document.createElement('input');
						input.setAttribute('class', 'btn btn-primary');
						input.setAttribute('type', 'submit');
						input.setAttribute('value', 'Submit');

						div.appendChild(input);

						if (buttons.length > 0) {
							for (var i = 0; i < buttons.length; i++) {
								var input = document.createElement('input');
								input.setAttribute('class', buttons[i].cssClass);
								input.setAttribute('type', 'button');
								input.setAttribute('value', buttons[i].name);
								input.addEventListener('click', buttons[i].callback, false);

								div.appendChild(input);
							}
						}

						form.appendChild(div);
					}

					return form;
					break;

				case 'panel':
					var fieldset = document.createElement('fieldset');
					var legend = document.createElement('legend');
					legend.appendChild(document.createTextNode(item.label));

					fieldset.appendChild(legend);

					for (var i = 0; i < item.children.item.length; i++) {
						fieldset.appendChild(this.parseElements(item.children.item[i]));
					}

					return fieldset;
					break;

				case 'captcha':
					var p = document.createElement('p');

					var label = document.createElement('label');
					label.setAttribute('for', item.ref);
					label.appendChild(document.createTextNode(item.label));

					var img = document.createElement('img');
					img.setAttribute('src', item.src);
					img.setAttribute('style', 'margin-bottom:4px');
					img.setAttribute('alt', 'Captcha');

					var input = document.createElement('input');
					input.setAttribute('type', 'text');
					input.setAttribute('name', item.ref);
					input.setAttribute('id', ns + item.ref);
					input.setAttribute('value', item.value || '');

					if (item.disabled) {
						input.setAttribute('disabled', 'disabled');
					}

					p.appendChild(label);
					p.appendChild(img);
					p.appendChild(document.createElement('br'));
					p.appendChild(input);

					return p;
					break;

				case 'datalist':

					break;

				case 'reference':
					var p = document.createElement('p');

					var label = document.createElement('label');
					label.setAttribute('for', item.ref);
					label.appendChild(document.createTextNode(item.label));

					var input = document.createElement('input');
					input.setAttribute('type', 'text');
					input.setAttribute('name', item.ref);
					input.setAttribute('id', ns + item.ref);
					input.setAttribute('value', item.value || '');

					if (item.disabled) {
						input.setAttribute('disabled', 'disabled');
					}

					p.appendChild(label);
					p.appendChild(input);

					return p;
					break;

				case 'input':
					var input = document.createElement('input');
					input.setAttribute('type', item.type);
					input.setAttribute('name', item.ref);
					input.setAttribute('id', ns + item.ref);
					input.setAttribute('value', item.value || '');

					if (item.disabled) {
						input.setAttribute('disabled', 'disabled');
					}

					switch (item.type) {
						case 'hidden':
							return input;
							break;

						default:
							var p = document.createElement('p');

							var label = document.createElement('label');
							label.setAttribute('for', item.ref);
							label.appendChild(document.createTextNode(item.label));

							p.appendChild(label);
							p.appendChild(input);

							return p;
							break;
					}
					break;

				case 'select':
					var p = document.createElement('p');

					var label = document.createElement('label');
					label.setAttribute('for', item.ref);
					label.appendChild(document.createTextNode(item.label));

					var select = document.createElement('select');
					select.setAttribute('name', item.ref);
					select.setAttribute('id', ns + item.ref);

					if (item.disabled) {
						select.setAttribute('disabled', 'disabled');
					}

					if (typeof item.children.item != 'undefined') {
						for (var j = 0; j < item.children.item.length; j++) {
							var opt = item.children.item[j];
							var option = document.createElement('option');
							option.setAttribute('value', opt.value);

							if (item.value == opt.value) {
								option.setAttribute('selected', 'selected');
							}

							option.appendChild(document.createTextNode(opt.label));
							select.appendChild(option);
						}
					}

					p.appendChild(label);
					p.appendChild(select);

					return p;
					break;

				case 'textarea':
					var p = document.createElement('p');

					var label = document.createElement('label');
					label.setAttribute('for', item.ref);
					label.appendChild(document.createTextNode(item.label));

					var textarea = document.createElement('textarea');
					textarea.setAttribute('name', item.ref);
					textarea.setAttribute('id', ns + item.ref);

					if (item.disabled) {
						textarea.setAttribute('disabled', 'disabled');
					}

					textarea.appendChild(document.createTextNode(item.value || ''));

					p.appendChild(label);
					p.appendChild(textarea);

					return p;
					break;
			}
		}

		this.handleFileUpload = function(event){
			var files = event.target.files;
			for (var i = 0; i < files.length; i++) {
				var name = $(this).attr('name');
				var file = files[i];

				amun.store.files[name] = file;
			}
		}

		this.load = function(){
			$.ajax({
				type: 'GET',
				url: formUrl,
				dataType: 'json',
				beforeSend: function(xhr){
					xhr.setRequestHeader('Accept', 'application/json');
				},
				error: function(xhr, status, e){
					if (errorCallback) {
						errorCallback.call(self, e);
					}
				},
				success: function(data, status, xhr){
					if (typeof data.success != 'undefined' && !data.success) {
						if (errorCallback) {
							errorCallback.call(self, data.text);
						}
					} else {
						self.transform(data);
					}
				}
			});
		}

		containerId = cId;
		formUrl = url;
	}

	/**
	 * Shows an form in an modal dialog
	 */
	amun.window = function(formUrl){

		var self = this;
		var url = formUrl;
		var buttons = [];
		var form;
		var client;

		var beforeShowCallback;
		var successCallback;
		var errorCallback;

		this.addButton = function(name, cssClass, callback){
			buttons.push({
				name: name,
				cssClass: cssClass,
				callback: callback
			});
		}

		this.beforeShow = function(callback){
			beforeShowCallback = callback;
		}

		this.onSuccess = function(callback){
			successCallback = callback;
		}

		this.onError = function(callback){
			errorCallback = callback;
		}

		this.getForm = function(){
			return form;
		}

		this.getClient = function(){
			return client;
		}

		this.show = function(){
			// add close button
			this.addButton('Close', 'btn', function(){
				$('#amun-form-window').modal('hide');
			});

			// add buttons
			$('#amun-form-window-buttons').html('');
			var btns = buttons;
			btns.reverse();
			for (var i = 0; i < btns.length; i++) {
				var btn = document.createElement('button');
				btn.setAttribute('class', btns[i].cssClass);
				btn.appendChild(document.createTextNode(btns[i].name));
				btn.addEventListener('click', btns[i].callback.bind(this), false);

				$('#amun-form-window-buttons').append(btn);
			}

			// on show event
			$('#amun-form-window').on('show', function(){
				form = new amun.form('amun-form-window-form', url);

				form.onError(function(msg){
					$('#amun-form-window-response').html('<div class="alert alert-error">' + msg + '</div>');
				});

				form.onLoad(function(cId){
					client = new amun.client(cId);

					client.beforeSubmit(function(){
						$('#' + this.getContainerId() + ' input[type=submit]').attr('disabled', 'disabled');
					});

					client.afterSubmit(function(){
						$('#' + this.getContainerId() + ' input[type=submit]').removeAttr('disabled');
					});

					client.onSuccess(function(msg){
						$('#amun-form-window-response').html('<div class="alert alert-success">' + msg + '</div>');

						if (successCallback) {
							successCallback.call(self);
						}
					});

					client.onError(function(msg){
						$('#amun-form-window-response').html('<div class="alert alert-error">' + msg + '</div>');

						if (errorCallback) {
							errorCallback.call(self);
						}
					});

					// transform textarea
					$('#' + cId + ' textarea').each(function(){
						var ref = $(this).attr('id');
						var name = $(this).attr('name');

						$(this).replaceWith('<div style="height:320px;"><div id="' + ref + '" title="' + ref + '" data-name="' + name + '" style="position:relative;width:740px;height:320px;border:1px solid #666;">' + $(this).html() + '</div></div>');

						var editor = ace.edit(ref);
						editor.setTheme("ace/theme/eclipse");

						var mode = require("ace/mode/html").Mode;
						editor.getSession().setMode(new mode());

						client.addEditor(ref, editor);
					});
				});

				form.setNamespace('afw');
				form.displayButtons(false);
				form.load();
			});

			if (beforeShowCallback) {
				beforeShowCallback.call(self);
			}

			// show window
			$('#amun-form-window').modal('show');
		}

		// add submit buttons
		this.addButton('Save', 'btn btn-primary', function(){
			$('#amun-form-window').find('form').submit();
		});
	}

	/**
	 * Handle gadgets
	 */
	amun.gadget = {

		load: function(name, cId){

			$('#' + cId).html('<p style="text-align:center;padding:12px;"><img src="' + amun.config.basePath + '/img/loader.gif" /></p>');

			$.ajax({
				type: 'GET',
				url: amun.config.url + 'gadget/' + name,
				statusCode: {
					401: function(){
						$('#' + cId).parent().parent().fadeOut();
					},
					500: function(){
						$('#' + cId).parent().parent().fadeOut();
					}
				},
				success: function(resp){
					$('#' + cId).html(resp);
				},
				error: function(){
					$('#' + cId).parent().parent().fadeOut();
				}
			});

		}

	}

	/**
	 * Util class wich provides common methods
	 */
	amun.util = {

		/**
		 * Returns the current url without the last / part. If no slash exists
		 * in the url the current location is returned
		 *
		 * @return string
		 */
		getParentLocation: function(){

			var url = "" + location;
			var pos = url.lastIndexOf('/');

			return pos != -1 ? url.substr(0, pos) : url;

		},

		/**
		 * Converts an sql string to an javascript date object
		 *
		 * @param string sqlTime
		 * @return date
		 */
		getSqlToDate: function(sqlTime){

			var parts = sqlTime.split(' ');

			var date = parts[0].split('-');
			var time = parts[1].split(':');

			var d = new Date();
			d.setUTCDate(date[0]);
			d.setUTCMonth(date[1]);
			d.setUTCFullYear(date[2]);
			d.setUTCHours(time[0]);
			d.setUTCMinutes(time[1]);
			d.setUTCSeconds(time[2]);

			return d;

		}

	};


	win.amun = amun;

})(window);
