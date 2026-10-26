import jQuery from 'jquery';

class SiteInteraction {

    constructor() {


        console.log("Site Interaction Loading");

        jQuery(window).on("scroll", function(e) {
            if (jQuery("body").scrollTop() > 103) {
                jQuery("#bodyWrap").addClass("fixedHeader");
            } else {
                jQuery("#bodyWrap").removeClass("fixedHeader");
            }

        });

        //Navbar top menu
        jQuery(".mainSidenav .toggleSettings").on('click', function(event) {
            event.preventDefault();
            jQuery(".mainSidenav .memberAccountLinks").toggleClass('open');
        });


        //Tooltips
        jQuery('[data-toggle=tooltip]').tooltip({});

        //Fancy dropdown
        jQuery(".js-advanced-dropdown").select2({dropdownAutoWidth:false});


        //Main side nav - mobile view
        jQuery(".menuToggleButton").on('click', function() {
            jQuery("body").addClass("menuOpen");
            jQuery(".modalMask").addClass("display");
        });


        //Modal window background mask
        jQuery(".modalMask").on('click', function() {
            jQuery(".modalMask").removeClass("display");
            jQuery("body").removeClass("menuOpen");
        });


        //Change sub amount button
        jQuery('.js-show-alter-subscription-amount').click(function(event) {
            event.preventDefault();
            jQuery('.js-alter-subscription-amount-form').removeClass('hidden');
            jQuery(this).hide();
        });

        console.log("Site Interaction Loaded");

    }

}

export default SiteInteraction;
