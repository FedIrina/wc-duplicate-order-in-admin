jQuery( function( $ ) {
		const { __, _x, _n, _nx } = wp.i18n;	
		thresholds_add();
		$('.wholesale-prices-options-group input.wc_input_price').on('input', function(e) {
			elem_id = $(this).attr('id');
			$('span#'+elem_id).html('');
		});
		$('.wholesale-prices-options-group input.wc_input_price').on('blur', function(e) {
			elem_id = $(this).attr('id');
			margins_recalc();
		});		
		$("#calc_wholesale_prices").on("click", function(e){
			e.preventDefault();
			/*e.stopImmediatePropagation();*/
			var regular_price = $('#general_product_data #_regular_price').val();
			if (!regular_price) {
				alert(__("Нельзя рассчитать отпускные цены, пока не указана закупочная цена !","wc-wholesale-prices-calculator"));
				return;
			}
			var wholesale_prices = '';
			$('.wholesale-prices-options-group input.wc_input_price').each(function() {
				var price_name = $(this).attr("name");
				var price_val = $(this).val();
				/*if (empty(price_val)) {
					price_val = '0';
				}*/
				wholesale_prices = wholesale_prices + '&'+ price_name + '=' + price_val;
			});
			$.ajax({
				type:'POST',
				url:ajaxurl,
				data:'action=wholesale_prices_calculate&regular_price='+regular_price,
				success:function(results){
					//location.reload(true);
					prices = $.parseJSON( results );
					$.each(prices,function(index,item_val){
						$('.wholesale-prices-options-group input#'+index+'_wholesale_price').val(item_val);
					});				
					margins_recalc();
				}
			});
		});
		function thresholds_add(){
			$.ajax({
				type:'POST',
				url:ajaxurl,
				data:'action=wholesale_ratio_threshold',
				success:function(results){
					thresholds = $.parseJSON( results );
					$.each(thresholds,function(index,item_val){
						$('input#'+index+'_wholesale_price').attr({'data-extrathreshold':item_val});
					});
					margins_recalc();									
				}
			});
		}
		function margins_recalc(){
			$('.wholesale-prices-options-group input.wholesale_price').each(function(index,value){
				elem_id = $(this).attr("id");
				if ($(this).val()>0) {
					procent = ($(this).val() * 100 / $('#_regular_price').val() - 100).toFixed(0);
					if ($('span#'+elem_id).length==0) {
						$('input#'+elem_id).after('<span id="'+elem_id+'"></span>');
					}
					$('span#'+elem_id).html(procent+'%');
					if (procent<($(this).data('extrathreshold')*100)) {
						$(this).addClass('attention');
						$('span#'+elem_id).addClass('attention');
					} else {
						$(this).removeClass('attention');
						$('span#'+elem_id).removeClass('attention');						
					}				
				}
			});
		}
});
