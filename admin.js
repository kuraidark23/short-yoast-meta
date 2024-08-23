jQuery(document).ready(function($) {
    $('#short_yoast_meta_options\\[debug\\]').on('change', function() {
        if ($(this).is(':checked')) {
            console.log('Short Yoast Meta: Debug mode activated');
        } else {
            console.log('Short Yoast Meta: Debug mode deactivated');
        }
    });
});