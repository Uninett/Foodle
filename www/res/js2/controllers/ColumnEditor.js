define(function(require, exports) {



	var
		$ = require('jquery'),
		Class = require('lib/class'),
		hb = require('lib/handlebars'),
		pretty = require('lib/pretty'),

		TextColumnEditor = require('./TextColumnEditor'),
		DateColumnEditor = require('./DateColumnEditor'),

		Foodle = require('models/Foodle')
		;

	var t = require('lib/text!templates/columneditor.html');
	var template = hb.compile(t);

	var ColumnEditor = Class.extend({
		"init": function(el, user) {
			var that = this;

			this.callbacks = {};
			this.user = user;
			this.el = el;
			this.el.empty().append(template());

			var c = this.el.find('#columnEditorContainer');
			

			this.el.on('click', '#columntypestext', function(e) {
				e.stopPropagation();
				console.error('click on #columntypestext indicates TEXT');
				that.setCurrent('text');
			});

			this.el.on('click', '#columntypesdates', function(e) {
				e.stopPropagation();
				console.error('click on #columntypesdates indicates DATES');
				that.setCurrent('dates');
			});

			var tel = $('<div></div>').appendTo(c);
			var del = $('<div></div>').appendTo(c);

			this.currentEditor = null;
			this.editors = {
				'text': {
					'el': tel,
					'ctrl': new TextColumnEditor(tel)
				},
				'dates': {
					'el': del,
					'ctrl': new DateColumnEditor(del, this.user)
				}
			};




			this.setCurrent('dates');

		},

		"validate": function() {
			return this.current().validate();
		},

		"setCurrent": function(s) {
			if (!this.editors.hasOwnProperty(s)) throw "Invalid columneditor. ";

			console.log(" ›››› attempting to set columntype editro to ", s);
			$('input[name="columntypes"][value="' + s + '"]').prop('checked', true);

			var past = this.currentEditor ;

			this.el.find('#columnEditorContainer').children().hide();
			this.currentEditor = s;
			this.editors[this.currentEditor].el.show();

			if (s !== past) {
				this.trigger('changeType', s);
			}

		},

		"getColumntype": function() {
			return this.currentEditor;
		},

		"current": function() {
			return this.editors[this.currentEditor].ctrl;
		},

		"on": function(evnt, callback) {
			this.callbacks[evnt] = callback;
		},
		"trigger": function(evnt) {
			var args = Array.prototype.slice.call(arguments, 1);
			if (this.callbacks && this.callbacks[evnt] && typeof this.callbacks[evnt] === 'function') {
				this.callbacks[evnt].apply(this, args);
			}
		},


		"getColNo": function(top, sub) {
			return this.current().getColNo(top, sub);
		},

		"setColDef": function(coldef) {
			return this.current().setColDef(coldef);
		},


		"getColDef": function() {
			return this.current().getColDef();
		},

		"hasTwoLevels": function(coldef) {
			return this.current().hasTwoLevels(coldef);
		},

		"redraw": function(setColdef, coltype) {
			this.setCurrent(coltype);
			return this.current().redraw(setColdef);
		},

		"addTable": function() {
			return this.current().addTable();
		},


		"getHeaderRow": function() {
			return this.current().addTable();
		},

		"getSuboptionsRow": function() {
			return this.current().getSuboptionsRow();
		},
		"getSuboptionsControllers": function() {
			return this.current().getSuboptionsControllers();
		}


	})

	return ColumnEditor;

});
