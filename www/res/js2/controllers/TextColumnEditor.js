define(function(require, exports) {



	var 
		$ = require('jquery'),
		Class = require('lib/class'),
		hb = require('lib/handlebars'),

		pretty = require('lib/pretty'),

		Foodle = require('models/Foodle')
		;

	var t = require('lib/text!templates/textcolumneditor.html');
	var template = hb.compile(t);

	var ColumnEditor = Class.extend({
		"init": function(el) {
			var that = this;

			this.callbacks = {};
			
			// Number of topcolumns and subcolumns. This is the default.
			this.topcolumns = 4;
			this.subcolumns = [2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2];

			// Include second row of sub options
			this.includeOptions = false;

			this.el = el;
			this.el.empty().append(template({"_": _d}));


			// Draw table..
			this.addTable();

			this.el.on('change', '#includeOptions', function(e) {
				e.preventDefault(); e.stopPropagation();
				var val = $(e.currentTarget).prop('checked');
				that.includeOptions = val;
				that.redraw();
			});

			this.el.on('click', '#addTopColumn', function() {
				// console.log("Add top column");
				// that.topcolumns++;

				// if (that.subcolumns.length < that.topcolumns) {
				// 	that.subcolumns.push(2);
				// }

				that.redraw(null, that.modifyNumberOfColumns(1) );
			});
			this.el.on('click', '#removeTopColumn', function() {
				// console.log("Remove top column");
				// that.topcolumns--;
				that.redraw(null, that.modifyNumberOfColumns(-1) );
			});

			this.el.on('click', '.addSubOpt', function(e) {
				e.preventDefault(); e.stopPropagation();
				var i = $(e.currentTarget).closest('td').data('col-l1');
				// console.log("Click add ", e, i, that.subcolumns);

				// that.subcolumns[i]++;
				that.redraw(null, that.modifyNumberOfColumns(null, i, 1) );
			});
			this.el.on('click', '.removeSubOpt', function(e) {
				e.preventDefault(); e.stopPropagation();
				var i = $(e.currentTarget).closest('td').data('col-l1');
				// console.log("Click remove ", e, i, that.subcolumns);;
				// that.subcolumns[i]--;
				that.redraw(null, that.modifyNumberOfColumns(null, i, -1) );
			});



			// this.setColDef([
			// 	{"title": "Title 1 <b>sdlfkj</b>'\"sldkfjlsdkjf"},
			// 	{"title": "Title 2",
			// 		"children": [
			// 			{"title": "Sub option 1"},
			// 			{"title": "Sub 2"}
			// 		]},
			// 	{"title": "Title 3"}
			// ]);

		},

		"modifyNumberOfColumns": function(adjtop, adjcolno, adjcol) {
			var obj = {
				"topcolumns": this.topcolumns,
				"subcolumns": this.subcolumns.slice(0)
			};
			if (adjtop !== 0) {
				obj.topcolumns += adjtop;
			}
			console.log("CHEKING ", obj.topcolumns, obj.subcolumns.length);
			if (obj.topcolumns > obj.subcolumns.length ) {
				obj.subcolumns.push(2);
			}
			if ((typeof adjcolno === 'number') && (typeof adjcol === 'number')) {
				obj.subcolumns[adjcolno] += adjcol;
			}
			return obj;
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

		"validate": function() {
			var x = this.getColDef();
			this.el.find('.colerrors').empty();

			var hasError = false;
			if (x.length === 0) {

				this.el.find('.colerrors').append('<div class="alert alert-danger"><strong>At least a single column header</strong> is required. Please provide one before saving.</div>');
				hasError = true;
			} else {
				$('.coldef-header').removeClass('has-error')
			}

			return !hasError;
		},





		"getColDef": function() {


			var coldef = [];

			var defTable = $('#columnEditorTable');

			for(var i = 0; i < this.topcolumns; i++) {

				var item = {};
				var title = defTable.find('.coldef-header').eq(i).val();
				item.title = title;
				// console.log('Title is ', title);

				if (this.includeOptions) {

					item.children = [];

					for(var j = 0; j < this.subcolumns[i]; j++) {
						var si = {};
						var st = defTable.find('.coldef-option').eq(this.getColNo(i, j)).val();
						si.title = st;
						// si.debug = [i, j, this.getColNo(i, j)];

						if (si.title !== '') {
							item.children.push(si);							
						}

					}

					if (item.children.length === 0) {
						delete item.children;
					}

				}

				if (title !== '' || item.hasOwnProperty('children')) {
					coldef.push(item);	
				}
				

			}
			return coldef;



		},

		"hasTwoLevels": function(coldef) {

			for(var i = 0; i < coldef.length; i++) {
				// console.log("Checking", coldef[i], )
				if (coldef[i].hasOwnProperty('children')) return true;
			}
			return false;

		},

		"setColDef": function(coldef) {
			this.topcolumns = coldef.length;
			this.subcolumns = [];
			for(var i = 0; i < coldef.length; i++) {

				if (coldef[i].hasOwnProperty('children')) {
					this.subcolumns[i] = coldef[i].children.length;
				} else {
					this.subcolumns[i] = 0;
				}

			}

			// this.redraw(coldef);

		},


		/*
		 * For top column number 'top' and number 'sub' of sub options, then find the number
		 * of suboption counting from the first. This will skip empty sub boxes when added.!
		 */
		"getColNo": function(top, sub) {
			var count = 0;
			if (top > 0) {
				for (var i = 0; i < top; i++) {
					count += this.subcolumns[i];
				}				
			}
			count += sub;
			return count;
		},


		"redraw": function(setColdef, modifyNumberOfColumns) {


			var coldef = setColdef;
			if (setColdef) {
				this.setColDef(setColdef);
				this.includeOptions = this.hasTwoLevels(setColdef);
				// console.log("Perform a check for two levels", setColdef, this.includeOptions);
			}
			if (!setColdef) {
				coldef = this.getColDef();
				console.log("Obtinaing coldef", coldef);
			}

			if (modifyNumberOfColumns) {
				console.log("About to adjust colnumbers...");
				console.log(this.topcolumns, this.subcolumns);
				console.log(modifyNumberOfColumns.topcolumns, modifyNumberOfColumns.subcolumns);
				this.topcolumns = modifyNumberOfColumns.topcolumns;
				this.subcolumns = modifyNumberOfColumns.subcolumns;
			}


			this.el.empty().append(template({"_": _d}));


			this.el.find('#includeOptions').prop('checked', this.includeOptions);

			this.addTable();


			if (this.topcolumns < 2) {
				$("#removeTopColumn").attr('disabled', 'disabled');
				$("#addTopColumn").removeAttr('disabled');
			} else if (this.topcolumns > 11) {
				$("#removeTopColumn").removeAttr('disabled');
				$("#addTopColumn").attr('disabled', 'disabled');
			}


			var colNo;
			var defTable = $('#columnEditorTable');
			// console.log("Completed redraw, now filling.");
			for(var i = 0; i < coldef.length; i++) {

				defTable.find('.coldef-header').eq(i).attr('value', coldef[i].title);
				// console.log("Fill header ", defTable.find('.coldef-header').eq(i), coldef[i].title);

				if (coldef[i].hasOwnProperty('children')) {

					for(var j = 0; j < coldef[i].children.length; j++) {

						colNo = this.getColNo(i, j);
						console.log("Col no (" + i + "," + j + ")", colNo);

						defTable.find('.coldef-option').eq(colNo).attr('value', coldef[i].children[j].title);

					}

				} 

			}	
			console.log("Summary", this.topcolumns, this.subcolumns)
			console.log("-----");

		},


		"addTable": function() {
			var containerTable = $('<table id="columnEditorTable" class="row"></table>').appendTo(this.el.find('#columneditorMain'));

			var headerRow = this.getHeaderRow();
			containerTable.append(headerRow);

			if (this.includeOptions) {

				var optionsRow = this.getSuboptionsRow();
				containerTable.append(optionsRow);

				var subc = this.getSuboptionsControllers();
				containerTable.append(subc);

				$("#includeOptions").prop('checked', true);

			} else {

				$("#includeOptions").prop('checked', false);

			}

		},


		"getHeaderRow": function() {
			var row = $('<tr></tr>');

			var t;
			for(var i = 0; i < this.topcolumns; i++) {
				var rowspan = 1;
				if (this.subcolumns[i] === 0) {
					rowspan = 2;
				}
				t = '<td rowspan="' + rowspan + '" colspan="' + this.subcolumns[i] + '"><input style="width: 100%" class="coldef-header form-control" type="text" placeholder="' + _d.header+ '" /></td>';
				row.append(t);	
			}
			return row;
		},

		"getSuboptionsRow": function() {
			var row = $('<tr></tr>');

			var t;
			for(var i = 0; i < this.topcolumns; i++) {
				for(var j = 0; j < this.subcolumns[i]; j++) {
					t = '<td><input style="width: 100%" class="coldef-option form-control" type="text" placeholder="' + _d.opt + '" /></td>';
					row.append(t);
				}
			}
			return row;
		},

		"getSuboptionsControllers": function() {
			var row = $('<tr></tr>');

			var t;
			for(var i = 0; i < this.topcolumns; i++) {

				t = '<td style="text-align: left" colspan="' + this.subcolumns[i] + '" data-col-l1="' + i + '">' + 
						'<button class="removeSubOpt" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-minus"></span></button>' + 
						'<button class="addSubOpt" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-plus"></span></button>' +
					'</td>';
				var td = $(t);
				row.append(td);

				if (this.subcolumns[i] < 1 ) {
					td.find('.removeSubOpt').attr('disabled', 'disabled');
					// td.find('.removeSubOpt').removeAttr('disabled');
				} else if (this.subcolumns[i] > 4 ) {
					// td.find('.addSubOpt').removeAttr('disabled');
					td.find('.addSubOpt').attr('disabled', 'disabled');
				}
			}
			
			return row;
		}


	})

	return ColumnEditor;

});