(function($){
	$(function(){

		jQuery(document).on('click', '.wc-action-button-duplicate-order', function(e){
			e.preventDefault();
			e.stopImmediatePropagation();
			alert('The order is duplicating');
			var href = $(this).attr('href');

			$.ajax({
				type:'POST',
				url:ajaxurl,
				data:'action=duplicate-order&order_id=' + getURLParameter(href, 'order_id'),
				success:function(results){
					alert ('The order is duplicated');
				}
			});
		});

		function getURLParameter(url, name) {
    		return (RegExp(name + '=' + '(.+?)(&|$)').exec(url)||[,null])[1];
		}
});
}) (jQuery);
