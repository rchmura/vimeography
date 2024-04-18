// JavaScript Document
(function() {
    tinymce.create('tinymce.plugins.vimeography', {
        init : function(ed, url) {
            ed.addButton('vimeography', {
                title : 'Add Vimeography Gallery',
                image : url+'/mce-button.png',
                onclick : function() {
                     //ed.selection.setContent('[vimeography]' + ed.selection.getContent() + '[/vimeography]');
                     tb_show('Add Vimeography Gallery','#TB_inline?width=480&inlineId=select_vimeography_gallery',false);
 
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
    });
    tinymce.PluginManager.add('vimeography', tinymce.plugins.vimeography);
})();
