(function(){var e=this.ModeDialog=new Class({Implements:[Options,Events],options:{params:{},title:"title",width:800,height:600,resizable:!0},initialize:function(c,a){if(c){this.url=c;this.setOptions(a);a=this.options;a.resizable=a.resizable?"yes":"no";a.handle&&(this.handle=$(a.handle));var b;b="dialogWidth={width}px;dialogHeight={height}px;resizable={resizable};status=no;".substitute(a);this.fireEvent("init");this.returnValue=window.showModalDialog(this.url,this,b);this.isSubmit&&this.onClose.call(this,
this.returnValue)}},onLoad:function(c){this.win=c;this.doc=this.win.document;this.fireEvent("load",[this,this.options.pname,this.options.params]);this.onShow.call(this)},onShow:function(){this.fireEvent("show");this.doc.getElement(".dialogBtn")&&this.doc.getElement(".dialogBtn").addEvent("click",function(){try{this.submit.call(this,this.win)}catch(c){}this.win.close();this.isSubmit=!0}.bind(this))},onClose:function(c){this.fireEvent("hide",[c])}});finderDialog=new Class({Extends:e,options:{onLoad:function(){if($(this.handle)){var c=
$(this.handle).getParent().getElement("input[type=hidden]").value;if(c){var a=this.doc.getElement("form[id^=finder-form-]");a.store("rowselected",c.split(","));this.win.fireEvent("resize");a.id.slice(-6)}}},onHide:function(c){if(c&&c.length){for(var a=new Element("div"),b=document.createDocumentFragment(),d=this.options.params,f=0,e=c.length;f<e;f++)b.appendChild(new Element("input",{type:"hidden",name:d.name,value:c[f]}));a.appendChild(b);b=d.postdata?a.toQueryString()+"&"+d.postdata:a.toQueryString();
b=this.filterData?b+"&filter[advance]="+this.filterData:b;(new Request({url:d.url,onSuccess:function(b){a.destroy();d.type&&this.options.select(d,b,c);this.fireEvent("callback",b)}.bind(this)})).send(b)}},select:function(c,a,b){"radio"==c.type?JSON.decode(a)&&("INPUT"===$(this.handle).tagName?$(this.handle).value=JSON.decode(a).name:$(this.handle).setText(JSON.decode(a).name)):"checkbox"==c.type&&($(this.handle).innerHTML=a);$(this.handle).getParent().getElement("input[type=hidden]").value=b}},submit:function(c){var a=
this.doc.getElement("form[id^=finder-form-]"),b=a.retrieve("rowselected",""),a=decodeURI(a.getElement("input[id^=finder-filter-]").value);this.options.params.app&&(a&&"_ALL_"==b)&&(a=a.replace(/&amp;/g,","),a=a.replace(/&/g,","),this.filterData=a=encodeURIComponent(a));b&&b.length&&(c.returnValue=b.toString().split(","))}});imgDialog=new Class({Extends:e,options:{onCallback:function(c,a){var b=$(this.handle).getParent(".image-input"),d=b.getElement("input"),b=b.getElement("img");b.removeProperties("width",
"height");d.value=c;b.src=a}},imgcallback:function(c,a){this.isSubmit=!0;this.fireEvent("callback",[c,a]);this.win.close()}})})();