var animate_duration = 1250;
var animation = 'fade';

$(document).ready(function() {
	$('.tooltipper').tooltipster({
		theme: 'tooltipster-borderless',
		animation: animation,
		animationDuration: animate_duration,
		arrow: true,
		contentAsHTML: true,
		maxWidth: 500,
	});

	initAll();
});

function initAll() {
	$('.dt-tooltipper.tooltipstered').tooltipster('destroy');
	$('.dt-tooltipper').tooltipster({
		theme: 'tooltipster-borderless',
		animation: animation,
		animationDuration: animate_duration,
		arrow: true,
		contentAsHTML: true,
		maxWidth: 500,
	});

	$('.tooltipper-ajax.tooltipstered').tooltipster('destroy');

	$('.tooltipper-ajax').tooltipster({
        trigger: 'custom',
        triggerOpen: {
            mouseenter: true,
            touchstart: true
        },
        triggerClose: {
            mouseleave: true,
            originClick: true,
            click: true,
            scroll: true,
            tap: true,
            touchLeave: true
        },
		theme: ['tooltipster-borderless', 'tooltipster-custom'],
		contentAsHTML: true,
        interactive: true,
		animation: animation,
		animationDuration: animate_duration,
		updateAnimation: animation,
		arrow: true,
        minWidth: 250,
		content: 'patience, grasshopper...',
		functionBefore: function(instance, helper) {
			var $origin = $(helper.origin);
			if ($origin.data('loaded') !== true) {
				$.post('../ajax/ajax_tooltips.php', {csrf_token:csrf_token}, function(data) {
					if(instance.content() === '') return false;
					instance.content(data);
					$origin.data('loaded', true);
				});
			}
		}
	});
}