/**
*  @author    Manuel José Pulgar Anguita for Affilired
*  @copyright Affilired
*/
jQuery(document).ready(function(){

	$("#merchant_id").change( function(event){

		var store = $('#affilired_shop_select').val();
		if( store == 0){
			// all the stores are selected
			$(".hidden_merchant_id").each( function(){			
				$(this).val($("#merchant_id").val());		
			});
		}
		else {
			$('#merchant_id_'+store).val($("#merchant_id").val());
		}
	});

	$("#changeStoreButton").on("click",function(){

		var store = $('#affilired_shop_select').val();
		if( store == 0){
			// all the stores are selected
			var merchant_value = "";
			var firstShop = true;
			var value_changed = false;

			$(".hidden_merchant_id").each( function(){			
				if (firstShop == true) {
					firstShop = false;
					merchant_value = $(this).val();
				}
				else {
					if (value_changed == false) {
						var new_value = $(this).val();
						if (new_value != merchant_value) {
							value_changed = true;
							merchant_value = "";
						}
					}
				}
			});

			$("#merchant_id").val(merchant_value);
		}
		else {
			$("#merchant_id").val($('#merchant_id_'+store).val());
		}

		$('#merchant_id_'+store).val($("#merchant_id").val());

	});
})