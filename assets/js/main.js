(function($) {
    $(document).ready(function() {
    	// Get ajax URL
    	var ajax_url = sale_post_variables.ajaxUrl;

    	$(".wpapijson-import_botao").on("click", function(){
    			$.get( ajax_url, {
					action: 'import_posts'
				},
				function(data){
					$(".wpapijson-import_wraper_posts_imports").fadeIn(300);
					$(".wpapijson-import_posts_import").html( data.message );
				}, "json");
    	});


    });
})(jQuery);
