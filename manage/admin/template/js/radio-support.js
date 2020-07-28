$(document).ready(function () {
    var elem = document.querySelector('.js-switch');
    var switchery = new Switchery(elem, {
        color: '#1AB394'
    });

    var elem_2 = document.querySelector('.js-switch_2');
    var switchery_2 = new Switchery(elem_2, {
        color: '#ED5565'
    });

    var elem_3 = document.querySelector('.js-switch_3');
    var switchery_3 = new Switchery(elem_3, {
        color: '#1AB394'
    });

});
var config = {
    '.chosen-select': {},
    '.chosen-select-deselect': {
        allow_single_deselect: true
    },
    '.chosen-select-no-single': {
        disable_search_threshold: 10
    },
    '.chosen-select-no-results': {
        no_results_text: 'Oops, nothing found!'
    },
    '.chosen-select-width': {
        width: "100%"
    }
}
for (var selector in config) {
    $(selector).chosen(config[selector]);
}