require([

    "jquery",
    'mage/translate'

], 

function($){

    $(document).ready(function() {

    	$(document).ajaxComplete(function (event, xhr, settings) {
    	    if (settings.url.indexOf("customer/section/load/?sections=magepal") > 0) {
    	    	cartObj = xhr.responseJSON;
    	    	if(cartObj.cart.items.length > 0) {
					var productId = cartObj.cart.items[0].product_id;
					var removeId = cartObj.cart.items[0].item_id;  
					
					var boxes = $("input[name=free]:checked");
					$.each(boxes, function () {
						if($(this).val() == productId) {
							$(this).attr("removeId", removeId);
	            			$(this).removeAttr("disabled");
	            			$(this).next().text($.mage.__('Added'));
	            			$(this).parents(".ck-button").removeAttr("disabled");
						}
					});
    	    	}
    	    }
    	});
    	

        $('input[name=free]').on('change', function() {

            var id = $(this).val();

            var url = $(this).attr('url');

            var removeId = $(this).attr('removeId');

            var uenc = $(this).attr('uenc');
            
            var max = $(this).attr('max');


            if (this.checked) {
            	
            	var numberOfChecked = $('input:checkbox:checked').length;
            	if(numberOfChecked == max) {
            		$("input[name=free]:not(:checked)").each (function () {
            			$(this).attr("disabled", true);
                    	$(this).parents(".ck-button").attr("disabled", true);
            		});
            	}
        		
            	$(this).attr("disabled", true);
    			$(this).next().text($.mage.__('Adding...'));
            	$(this).parents(".ck-button").attr("disabled", true);

                AddToMagentoCart(url, id);

                //jQuery("#freesample").load(document.URL+' #freesample');

            }

            else{
            	       	
            	$(this).attr("disabled", true);
    			$(this).next().text($.mage.__('Removing...'));
            	$(this).parents(".ck-button").attr("disabled", true);

                RemoveToMagentoCart(uenc, removeId, id, max);
                
            }

        });
        

        function AddToMagentoCart(url, id) {

            var productId = id;

            var addToCartUrl = url;



            // add form key

            var formKey = $.cookie("form_key");

            var dataString = 'product='+productId+'&selected_configurable_option=&related_product=&form_key='+formKey+'&qty=1';



            // make POST call with the custom options as body element
            
            console.log("Procedo all'aggiunta di id: " + productId); // <--- set correct redirect link


            var request = $.ajax({

                url: addToCartUrl,

                method: "POST",

                processData: false,

                contentType: "application/x-www-form-urlencoded",

                data: dataString

            }).done(function( response ) { // redirect to cart if call was successful

                console.log("Aggiunto al carrello id: " + productId); // <--- set correct redirect link
                
                var startIdx = addToCartUrl.indexOf('uenc/') + 5;
                var endIdx = addToCartUrl.indexOf('/product');                
                var length = endIdx - startIdx;
                
                var uenc = addToCartUrl.substr(startIdx, length);
                
                console.log("Prendo uenc: " + uenc);
                
                
                $("input[name=free]:checked").each (function () {
        			if($(this).val() == productId) {
        				$(this).attr("uenc", uenc);
        			}
        		});
            	
                //window.location = window.location.href;

            }).fail(function( jqXHR, textStatus ) {

                console.log("Couldn't add product to cart. " + textStatus);

            });

        }

        

        function RemoveToMagentoCart(uenc, removeId, id, max) {

            var productId = removeId;

            var uenc = uenc;
            
            var prodCatId = id;

            var removeToCartUrl = window.location.href+"delete/";



            var formKey = $.cookie("form_key");

            var dataString = 'id='+productId+'&uenc='+uenc+'&form_key='+formKey;

            

            // make POST call with the custom options as body element
            console.log("Procedo alla rimozione di id: " + productId); // <--- set correct redirect link

            var request = $.ajax({

                url: removeToCartUrl,

                method: "POST",

                processData: false,

                contentType: "application/x-www-form-urlencoded",

                data: dataString

            }).done(function( response ) { // redirect to cart if call was successful

                console.log("Rimosso dal carrello id: " + productId);
                enableCheckbox(max);
                //window.location = window.location.href;

            }).fail(function( jqXHR, textStatus ) {

                console.log("Couldn't add product to cart. " + textStatus);
                enableCheckbox(max);

            });

        }
        
        function enableCheckbox(max) {
        	var numberOfChecked = $('input:checkbox:checked').length;
            if(numberOfChecked < max) {
        		$("input[name=free]:not(:checked)").each (function () {
        			$(this).removeAttr("disabled");
        			$(this).next().text($.mage.__('Add'));
        			$(this).parents(".ck-button").removeAttr("disabled");
        		});
        	}
        }

    });

});