$( document ).ready( function() {
    simplecmsloadBind('div.simplecms-edit');
    simplecmsloadBindInner('div.simplecms-edit');
} );

function simplecmsAjaxing(href, me){
    jQuery.ajax({
        url: href,
        context: me,
        success: function(html) {   
            var replacmentid = jQuery(this).attr('id');
            if(jQuery(this).hasClass('simplecms-add')){
                replacmentid = jQuery(this).parent().attr('id')
            }
            jQuery('<div id="simplecms-dialog" name="'+replacmentid+'">'+html+'</div>').dialog({
                dialogClass: 'simplecms-jquery-dialog',
                height: 'auto',
                width: 'auto',
                modal: true,
                resizable: false,
                title: 'SimpleCMS',
                buttons: {
                    "Save": function() {
                        var parrent = this;
                        jQuery('#simplecms-dialog form').ajaxForm({
                            success: function(data, statusText, xhr, form){
                                source = jQuery(parrent).attr('name');
                                if(source != jQuery(data).attr('id') ){
                                    jQuery(data).dialog({
                                        dialogClass: 'simplecms-jquery-dialog',
                                        title: 'can\'t save'
                                    });
                                }else{
                                    jQuery('.'+source).html(jQuery(data).html());
                                    if(jQuery('#'+source).hasClass('simplecms-edit-collection')){
                                        simplecmsloadBind('.'+source+' div.simplecms-edit');  
                                        simplecmsloadBindInner('.'+source);    
                                    }else{
                                        simplecmsloadBindInner('.'+source);
                                    }
                                }
                            },
                            error: function(html){
                                alert('can\'t save');
                            }
                        }).submit();
                        jQuery('#simplecms-dialog').remove();
                    }
                }
            });
            $('#simplecms-dialog form.simplecms-html textarea').tinymce(simple_cms_wysiwyg_config);   
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
        var href = jQuery(this).attr('href') ;        
        simplecmsAjaxing(href,me);        
    });    
    jQuery(parent+' a.simplecms-deletelink').bind('click', function(event){
        event.preventDefault();
        event.stopPropagation();
        if(false==confirm(  "delete ?")){
            return false;
        }
        var me = jQuery(this).parent('div');
        var href = jQuery(this).attr('href') ;        
        jQuery.ajax({
            url: href,
            context: me,
            success: function(data) {
                if(jQuery(this).parent().hasClass('simplecms-edit-collection')){
                   jQuery(this).hide();
                }else{
                    jQuery(this).html(jQuery(data).html());
                    simplecmsloadBindInner('#'+jQuery(this).attr('id'));   
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
        jQuery(this).addClass('active');
    });
    jQuery(classtobind).bind('mouseleave', function(event){
        jQuery(this).removeClass('active');
    });    
    jQuery(classtobind).bind('dblclick', function(event){
        event.preventDefault();
        event.stopPropagation();
        
        var me = jQuery(this);
        var href = me.children('a.simplecms-editlink').attr('href') ;        
        simplecmsAjaxing(href,me);
    });
}