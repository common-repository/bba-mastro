jQuery(function($){
    const apiUrls = {
        'dev': 'https://api-dev.bbamastro.com',
        'staging': 'https://api-staging.bbamastro.com',
        'demo': 'https://api-demo.bbamastro.com',
        'prod': 'https://api.bbamastro.com',
        'azure': 'https://api-azure.bbamastro.com',
        'uk': 'https://api-uk.bbamastro.com',
        'us': 'https://api-us.bbamastro.com'
    };

    const authUrls = {
        'dev': 'https://auth-dev.bbamastro.com/oauth/token',
        'staging': 'https://auth-staging.bbamastro.com/oauth/token',
        'demo': 'https://auth-demo.bbamastro.com/oauth/token',
        'prod': 'https://auth.bbamastro.com/oauth/token',
        'azure': 'https://auth-azure.bbamastro.com/oauth/token',
        'uk': 'https://auth-uk.bbamastro.com/oauth/token',
        'us': 'https://auth-us.bbamastro.com/oauth/token'
    };
    
    const apiOptions = {
        placeholder: 'Choose an API URL',
        data: [
                { id: apiUrls.dev, text: apiUrls.dev }, 
                { id: apiUrls.staging, text: apiUrls.staging }, 
                { id: apiUrls.demo, text: apiUrls.demo }, 
                { id: apiUrls.prod, text: apiUrls.prod },
                { id: apiUrls.azure, text: apiUrls.azure },
                { id: apiUrls.uk, text: apiUrls.uk },
                { id: apiUrls.us, text: apiUrls.us }
            ]
    };

    var id_api_url_setting = ['#woocommerce_bbamastro_rules_api_url'];
    $.each(id_api_url_setting, (_index, selector) =>{
        var authUrl = '#woocommerce_bbamastro_rules_auth_url';
        $(selector).select2(apiOptions).on('select2:select', function(e){
            let data = e.params.data;
            switch(data.id) {
                case apiUrls.dev:
                    $(authUrl).val(authUrls.dev)
                    break;
                case apiUrls.staging: 
                    $(authUrl).val(authUrls.staging)
                    break; 
                case apiUrls.demo: 
                    $(authUrl).val(authUrls.demo)
                    break; 
                case apiUrls.prod: 
                    $(authUrl).val(authUrls.prod)
                    break;
                case apiUrls.azure: 
                    $(authUrl).val(authUrls.azure)
                    break;
                case apiUrls.uk: 
                    $(authUrl).val(authUrls.uk);
                    break;
                case apiUrls.us: 
                    $(authUrl).val(authUrls.us)
                    break;
            }
        });
        $(authUrl).prop('readonly', true);
    });

    var id_country_setting = ['#woocommerce_bbamastro_rules_warehouse_country'];
    var countryOptions = {
        placeholder: 'Choose a country',
        data: bbamastro.countries
    };
    $.each(id_country_setting, (_index, selector) => {
        $(selector).select2(countryOptions).on('change', function(e){
            // NEED TO LOAD THE COUNTRIES AND POSTCODES AVAILABLE IN THE SELECTED COUNTRY
        });
    });
});