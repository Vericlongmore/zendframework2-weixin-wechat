function Submit(url, data) {
	this.url = url;
	this.data = data;
	this.name = 'data';
	this.execute();
}
Submit.prototype = {
	constructor : Submit,
	execute : function() {
		input = $('<input />');
		input.attr("name", this.name);
		input.val(this.data);
		form = $('<form method="post" style="display: none;"></form>');
		form.attr("action", this.url);
		form.appendTo($("body")).append(input);
		form.submit();
	}
};
function Config(options, container, url, submit) {
	this.url = url;
	this.container = container;
	this.submit = submit;
	this.options = options;
	this.create();
}
Config.prototype = {
	constructor : Config,
	create : function() {
		for (key in this.options) {
			option = this.options[key];
			switch (option.type) {
			case 'array':
				this.createArray(option);
				break;
			case 'news':
				this.createNews(option);
				break;
			case 'keyList':
				this.createKeyList(option);
				break;
			case 'richContent':
				this.createRich(option);
				break;
			case 'text':
				this.createText(option);
				break;
			case 'toggle':
				this.createToggle(option);
				break;
			case 'select':
				this.createSelect(option);
				break;
			}
		}
		this.createSubmit();
	},
	createSubmit : function() {
		var config = this;
		var submit = function() {
			$.post(config.url, {
				'data' : JSON.stringify(config.options)
			}, function(result, status) {
				alert(result);
			});
		};
		if (this.submit) {
			submit = this.submit;
		}
		$('<button>提交</button>').appendTo(this.container).click({
			'config' : config
		}, submit);
	},
	createContainer : function(option) {
		return $('<div class="form-group options-item"></div>').appendTo(
				this.container);
	},
	createLabel : function(option) {
		return $('<label>' + option.name + '</label>');
	},
	createToggle : function(option) {
		container = this.createContainer(option);
		this.createLabel(option).appendTo(container);
		$('<a>').attr('id', option.key).html(option.value).appendTo(container)
				.click(function() {
					if ($(this).html() == 'off')
						$(this).html('on');
					else
						$(this).html('off');
				});
	},
	createArray : function(option) {
		container = this.createContainer(option);
		this.createLabel(option).appendTo(container);
	},
	createNews : function(option) {
		container = this.createContainer(option);
		this.createLabel(option).appendTo(container);
		this.createNewsContent(option.value, container);
	},
	createRich : function(option) {
		container = this.createContainer(option);
		this.createLabel(option).appendTo(container);
		var keySet = $('<div>').addClass('kw_set').appendTo(container);
		this.createRichContent(option.value, keySet);
	},
	createKeyList : function(option) {
		container = this.createContainer(option);
		this.createLabel(option).appendTo(container);
		var outDiv = $('<div>').addClass('outDiv').appendTo(container);
		var addDiv = $('<div>').addClass('jsAddDiv').appendTo(outDiv);
		if (option.value.length == 0) {
			option.value = {};
		}
		for (i in option.value) {
			this.createKeyListItem(option.value, i, addDiv);
		}
		this_obj = this;
		$('<a>').html('添加关键字').addClass('kw_add_btn').appendTo(outDiv).click(
				function() {
					var key = 'new';
					option.value[key] = {};
					this_obj.createKeyListItem(option.value, key, addDiv);
				});
	},
	createKeyListItem : function(list, i, container) {
		var itemData = list[i];
		var key = i;
		var keyBox = $('<div>').addClass('keyword_box').appendTo(container);
		// keyBox
		var keyText = $('<div>').addClass('kw_text').appendTo(keyBox);
		var keySet = $('<div>').addClass('kw_set').appendTo(keyBox);
		$('<a>').attr('title', '删除本条关键字').html('删除关键字').addClass('kw_delete')
				.appendTo(keyBox).click(function() {
					delete list[i];
					keyBox.remove();
				});
		// keyText
		$('<span>').html('关键字:').appendTo(keyText);
		$('<input>').attr('type', 'text').val(key).appendTo(keyText).focusout(
				function() {
					var key = $(this).val();
					delete list[i];
					list[key] = itemData;
				});
		// keySet
		this.createRichContent(itemData, keySet, i);
	},
	createRichContent : function(itemData, keySet, i) {
		if (!itemData['newsContent']) {
			itemData['newsContent'] = new Array();
		}
		var newsContent = itemData['newsContent'];
		var this_obj = this;
		var textSetRadio = $(
				'<input class="radios" name="kw_' + i
						+ '" type="radio" id="text' + i + '" />').appendTo(
				keySet).change(function() {
			if ($(this).attr('checked') == 'checked') {
				keySet.children('.jsTextSet,.jsImgSet').css('display', 'none');
				keySet.children('.jsTextSet').css('display', 'block');
				itemData['type'] = 'text';
			}
		});
		$('<label for="text' + i + '">').html('文本消息').appendTo(keySet);
		var imgSetRadio = $(
				'<input class="radios" name="kw_' + i
						+ '" type="radio" id="img' + i + '" />').appendTo(
				keySet).change(function() {
			if ($(this).attr('checked') == 'checked') {
				keySet.children('.jsTextSet,.jsImgSet').css('display', 'none');
				keySet.children('.jsImgSet').css('display', 'block');
				itemData['type'] = 'news';
			}
		});
		$('<label for="img' + i + '">').html('图文消息').appendTo(keySet);
		// textSet
		textSet = $(' <textarea>').addClass('jsTextSet').appendTo(keySet).val(
				itemData['textContent']).change(function() {
			itemData['textContent'] = $(this).val();
		});
		// imgSet
		var imgSet = $('<div>').addClass('imgSet jsImgSet').appendTo(keySet);
		var imgDiv = $('<div>').addClass('imgDiv').appendTo(imgSet);
		for (j in newsContent) {
			this_obj.createKeyListItemImgSet(newsContent, j, imgDiv);
		}
		$('<a>').attr('title', '添加一条新的图文消息').html('添加图文消息').addClass('img_add')
				.appendTo(imgSet).click(function() {
					j = newsContent.length;
					newsContent[j] = {};
					this_obj.createKeyListItemImgSet(newsContent, j, imgDiv);
				});
		// init
		switch (itemData['type']) {
		case 'text':
			textSetRadio.trigger('click');
			break;
		case 'news':
			imgSetRadio.trigger('click');
			break;
		}
	},
	createKeyListItemImgSet : function(list, j, imgSet) {
		var div = $('<div>').addClass('imgDivItem').appendTo(imgSet);
		$('<span>').html('标题').appendTo(div);
		$('<input type="text" />').appendTo(div).val(list[j]['Title'])
				.focusout(function() {
					list[j]['Title'] = $(this).val();
				});
		$('<span>').html('摘要').appendTo(div);
		$('<input type="text" />').appendTo(div).val(list[j]['Description'])
				.focusout(function() {
					list[j]['Description'] = $(this).val();
				});
		$('<span>').html('图片地址').appendTo(div);
		$('<input type="text" />').appendTo(div).val(list[j]['PicUrl'])
				.focusout(function() {
					list[j]['PicUrl'] = $(this).val();
				});
		$('<span>').html('链接地址').appendTo(div);
		$('<input type="text" />').appendTo(div).val(list[j]['Url']).focusout(
				function() {
					list[j]['Url'] = $(this).val();
				});
		$('<a>').attr('title', '删除本条图文消息').html('删除图文消息')
				.addClass('img_delete').appendTo(div).click(function() {
					list.splice(j, 1);
					div.remove();
				});
	},
	createNewsContent : function(list, container) {
		var ul = $('<ul>').addClass('news_item').appendTo(container);
		var li = $('<li>').appendTo(ul);
		title = $('<p>').html('标题').appendTo(li);
		description = $('<p>').html('描述').appendTo(li);
		image = $('<p>').html('图片').appendTo(li);
		url = $('<p>').html('链接').appendTo(li);
		opr = $('<p>').html('操作').appendTo(li);
		for (i in list) {
			this.createNewsItem(list, i, ul);
		}
		this_obj = this;
		$('<a>').html('添加').appendTo(container).click(function() {
			i = list.length;
			list.push(new Object());
			this_obj.createNewsItem(list, i, ul);
		});
	},
	createNewsItem : function(list, i, container) {
		var li = $('<li></li>').appendTo(container);
		titleInput = $('<input />').val(list[i].Title).appendTo(li).focusout(
				function() {
					list[i].Title = $(this).val();
				});
		descriptionInput = $('<input />').val(list[i].Description).appendTo(li)
				.focusout(function() {
					list[i].Description = $(this).val();
				});
		imageInput = $('<input />').val(list[i].PicUrl).appendTo(li).focusout(
				function() {
					list[i].PicUrl = $(this).val();
				});
		urlInput = $('<input />').val(list[i].Url).appendTo(li).focusout(
				function() {
					list[i].Url = $(this).val();
				});
		deleteInput = $('<input type="button"/>').val('删除').appendTo(li).click(
				function() {
					list.splice(i, 1);
					li.remove();
				});
	},
	createText : function(option) {
		container = this.createContainer(option);
		this.createLabel(option).appendTo(container);
		$(
				'<input class="noInfo" name="' + option.key + '" value="'
						+ option.value + '">').appendTo(container).focusout(
				function() {
					option.value = $(this).val();
				});
	},
	createSelect : function(option) {
		container = this.createContainer(option);
		this.createLabel(option).appendTo(container);
		ol = $('<ol>').addClass('selectable').appendTo(container).data(
				'option', option).on("selectableselected", function(event, ui) {
			option = $(this).data('option');
			value = $(this).find(".ui-selected").data('value');
			option.value = value;
		});
		var list = option.select_list;
		for (i in list) {
			li = $('<li>').addClass('ui-widget-content').html(list[i].name)
					.appendTo(ol);
			li.data('value', list[i].value);
			if (list[i].value == option.value) {
				li.addClass("ui-selected");
			}
		}
		ol.selectable();
	}
};