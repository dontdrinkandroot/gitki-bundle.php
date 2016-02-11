var actionNames = {
	"author" : "РђРІС‚РѕСЂ",
	"translator" : "РџСЂРµРІРѕРґР°С‡",
	"text" : "Р—Р°РіР»Р°РІРёРµ",
	"series" : "РЎРµСЂРёСЏ",
	"book" : "РљРЅРёРіР°",
	"label" : "Р•С‚РёРєРµС‚"
};
$(function(){
	var defSearchVal = "РўСЉСЂСЃРµРЅРµ РЅР°вЂ¦";
	var accesskey = "Рў";
	var $search = $('<input type="text" id="q" tabindex="0" class="search"/>')
		.attr({
			"value"     : defSearchVal,
			"accesskey" : accesskey,
			"title"     : "РљР»Р°РІРёС€ Р·Р° РґРѕСЃС‚СЉРї вЂ” " + accesskey
		})
		.focus(function(){
			if (this.value == defSearchVal) {this.value = ""}
		})
		.blur(function(){
			if (this.value == "") {this.value = defSearchVal}
		})
		.autocomplete(searchData, {
			formatItem: function(item) {
				return '<span class="ac_action ac_'+ item.action +'">' + item.text + ' <span class="ac_comment">('+ actionNames[item.action] +')</span></span>';
			},
			formatResult: function(item) {
				return item.text;
			},
			matchContains: true,
			max: 25,
			scroll: false
		}).result(function(event, item) {
			location.href = mgSettings.webroot + item.action + "/" + item.url;
		});
	$('<div id="search"><form><ul><li></li></ul></form></div>')
		.contents()
			.find("li").append($search).end()
			.parent()
		.appendTo("body");
});
