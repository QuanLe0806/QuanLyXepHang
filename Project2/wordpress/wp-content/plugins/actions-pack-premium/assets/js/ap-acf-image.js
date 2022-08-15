(function(a){var b=acf.Field.extend({type:"image_actions_pack",$control:function(){return this.$(".acf-image-uploader")},$input:function(){return this.$("input[type=\"hidden\"]")},events:{'click a[data-name="add"]':"onClickAdd",'click a[data-name="edit"]':"onClickEdit",'click a[data-name="remove"]':"onClickRemove",'change input[type="file"]':"onChange"},initialize:function(){"basic"===this.get("uploader")&&this.$el.closest("form").attr("enctype","multipart/form-data")},validateAttachment:function(a){a&&a.attributes&&(a=a.attributes),a=acf.parseArgs(a,{id:0,url:"",alt:"",title:"",caption:"",description:"",width:0,height:0});var b=acf.isget(a,"sizes",this.get("preview_size"));return b&&(a.url=b.url,a.width=b.width,a.height=b.height),a},render:function(a){a=this.validateAttachment(a),this.$("img").attr({src:a.url,alt:a.alt}),a.id?(this.val(a.id),this.$control().addClass("has-value")):(this.val(""),this.$control().removeClass("has-value"))},append:function(a,b){var c=function(a,b){for(var c=acf.getFields({key:a.get("key"),parent:b.$el}),d=0;d<c.length;d++)if(!c[d].val())return c[d];return!1},d=c(this,b);d||(b.$(".acf-button:last").trigger("click"),d=c(this,b)),d&&d.render(a)},selectAttachment:function(){var b=this.parent(),c=b&&"repeater"===b.get("type"),d=acf.newMediaPopup({mode:"select",type:"image",title:acf.__("Select Image"),field:this.get("key"),multiple:c,library:this.get("library"),allowedTypes:this.get("mime_types"),select:a.proxy(function(a,c){0<c?this.append(a,b):this.render(a)},this)})},editAttachment:function(){var b=this.val();if(b)acf.newMediaPopup({mode:"edit",title:acf.__("Edit Image"),button:acf.__("Update Image"),attachment:b,field:this.get("key"),select:a.proxy(function(a){this.render(a)},this)})},removeAttachment:function(){this.render(!1)},onClickAdd:function(){this.selectAttachment()},onClickEdit:function(){this.editAttachment()},onClickRemove:function(){this.removeAttachment()},onChange:function(b,c){var d=this.$input();acf.getFileInputData(c,function(b){d.val(a.param(b))})}});acf.registerFieldType(b)})(jQuery);