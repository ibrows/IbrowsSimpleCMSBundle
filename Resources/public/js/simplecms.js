jQuery ( document ).ready( function() {
    simplecmsloadBind('div.simplecms-edit');
    simplecmsloadBindInner('div.simplecms-edit');
} );
function ajaxfilemanager(field_name, url, type, win) {
	var view = 'detail';
	switch (type) {
		case "image":
		view = 'thumbnail';
			break;
		case "media":
			break;
		case "flash": 
			break;
		case "file":
			break;
		default:
			return false;
	}
    tinyMCE.activeEditor.windowManager.open({       
        url: ajaxfilemanagerurl+"?view=" + view,
        width: 782,
        height: 440,
        inline : "yes",
        close_previous : "no"
    },{
        window : win,
        input : field_name
    });
    
}
    
function simplecmsAjaxing(href, me){
    
    if(jQuery('.simplecms-dialog').length !== 0){
        //allready open
        return;
    }
        jQuery.ajax({
        url: href,
        context: me,
        success: function(html) {   
            var replacmentid = jQuery(this).prop('id');
            if(jQuery(this).hasClass('simplecms-add')){
                replacmentid = jQuery(this).parent().prop('id')
            }
            var modalstr = html;
            var modal = jQuery(modalstr).modal({backdrop: false, keyboard: true});
            
            modal
                .find('form.simplecms-html textarea')
                .tinymce(simple_cms_wysiwyg_config)
                ;
            parent = this;
            modal.find('.btn-primary').bind('click', function() {
                jQuery('.simplecms-dialog form').ajaxForm({
                    success: function(data, statusText, xhr, form){
                        source = replacmentid;
                        if(source != jQuery(data).prop('id') ){
                            alert('can\'t save: '+data);
                        }else{
                            jQuery('.'+source).html(jQuery(data).html());
                            if(jQuery('#'+source).hasClass('simplecms-edit-collection')){
                                simplecmsloadBind('.'+source+' div.simplecms-edit');  
                                simplecmsloadBindInner('.'+source);    
                            }else{
                                simplecmsloadBindInner('.'+source);
                            }
                        }
                        modal.hide();
                        modal.remove();
                    },
                    error: function(html){
                        alert('can\'t save');
                        modal.hide();
                        modal.remove();
                    }
                }).submit();       
                
            });
           
            
           modal.on('hide.bs.modal', function () {
               modal.remove();
           })
        },
        
        error: function()
        {
            alert('forbidden');
        }
    });
}

function simplecmsloadBindInner(parent){
    jQuery(parent+' a.simplecms-editlink').bind('click', function(event){
        event.preventDefault();
        event.stopPropagation();
        
        var me = jQuery(this).parent('div');
        simplecmsDeactivateItem(me);

        var href = jQuery(this).prop('href') ;        

        simplecmsAjaxing(href,me);  
        
    });    
    jQuery(parent+' a.simplecms-deletelink').bind('click', function(event){
        event.preventDefault();
        event.stopPropagation();
        
        var me = jQuery(this).parent('div');
        simplecmsDeactivateItem(me);
        
        var href = jQuery(this).prop('href') ;        
        
        if(false==confirm(  "delete ?")){
            return false;
        }
        
        jQuery.ajax({
            url: href,
            context: me,
            success: function(data) {
                if(jQuery(this).parent().hasClass('simplecms-edit-collection')){
                   jQuery(this).hide();
                }else{
                    jQuery(this).html(jQuery(data).html());
                    simplecmsloadBindInner('#'+jQuery(this).prop('id'));   
                }
            },
            error: function()
            {
                alert('forbidden');
            }
        });      
    });       
}


function simplecmsloadBind(classtobind){    
    jQuery(classtobind).bind('mouseenter', function(event){
        var $this =  jQuery(this);
        $this.data('simplecms-css-zindex', $this.css('zIndex'));
        $this.addClass('active')
            .css('zIndex', 9999);
    });
    
    jQuery(classtobind).bind('mouseleave', function(event){
        simplecmsDeactivateItem(jQuery(this));
    });    
    
    jQuery(classtobind).bind('dblclick', function(event){
        event.preventDefault();
        event.stopPropagation();
        
        var me = jQuery(this);
        simplecmsDeactivateItem(me);
        
        var href = me.children('a.simplecms-editlink').prop('href') ;        
        simplecmsAjaxing(href,me);
    });
}

function simplecmsDeactivateItem(item) {
    item.removeClass('active')
        .css('zIndex', item.data('simplecms-css-zindex'));
}