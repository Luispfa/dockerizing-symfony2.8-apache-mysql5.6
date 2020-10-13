'use strict';

/*--------------Open NEW/EDIT/REMOVE Modals---------------*/
function createTagModal(e){

	e.stopPropagation();
	e.preventDefault();
	buildModal([],'new');

}
function editTagModal(e){

	e.stopPropagation();
	e.preventDefault();

	var tag = $(e.currentTarget.parentElement.parentElement.children[0]).text();
	var data = [];
	var langs = getLangs();
	for(var i in langs){
		var el = null;
		el = tradTable[langs[i]].$('tr[id*="'+tag+'_'+langs[i]+'"]');
		var lang_data = {
			tag_id: tag,
			lang: langs[i],
			value:el.children().eq(1).text()
		};
		data.push(lang_data);
	}
	buildModal(data,'edit');

}
function removeTagModal(e){

	e.stopPropagation();
	e.preventDefault();

	var tag = $(e.currentTarget.parentElement.parentElement.children[0]).text();
	var data = [];
	var langs = getLangs();

	for(var i in langs){
		var el = tradTable[langs[i]].$('tr[id="'+tag+'_'+langs[i]+'"]');
		tradTable.myTagElement = el;
		data.push({
			tag_id: tag,
			lang: langs[i],
			value:el.children().eq(1).text()
		});
	}
	buildModal(data,'delete');

}

/*--------------Bind evets-----------------------------*/

$(document).on('click','.tag-new', 	  createTagModal);
$(document).on('click','.tag-edit', 	editTagModal);
$(document).on('click','.tag-remove', removeTagModal);

/*--------------Save validation N petition to server---*/

function translatesCallback( response ){
	var data = JSON.parse( response );
	//host has tag(0) + Nlangs(1,2,3,4...,N) + 2(N+1, N+2) columns
	////tradTable['undefined'].$('tr[id*="id00033"]')
	//oTable.fnGetPosition( this )
	//each lang has id(0) + tag(1) + editbutton(2) columns


	if(data['success'])
	{

		switch(data['success'])
		{
			case '1':
			/**
		 * Update a table cell or row - this method will accept either a single value to
		 * update the cell with, an array of values with one element for each column or
		 * an object in the same format as the original data source. The function is
		 * self-referencing in order to make the multi column updates easier.
		 *  @param {object|array|string} mData Data to update the cell/row with
		 *  @param {node|int} mRow TR element you want to update or the aoData index
		 *  @param {int} [iColumn] The column to update (not used of mData is an array or object)
		 *  @param {bool} [bRedraw=true] Redraw the table or not
		 *  @param {bool} [bAction=true] Perform predraw actions or not
		 *  @returns {int} 0 on success, 1 on error
		 *  @dtopt API
		 *
		 *  @example
		 *    $(document).ready(function() {
		 *      var oTable = $('#example').dataTable();
		 *      oTable.fnUpdate( 'Example update', 0, 0 ); // Single cell
		 *      oTable.fnUpdate( ['a', 'b', 'c', 'd', 'e'], 1, 0 ); // Row
		 *    } );
		 */

		 		var tagId = data.data[Object.keys(data.data)[0]].tag;

				var rowData = {0:tagId};
				var len = Object.keys(data.data).length;//each lang in response
				for(var i = 0; i < len; i++)
				{
					var lang = Object.keys(data.data)[i];
					rowData[1 + i] = data.data[ lang ].value || '';
					var element = tradTable[lang].$('tr[id*="'+tagId+'"]');

					if(element[0]){
						var row = tradTable[ lang ].fnGetPosition( element[0] );
						tradTable[ lang ].fnUpdate( {
							0:tagId,
							1:data.data[ lang ].value || '',
							2:'<button class="tag-edit btn btn-small btn-default" value="'+tagId+'_'+lang+'">Edit</button>'
						},row,true );

					}

				}
				rowData[1+len+0] = '<button class="tag-remove btn btn-small btn-danger">Remove</button>';
				rowData[1+len+1] = "<button class='tag-edit btn btn-small btn-default'>Edit</button>";

				//peta porque no lo tiene le host
				var element = tradTable['undefined'].$('tr[id*="'+tagId+'"]');
				if(element[0]){
					var row = tradTable[ 'undefined' ].fnGetPosition( element[0] );
					tradTable['undefined'].fnUpdate( rowData,row,true );
				}
				else{
					var newRow = tradTable['undefined'].fnAddData(rowData,true);
					var oSettings = tradTable['undefined'].fnSettings();
					var nTr = oSettings.aoData[ newRow[0] ].nTr;
					nTr.id = tagId;
				}

				//console.log('tag editted successfully!');

				$('#editTag').modal('toggle');
				break;

			case '2':
				//console.log('new tag created & stored successfully!');
				var tagId = data.data[Object.keys(data.data)[0]].tag;

				var rowData = {0:tagId};
				var len = Object.keys(data.data).length;//each lang in response
				for(var i = 0; i < len; i++){
					var lang = Object.keys(data.data)[i];

					rowData[1 + i] = data.data[ lang ].value || '';
					var newRow = tradTable[ lang ].fnAddData({
						0:tagId,
						1:data.data[ lang ].value || '',
						2:'<button class="tag-edit btn btn-small btn-default" value="'+tagId+'_'+lang+'">Edit</button>'
					},true);

					var oSettings = tradTable[ lang ].fnSettings();
					var nTr = oSettings.aoData[ newRow[0] ].nTr;
					nTr.id = tagId+'_'+lang;
				}

				rowData[1+len+0] = '<button class="tag-remove btn btn-small btn-danger">Remove</button>';
				rowData[1+len+1] = "<button class='tag-edit btn btn-small btn-default'>Edit</button>";

				var newRow = tradTable['undefined'].fnAddData(rowData,true);
				var oSettings = tradTable['undefined'].fnSettings();
				var nTr = oSettings.aoData[ newRow[0] ].nTr;
				nTr.id = tagId;

				$('#editTag').modal('toggle');
				break;

			case '3':

				var tagId = data.data[Object.keys(data.data)[0]].tag;
				var len = Object.keys(data.data).length;

				for(var i = 0; i < len; i++)
				{
					var lang = Object.keys(data.data)[i];
					var element = tradTable[lang].$('tr[id*="'+tagId+'"]');

					if(element[0]){
						var row = tradTable[ lang ].fnGetPosition( element[0] );
						tradTable[ lang ].fnDeleteRow( row );
					}

				}

				var element = tradTable['undefined'].$('tr[id*="'+tagId+'"]');
				var row = tradTable[ 'undefined' ].fnGetPosition( element[0] );

				var that = this;
				//console.log(tagId);
				$.post( Routing.generate('_translationsplatform_getTag',{platform:$('input[name=trad_platform]').val(), data: tagId},true),
					function(r){

						var data2 = JSON.parse( r );
						tradTable['undefined'].fnDeleteRow( row );
						var len = Object.keys(data2.data).length;//each lang in response

						for(var i = 0; i < len; i++)
						{
							var lang = Object.keys(data2.data)[i];
							if(data2.data[ lang ][0] == undefined)
								continue;


							var oldData = (data2.data[ lang ][1] !== 'undefined')? data2.data[ lang ][1]: (data2.data[ lang ][0] !== 'undefined')?data2.data[ lang ][0] : '' ;

							var newRow = tradTable[ lang ].fnAddData({
								0:data2.tag,
								1: oldData || '',
								2:'<button class="tag-edit btn btn-small btn-default" value="'+that.tagId+'_'+lang+'">Edit</button>'
							},true);
							var oSettings = tradTable[ lang ].fnSettings();
							var nTr = oSettings.aoData[ newRow[0] ].nTr;
							nTr.id = tagId+'_'+lang;
						}
						//console.log('tag removed successfully!');
						$('#editTag').modal('toggle');
					});

				break;

			default:
			//console.log('error!');
			var validator = $('#editTag').validate();
		}


	}
	else{
		//console.log('error2!');
		var validator = $('#editTag').validate();
		//validator.showErrors({ "tag": 'Culomante'});
	}
}

function newTag(e){
	var tag_data={};
	var langs = getLangs();
	for(var i in langs)
	{
		tag_data[langs[i]] = {
			tag:  $('#editTag #tag').val(),
			lang: langs[i],
			value:$('#editTag #form-tag-'+langs[i])[0].value
		};
	}
	$.post( Routing.generate('_translationsplatform_new',{platform:$('input[name=trad_platform]').val(),data:tag_data},true), translatesCallback);
}

function saveTag(e){
	var tag_data={};
	var langs = getLangs();
	for(var i in langs)
	{
		tag_data[langs[i]] = {
			tag:  $('#editTag #tag').val(),
			lang: langs[i],
			value:$('#editTag #form-tag-'+langs[i])[0].value
		};
	}

	$.post( Routing.generate('_translationsplatform_save',{platform:$('input[name=trad_platform]').val(),data:tag_data},true), translatesCallback);
}

function removeTag(e){
	var tag_data={};
	var langs = getLangs();
	for(var i in langs)
	{
		tag_data[langs[i]] = {
			tag:  $('#editTag #tag').val(),
			lang: langs[i],
			value:$('#editTag #form-tag-'+langs[i])[0].value
		};
	}

	$.post( Routing.generate('_translationsplatform_remove',{platform:$('input[name=trad_platform]').val(),data:tag_data},true), translatesCallback);
}



/*---------------Private functions---------------------*/

function buildModal(data, type)
{
	data = data || [];

	var langs = getLangs();
	var tabs = '';
	var content = '';

	$('#editTag #modal-head').html('');
	$('#editTag .modal-body .tab-content').html('');
	for(var i in langs){
		$('#editTag #modal-head').append('<li id="head'+i+'"><a href="#'+langs[i]+'">'+langs[i]+'</a></li>');

		$('#editTag .modal-body .tab-content').append('<div class="tab-pane" id="'+langs[i]+'"><p id="tag-text-'+langs[i]+'"><textarea id="form-tag-'+langs[i]+'" name="form-tag-'+langs[i]+'" class="textarea-large"></textarea></p></div>');

		if(i == 0){
			$('#editTag #modal-head #head0').addClass("active");
			$('#editTag .tab-content #'+langs[0]).addClass("active");
		}
	}

	switch( type ){

		case 'edit':
			$('#translations-myModalLabel').html('Edit Tag');

			$('#editTag .modal-body #tag').val( data[0].tag_id );
			$('#editTag .modal-body #tag').attr("disabled",true);

			for(var i in langs){
				$('#editTag #form-tag-'+langs[i]).val(data[i].value);
			}

			$('#editTag .modal-footer').html('<button class="btn btn-primary save-btn submit" type="submit" value="Submit"> Save changes </button>');

			$('#editTag').validate({
				rules:{},
				messages: {},
				submitHandler: saveTag

			}).settings.submitHandler = saveTag;


			break;

		case 'delete':
			$('#translations-myModalLabel').html('Delete Tag');

			$('#editTag .modal-body #tag').val( data[0].tag_id );
			$('#editTag .modal-body #tag').attr("disabled",true);

			for(var i in langs){
				$('#editTag #form-tag-'+langs[i]).val(data[i].value);
				$('#editTag #form-tag-'+langs[i]).attr('placeholder', '');
				$('#editTag #form-tag-'+langs[i]).attr("disabled",true);
			}

			$('#editTag .modal-footer').html('<button class="btn btn-danger delete-btn submit" type="submit" value="Submit"> Delete Permanently </button>');

			$('#editTag').validate({
				rules:{},
				messages: {},
				submitHandler: removeTag
			}).settings.submitHandler = removeTag;

			break;

		case 'new':
			$('#translations-myModalLabel').html('New Tag');

			$('#editTag .modal-body #tag').val('');
			$('#editTag .modal-body #tag').attr('placeholder', suggestId());
			$('#editTag .modal-body #tag').attr("disabled",false);

			for(var i in langs){
				$('#editTag #form-tag-'+langs[i]).html('');
				$('#editTag #form-tag-'+langs[i]).attr('placeholder', 'Insert tag related data');
				$('#editTag #form-tag-'+langs[i]).attr("disabled",false);
			}

			$('#editTag .modal-footer').html('<button class="btn btn-success new-btn submit" type="submit" value="Submit"> Create Now </button>');

			$('#editTag').validate({
				rules:{
					tag:{
							minlength: 2,
							required: true
						}
				},

				messages: {tag: "Please insert a unique ID"},

				submitHandler: newTag

			}).settings.submitHandler = newTag;

			break;
	}
	$('#editTag .modal-footer').append('<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>');

	$('#editTag').modal({});

}


function getLangs(){
	var langs = [];

	$('.traduccionesform[id*="trad_form"]').each(function(i,node){
		if(node.lang && node.lang != '')
			langs.push(node.lang);
	});

	return langs;
}

Number.prototype.pad = function(size) {
	var s = String(this);
	while (s.length < (size || 2)) {s = "0" + s;}
	return s;
}

function suggestId(){

	var tags = tradTable['es'].$('tr[id^="id"]');
	if(!tags.length)
		return 'insert new tag id';
	var ids = [];
	var str = '';
	for(var i=0; i < tags.length; i++){
		str = tags[i].id;
		ids.push( str.substr(0,str.indexOf('_')) );
	}
	var id = ids.sort().reverse()[0];
	var newId = str.substr(2,id.length -2 );

	return  'id' + (++newId).pad(5) + '_';
}






























/*----------------------------------------------------------------------------*/
function translatesCallbackTemplate( response ){
	var data = JSON.parse( response );
	//host has tag(0) + Nlangs(1,2,3,4...,N) + 2(N+1, N+2) columns
	////tradTable['undefined'].$('tr[id*="id00033"]')
	//oTable.fnGetPosition( this )
	//each lang has id(0) + tag(1) + editbutton(2) columns


	if(data['success'])
	{

		switch(data['success'])
		{
			case '1':
			/**
		 * Update a table cell or row - this method will accept either a single value to
		 * update the cell with, an array of values with one element for each column or
		 * an object in the same format as the original data source. The function is
		 * self-referencing in order to make the multi column updates easier.
		 *  @param {object|array|string} mData Data to update the cell/row with
		 *  @param {node|int} mRow TR element you want to update or the aoData index
		 *  @param {int} [iColumn] The column to update (not used of mData is an array or object)
		 *  @param {bool} [bRedraw=true] Redraw the table or not
		 *  @param {bool} [bAction=true] Perform predraw actions or not
		 *  @returns {int} 0 on success, 1 on error
		 *  @dtopt API
		 *
		 *  @example
		 *    $(document).ready(function() {
		 *      var oTable = $('#example').dataTable();
		 *      oTable.fnUpdate( 'Example update', 0, 0 ); // Single cell
		 *      oTable.fnUpdate( ['a', 'b', 'c', 'd', 'e'], 1, 0 ); // Row
		 *    } );
		 */

		 		var tagId = data.data[Object.keys(data.data)[0]].tag;

				var rowData = {0:tagId};
				var len = Object.keys(data.data).length;//each lang in response
				for(var i = 0; i < len; i++)
				{
					var lang = Object.keys(data.data)[i];
					rowData[1 + i] = data.data[ lang ].value || '';
					var element = tradTable[lang].$('tr[id*="'+tagId+'"]');

					if(element[0]){
						var row = tradTable[ lang ].fnGetPosition( element[0] );
						tradTable[ lang ].fnUpdate( {
							0:tagId,
							1:data.data[ lang ].value || '',
							2:'<button class="tag-edit-template btn btn-small btn-default" value="'+tagId+'_'+lang+'">Edit</button>'
						},row,true );

					}

				}
				rowData[1+len+0] = '<button class="tag-remove-template btn btn-small btn-danger">Remove</button>';
				rowData[1+len+1] = "<button class='tag-edit-template btn btn-small btn-default'>Edit</button>";

				var element = tradTable['undefined'].$('tr[id*="'+tagId+'"]');
				if(element[0]){
					var row = tradTable[ 'undefined' ].fnGetPosition( element[0] );
					tradTable['undefined'].fnUpdate( rowData,row,true );
				}
				else{
					var newRow = tradTable['undefined'].fnAddData(rowData,true);
					var oSettings = tradTable['undefined'].fnSettings();
					var nTr = oSettings.aoData[ newRow[0] ].nTr;
					nTr.id = tagId;
				}

				//console.log('tag editted successfully!');

				$('#editTag').modal('toggle');
				break;

			case '2':
				//console.log('new tag created & stored successfully!');
				var tagId = data.data[Object.keys(data.data)[0]].tag;

				var rowData = {0:tagId};
				var len = Object.keys(data.data).length;//each lang in response
				for(var i = 0; i < len; i++){
					var lang = Object.keys(data.data)[i];

					rowData[1 + i] = data.data[ lang ].value || '';
					var newRow = tradTable[ lang ].fnAddData({
						0:tagId,
						1:data.data[ lang ].value || '',
						2:'<button class="tag-edit-template btn btn-small btn-default" value="'+tagId+'_'+lang+'">Edit</button>'
					},true);

					var oSettings = tradTable[ lang ].fnSettings();
					var nTr = oSettings.aoData[ newRow[0] ].nTr;
					nTr.id = tagId+'_'+lang;
				}

				rowData[1+len+0] = '<button class="tag-remove-template btn btn-small btn-danger">Remove</button>';
				rowData[1+len+1] = "<button class='tag-remove-template btn btn-small btn-default'>Edit</button>";

				var newRow = tradTable['undefined'].fnAddData(rowData,true);
				var oSettings = tradTable['undefined'].fnSettings();
				var nTr = oSettings.aoData[ newRow[0] ].nTr;
				nTr.id = tagId;

				$('#editTag').modal('toggle');
				break;

			case '3':

				var tagId = data.data[Object.keys(data.data)[0]].tag;
				var len = Object.keys(data.data).length;

				for(var i = 0; i < len; i++)
				{
					var lang = Object.keys(data.data)[i];
					var element = tradTable[lang].$('tr[id*="'+tagId+'"]');

					if(element[0]){
						var row = tradTable[ lang ].fnGetPosition( element[0] );
						tradTable[ lang ].fnDeleteRow( row );
					}

				}

				var element = tradTable['undefined'].$('tr[id*="'+tagId+'"]');
				var row = tradTable[ 'undefined' ].fnGetPosition( element[0] );

				var that = this;
				$.post( Routing.generate('_translationstemplate_getTag',{template:$('input[name=trad_platform]').val(), data: tagId},true),
					function(r){

						var data2 = JSON.parse( r );
						tradTable['undefined'].fnDeleteRow( row );
						var len = Object.keys(data2.data).length;//each lang in response

						for(var i = 0; i < len; i++)
						{

							var lang = Object.keys(data2.data)[i];
							if(data2.data[ lang ][0] == undefined)
								continue;
							var newRow = tradTable[ lang ].fnAddData({
								0:data2.tag,
								1:data2.data[ lang ][0] || '',
								2:'<button class="tag-edit-template btn btn-small btn-default" value="'+that.tagId+'_'+lang+'">Edit</button>'
							},true);
							var oSettings = tradTable[ lang ].fnSettings();
							var nTr = oSettings.aoData[ newRow[0] ].nTr;
							nTr.id = tagId+'_'+lang;
						}
						//console.log('tag removed successfully!');
						$('#editTag').modal('toggle');
					});

				break;

			default:
			//console.log('error!');
			var validator = $('#editTag').validate();
		}


	}
	else{
		//console.log('error2!');
		var validator = $('#editTag').validate();
		//validator.showErrors({ "tag": 'Culomante'});
	}
}
function newTagTemplate(e){
	var tag_data={};
	var langs = getLangs();
	for(var i in langs)
	{
		tag_data[langs[i]] = {
			tag:  $('#editTag #tag').val(),
			lang: langs[i],
			value:$('#editTag #form-tag-'+langs[i])[0].value
		};
	}
	$.post( Routing.generate('_translationstemplate_new',{template:$('input[name=trad_platform]').val(),data:tag_data},true), translatesCallbackTemplate);
}
function saveTagTemplate(e){
	var tag_data={};
	var langs = getLangs();
	for(var i in langs)
	{
		tag_data[langs[i]] = {
			tag:  $('#editTag #tag').val(),
			lang: langs[i],
			value:$('#editTag #form-tag-'+langs[i])[0].value
		};
	}

	$.post( Routing.generate('_translationstemplate_save',{template:$('input[name=trad_platform]').val(),data:tag_data},true), translatesCallbackTemplate);
}
function removeTagTemplate(e){
	var tag_data={};
	var langs = getLangs();
	for(var i in langs)
	{
		tag_data[langs[i]] = {
			tag:  $('#editTag #tag').val(),
			lang: langs[i],
			value:$('#editTag #form-tag-'+langs[i])[0].value
		};
	}

	$.post( Routing.generate('_translationstemplate_remove',{template:$('input[name=trad_platform]').val(),data:tag_data},true), translatesCallbackTemplate);
}

/*--------------Open NEW/EDIT/REMOVE Modals---------------*/
function createTagModalTemplate(e){

	e.stopPropagation();
	e.preventDefault();
	buildModalTemplate([],'new');

}
function editTagModalTemplate(e){

	e.stopPropagation();
	e.preventDefault();

	var tag = $(e.currentTarget.parentElement.parentElement.children[0]).text();
	var data = [];
	var langs = getLangs();
	for(var i in langs){
		var el = null;
		el = tradTable[langs[i]].$('tr[id*="'+tag+'_'+langs[i]+'"]');
		var lang_data = {
			tag_id: tag,
			lang: langs[i],
			value:el.children().eq(1).text()
		};
		data.push(lang_data);
	}
	buildModalTemplate(data,'edit');

}
function removeTagModalTemplate(e){

	e.stopPropagation();
	e.preventDefault();

	var tag = $(e.currentTarget.parentElement.parentElement.children[0]).text();
	var data = [];
	var langs = getLangs();

	for(var i in langs){
		var el = tradTable[langs[i]].$('tr[id="'+tag+'_'+langs[i]+'"]');
		tradTable.myTagElement = el;
		data.push({
			tag_id: tag,
			lang: langs[i],
			value:el.children().eq(1).text()
		});
	}
	buildModalTemplate(data,'delete');

}
function buildModalTemplate(data, type)
{
	data = data || [];

	var langs = getLangs();
	var tabs = '';
	var content = '';

	$('#editTag #modal-head').html('');
	$('#editTag .modal-body .tab-content').html('');
	for(var i in langs){
		$('#editTag #modal-head').append('<li id="head'+i+'"><a href="#'+langs[i]+'">'+langs[i]+'</a></li>');

		$('#editTag .modal-body .tab-content').append('<div class="tab-pane" id="'+langs[i]+'"><p id="tag-text-'+langs[i]+'"><textarea id="form-tag-'+langs[i]+'" name="form-tag-'+langs[i]+'" class="textarea-large"></textarea></p></div>');

		if(i == 0){
			$('#editTag #modal-head #head0').addClass("active");
			$('#editTag .tab-content #'+langs[0]).addClass("active");
		}
	}

	switch( type ){

		case 'edit':
			$('#translations-myModalLabel').html('Edit Tag');

			$('#editTag .modal-body #tag').val( data[0].tag_id );
			$('#editTag .modal-body #tag').attr("disabled",true);

			for(var i in langs){
				$('#editTag #form-tag-'+langs[i]).val(data[i].value);
			}

			$('#editTag .modal-footer').html('<button class="btn btn-primary save-btn submit" type="submit" value="Submit"> Save changes </button>');

			$('#editTag').validate({
				rules:{},
				messages: {},
				submitHandler: saveTagTemplate

			}).settings.submitHandler = saveTagTemplate;


			break;

		case 'delete':
			$('#translations-myModalLabel').html('Delete Tag');

			$('#editTag .modal-body #tag').val( data[0].tag_id );
			$('#editTag .modal-body #tag').attr("disabled",true);

			for(var i in langs){
				$('#editTag #form-tag-'+langs[i]).val(data[i].value);
				$('#editTag #form-tag-'+langs[i]).attr('placeholder', '');
				$('#editTag #form-tag-'+langs[i]).attr("disabled",true);
			}

			$('#editTag .modal-footer').html('<button class="btn btn-danger delete-btn submit" type="submit" value="Submit"> Delete Permanently </button>');

			$('#editTag').validate({
				rules:{},
				messages: {},
				submitHandler: removeTagTemplate
			}).settings.submitHandler = removeTagTemplate;

			break;

		case 'new':
			$('#translations-myModalLabel').html('New Tag');

			$('#editTag .modal-body #tag').val('');
			$('#editTag .modal-body #tag').attr('placeholder', suggestId());
			$('#editTag .modal-body #tag').attr("disabled",false);

			for(var i in langs){
				$('#editTag #form-tag-'+langs[i]).html('');
				$('#editTag #form-tag-'+langs[i]).attr('placeholder', 'Insert tag related data');
				$('#editTag #form-tag-'+langs[i]).attr("disabled",false);
			}

			$('#editTag .modal-footer').html('<button class="btn btn-success new-btn submit" type="submit" value="Submit"> Create Now </button>');

			$('#editTag').validate({
				rules:{
					tag:{
							minlength: 2,
							required: true
						}
				},

				messages: {tag: "Please insert a unique ID"},

				submitHandler: newTagTemplate

			}).settings.submitHandler = newTagTemplateMails;

			break;
	}
	$('#editTag .modal-footer').append('<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>');

	$('#editTag').modal({});

}

/*--------------Bind evets-----------------------------*/
$(document).on('click','.tag-new-template-mail', 	createTagModalTemplateMails);
$(document).on('click','.tag-edit-template-mail', 	editTagModalTemplateMails);
$(document).on('click','.tag-remove-template-mail', removeTagModalTemplateMails);











/*--------------Save validation N petition to server---*/

function translatesCallbackTemplateMails( response ){
	var data = JSON.parse( response );
	//host has tag(0) + Nlangs(1,2,3,4...,N) + 2(N+1, N+2) columns
	////tradTable['undefined'].$('tr[id*="id00033"]')
	//oTable.fnGetPosition( this )
	//each lang has id(0) + tag(1) + editbutton(2) columns


	if(data['success'])
	{

		switch(data['success'])
		{
			case '1':
			/**
		 * Update a table cell or row - this method will accept either a single value to
		 * update the cell with, an array of values with one element for each column or
		 * an object in the same format as the original data source. The function is
		 * self-referencing in order to make the multi column updates easier.
		 *  @param {object|array|string} mData Data to update the cell/row with
		 *  @param {node|int} mRow TR element you want to update or the aoData index
		 *  @param {int} [iColumn] The column to update (not used of mData is an array or object)
		 *  @param {bool} [bRedraw=true] Redraw the table or not
		 *  @param {bool} [bAction=true] Perform predraw actions or not
		 *  @returns {int} 0 on success, 1 on error
		 *  @dtopt API
		 *
		 *  @example
		 *    $(document).ready(function() {
		 *      var oTable = $('#example').dataTable();
		 *      oTable.fnUpdate( 'Example update', 0, 0 ); // Single cell
		 *      oTable.fnUpdate( ['a', 'b', 'c', 'd', 'e'], 1, 0 ); // Row
		 *    } );
		 */

		 		var tagId = data.data[Object.keys(data.data)[0]].tag;

				var rowData = {0:tagId};
				var len = Object.keys(data.data).length;//each lang in response
				for(var i = 0; i < len; i++)
				{
					var lang = Object.keys(data.data)[i];
					rowData[1 + i] = data.data[ lang ].value || '';
					var element = tradTable[lang].$('tr[id*="'+tagId+'"]');

					if(element[0]){
						var row = tradTable[ lang ].fnGetPosition( element[0] );
						tradTable[ lang ].fnUpdate( {
							0:tagId,
							1:data.data[ lang ].value || '',
							2:'<button class="tag-edit-template-mail btn btn-small btn-default" value="'+tagId+'_'+lang+'">Edit</button>'
						},row,true );

					}

				}
				rowData[1+len+0] = '<button class="tag-remove-template-mail btn btn-small btn-danger">Remove</button>';
				rowData[1+len+1] = "<button class='tag-edit-template-mail btn btn-small btn-default'>Edit</button>";

				var element = tradTable['undefined'].$('tr[id*="'+tagId+'"]');
				if(element[0]){
					var row = tradTable[ 'undefined' ].fnGetPosition( element[0] );
					tradTable['undefined'].fnUpdate( rowData,row,true );
				}
				else{
					var newRow = tradTable['undefined'].fnAddData(rowData,true);
					var oSettings = tradTable['undefined'].fnSettings();
					var nTr = oSettings.aoData[ newRow[0] ].nTr;
					nTr.id = tagId;
				}

				//console.log('tag editted successfully!');

				$('#editTag').modal('toggle');
				break;

			case '2':
				//console.log('new tag created & stored successfully!');
				var tagId = data.data[Object.keys(data.data)[0]].tag;

				var rowData = {0:tagId};
				var len = Object.keys(data.data).length;//each lang in response
				for(var i = 0; i < len; i++){
					var lang = Object.keys(data.data)[i];

					rowData[1 + i] = data.data[ lang ].value || '';
					var newRow = tradTable[ lang ].fnAddData({
						0:tagId,
						1:data.data[ lang ].value || '',
						2:'<button class="tag-edit-template-mail btn btn-small btn-default" value="'+tagId+'_'+lang+'">Edit</button>'
					},true);

					var oSettings = tradTable[ lang ].fnSettings();
					var nTr = oSettings.aoData[ newRow[0] ].nTr;
					nTr.id = tagId+'_'+lang;
				}

				rowData[1+len+0] = '<button class="tag-remove-template-mail btn btn-small btn-danger">Remove</button>';
				rowData[1+len+1] = "<button class='tag-remove-template-mail btn btn-small btn-default'>Edit</button>";

				var newRow = tradTable['undefined'].fnAddData(rowData,true);
				var oSettings = tradTable['undefined'].fnSettings();
				var nTr = oSettings.aoData[ newRow[0] ].nTr;
				nTr.id = tagId;

				$('#editTag').modal('toggle');
				break;

			case '3':

				var tagId = data.data[Object.keys(data.data)[0]].tag;
				var len = Object.keys(data.data).length;

				for(var i = 0; i < len; i++)
				{
					var lang = Object.keys(data.data)[i];
					var element = tradTable[lang].$('tr[id*="'+tagId+'"]');

					if(element[0]){
						var row = tradTable[ lang ].fnGetPosition( element[0] );
						tradTable[ lang ].fnDeleteRow( row );
					}

				}

				var element = tradTable['undefined'].$('tr[id*="'+tagId+'"]');
				var row = tradTable[ 'undefined' ].fnGetPosition( element[0] );

				var that = this;
				$.post( Routing.generate('_mailstemplate_getmailtag',{template:$('input[name=trad_platform]').val(), data: tagId},true),
					function(r){

						var data2 = JSON.parse( r );
						tradTable['undefined'].fnDeleteRow( row );
						var len = Object.keys(data2.data).length;//each lang in response

						for(var i = 0; i < len; i++)
						{

							var lang = Object.keys(data2.data)[i];
							if(data2.data[ lang ][0] == undefined)
								continue;
							var newRow = tradTable[ lang ].fnAddData({
								0:data2.tag,
								1:data2.data[ lang ][0] || '',
								2:'<button class="tag-edit-template-mail btn btn-small btn-default" value="'+that.tagId+'_'+lang+'">Edit</button>'
							},true);
							var oSettings = tradTable[ lang ].fnSettings();
							var nTr = oSettings.aoData[ newRow[0] ].nTr;
							nTr.id = tagId+'_'+lang;
						}
						//console.log('tag removed successfully!');
						$('#editTag').modal('toggle');
					});

				break;

			default:
			//console.log('error!');
			var validator = $('#editTag').validate();
		}


	}
	else{
		//console.log('error2!');
		var validator = $('#editTag').validate();
	}
}
function newTagTemplateMails(e){
	var tag_data={};
	var langs = getLangs();
	for(var i in langs)
	{
		tag_data[langs[i]] = {
			tag:  $('#editTag #tag').val(),
			lang: langs[i],
			value:$('#editTag #form-tag-'+langs[i])[0].value
		};
	}
	$.post( Routing.generate('_mailstemplate_newmailtag',{template:$('input[name=trad_platform]').val(),data:tag_data},true), translatesCallbackTemplateMails);
}
function saveTagTemplateMails(e){
	var tag_data={};
	var langs = getLangs();
	for(var i in langs)
	{
		tag_data[langs[i]] = {
			tag:  $('#editTag #tag').val(),
			lang: langs[i],
			value:$('#editTag #form-tag-'+langs[i])[0].value
		};
	}

	$.post( Routing.generate('_mailstemplate_savemailtag',{template:$('input[name=trad_platform]').val(),data:tag_data},true), translatesCallbackTemplateMails);
}
function removeTagTemplateMails(e){
	var tag_data={};
	var langs = getLangs();
	for(var i in langs)
	{
		tag_data[langs[i]] = {
			tag:  $('#editTag #tag').val(),
			lang: langs[i],
			value:$('#editTag #form-tag-'+langs[i])[0].value
		};
	}

	$.post( Routing.generate('_mailstemplate_removemailtag',{template:$('input[name=trad_platform]').val(),data:tag_data},true), translatesCallbackTemplateMails);
}

/*--------------Open NEW/EDIT/REMOVE Modals---------------*/
function createTagModalTemplateMails(e){

	e.stopPropagation();
	e.preventDefault();
	buildModalTemplateMails([],'new');

}
function editTagModalTemplateMails(e){

	e.stopPropagation();
	e.preventDefault();

	var tag = $(e.currentTarget.parentElement.parentElement.children[0]).text();
	var data = [];
	var langs = getLangs();
	for(var i in langs){
		var el = null;
		el = tradTable[langs[i]].$('tr[id*="'+tag+'_'+langs[i]+'"]');
		var lang_data = {
			tag_id: tag,
			lang: langs[i],
			value:el.children().eq(1).text()
		};
		data.push(lang_data);
	}
	buildModalTemplateMails(data,'edit');

}
function removeTagModalTemplateMails(e){

	e.stopPropagation();
	e.preventDefault();

	var tag = $(e.currentTarget.parentElement.parentElement.children[0]).text();
	var data = [];
	var langs = getLangs();

	for(var i in langs){
		var el = tradTable[langs[i]].$('tr[id="'+tag+'_'+langs[i]+'"]');
		tradTable.myTagElement = el;
		data.push({
			tag_id: tag,
			lang: langs[i],
			value:el.children().eq(1).text()
		});
	}
	buildModalTemplateMails(data,'delete');

}
function buildModalTemplateMails(data, type)
{
	data = data || [];

	var langs = getLangs();
	var tabs = '';
	var content = '';

	$('#editTag #modal-head').html('');
	$('#editTag .modal-body .tab-content').html('');
	for(var i in langs){
		$('#editTag #modal-head').append('<li id="head'+i+'"><a href="#'+langs[i]+'">'+langs[i]+'</a></li>');

		$('#editTag .modal-body .tab-content').append('<div class="tab-pane" id="'+langs[i]+'"><p id="tag-text-'+langs[i]+'"><textarea id="form-tag-'+langs[i]+'" name="form-tag-'+langs[i]+'" class="textarea-large"></textarea></p></div>');

		if(i == 0){
			$('#editTag #modal-head #head0').addClass("active");
			$('#editTag .tab-content #'+langs[0]).addClass("active");
		}
	}

	switch( type ){

		case 'edit':
			$('#translations-myModalLabel').html('Edit Tag');

			$('#editTag .modal-body #tag').val( data[0].tag_id );
			$('#editTag .modal-body #tag').attr("disabled",true);

			for(var i in langs){
				$('#editTag #form-tag-'+langs[i]).val(data[i].value);
			}

			$('#editTag .modal-footer').html('<button class="btn btn-primary save-btn submit" type="submit" value="Submit"> Save changes </button>');

			$('#editTag').validate({
				rules:{},
				messages: {},
				submitHandler: saveTagTemplateMails

			}).settings.submitHandler = saveTagTemplateMails;


			break;

		case 'delete':
			$('#translations-myModalLabel').html('Delete Tag');

			$('#editTag .modal-body #tag').val( data[0].tag_id );
			$('#editTag .modal-body #tag').attr("disabled",true);

			for(var i in langs){
				$('#editTag #form-tag-'+langs[i]).val(data[i].value);
				$('#editTag #form-tag-'+langs[i]).attr('placeholder', '');
				$('#editTag #form-tag-'+langs[i]).attr("disabled",true);
			}

			$('#editTag .modal-footer').html('<button class="btn btn-danger delete-btn submit" type="submit" value="Submit"> Delete Permanently </button>');

			$('#editTag').validate({
				rules:{},
				messages: {},
				submitHandler: removeTagTemplateMails
			}).settings.submitHandler = removeTagTemplateMails;

			break;

		case 'new':
			$('#translations-myModalLabel').html('New Tag');

			$('#editTag .modal-body #tag').val('');
			$('#editTag .modal-body #tag').attr('placeholder', suggestId());
			$('#editTag .modal-body #tag').attr("disabled",false);

			for(var i in langs){
				$('#editTag #form-tag-'+langs[i]).html('');
				$('#editTag #form-tag-'+langs[i]).attr('placeholder', 'Insert tag related data');
				$('#editTag #form-tag-'+langs[i]).attr("disabled",false);
			}

			$('#editTag .modal-footer').html('<button class="btn btn-success new-btn submit" type="submit" value="Submit"> Create Now </button>');

			$('#editTag').validate({
				rules:{
					tag:{
							minlength: 2,
							required: true
						}
				},

				messages: {tag: "Please insert a unique ID"},

				submitHandler: newTagTemplateMails

			}).settings.submitHandler = newTagTemplateMails;

			break;
	}
	$('#editTag .modal-footer').append('<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>');

	$('#editTag').modal({});

}

/*--------------Bind evets-----------------------------*/
$(document).on('click','.tag-new-template', 	createTagModalTemplate);
$(document).on('click','.tag-edit-template', 	editTagModalTemplate);
$(document).on('click','.tag-remove-template', removeTagModalTemplate);
/*--------------Save validation N petition to server---*/
