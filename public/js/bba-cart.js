jQuery(function ($) {
    $(document).ready(function () {
        $(document).on('change', '#bba-residential', function() {
            updateGoodsCheck(wc_cart_params.ajax_url);
        });

        $(document).on('change', '#bba-tailgate', function() {
            updateGoodsCheck(wc_cart_params.ajax_url);
        });

        $(document).on('change', '#bba-checkout-residential', function() {
            updateGoodsCheck(wc_checkout_params.ajax_url);
        });

        $(document).on('change', '#bba-checkout-tailgate', function() {
            updateGoodsCheck(wc_checkout_params.ajax_url);
        });

        function updateGoodsCheck(url) {
            toggleLoadingIndicator(true);
            $.ajax({
                type: 'POST',
                url,
                data: {
                    action: 'update_cart',
                    residential: getResidentialValue(),
                    tailgate: getTailgateValue()
                },
                success: function (response) {
                    const element = $('#shipping_method');
                    element.empty();
                    response.data.forEach(method => {
                        const newElement = $(`
                        <li>
                            <input type="radio" name="shipping_method[0]" data-index="0" id="shipping_method_0_${method.shipping_id}" value="${method.shipping_id}" class="shipping_method" checked="checked"><label for="shipping_method_0_${method.shipping_id}">${method.label}: <span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>${method.cost}</bdi></span></label>
                        </li>
                    `);
                        element.append(newElement);
                    });
                    toggleLoadingIndicator(false);
                }
            });
        }

        function getResidentialValue() {
            return $('input[name="residential"]').is(':checked') ? true : false;
        }

        function getTailgateValue() {
            return $('input[name="tailgate"]').is(':checked') ? true : false;
        }
       
        function toggleLoadingIndicator(isShow) {
            if (isShow) {
                $('#bba-loading-overlay').show();
                return;
            }

            $('#bba-loading-overlay').hide();
        }
    });
});




