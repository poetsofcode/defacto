(function($){
    var ePrivacyAdminClass = function(){
        var root = this;
        this.vars = {};
        var construct = function(){
            
        };
        construct();
    };
    $(document).ready(function(){
        window.epac = new ePrivacyAdminClass();
        $(document).on('subform-row-add',function(e,row){
            switch($(row).data('base-name')) {
                case 'cookies':
                    console.log(row);
                    break;
            }
        });
    });
})(jQuery);
