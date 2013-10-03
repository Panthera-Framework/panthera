/*
 * jQuery searchBarDropdown: A simple searchBarDropdown plugin
 *
 * Inspired by Bootstrap: http://twitter.github.com/bootstrap/javascript.html#searchBarDropdowns
 *
 * Copyright 2013 Cory LaViska for A Beautiful Site, LLC. (http://abeautifulsite.net/)
 *
 * Dual licensed under the MIT / GPL Version 2 licenses
 *
*/
if(jQuery) (function($) {
	
	$.extend($.fn, {
		searchBarDropdown: function(method, data) {
			
			switch( method ) {
				case 'hide':
					hide();
					return $(this);
				case 'attach':
					return $(this).attr('data-searchBarDropdown', data);
				case 'detach':
					hide();
					return $(this).removeAttr('data-searchBarDropdown');
				case 'disable':
					return $(this).addClass('searchBarDropdown-disabled');
				case 'enable':
					hide();
					return $(this).removeClass('searchBarDropdown-disabled');
			}
			
		}
	});
	
	function show(event) {
		
		var trigger = $(this),
			searchBarDropdown = $(trigger.attr('data-searchBarDropdown')),
			isOpen = trigger.hasClass('searchBarDropdown-open');
		
		// In some cases we don't want to show it
		if( $(event.target).hasClass('searchBarDropdown-ignore') ) return;
		
		event.preventDefault();
		event.stopPropagation();
		hide();
		
		if( isOpen || trigger.hasClass('searchBarDropdown-disabled') ) return;
		
		// Show it
		trigger.addClass('searchBarDropdown-open');
		searchBarDropdown
			.data('searchBarDropdown-trigger', trigger)
			.show();
			
		// Position it
		position();
		
		// Trigger the show callback
		searchBarDropdown
			.trigger('show', {
				searchBarDropdown: searchBarDropdown,
				trigger: trigger
			});
		
	}
	
	function hide(event) {
		
		// In some cases we don't hide them
		var targetGroup = event ? $(event.target).parents().addBack() : null;
		
		// Are we clicking anywhere in a searchBarDropdown?
		if( targetGroup && targetGroup.is('.searchBarDropdown') ) {
			// Is it a searchBarDropdown menu?
			if( targetGroup.is('.searchBarDropdown-menu') ) {
				// Did we click on an option? If so close it.
				if( !targetGroup.is('A') ) return;
			} else {
				// Nope, it's a panel. Leave it open.
				return;
			}
		}
		
		// Hide any searchBarDropdown that may be showing
		$(document).find('.searchBarDropdown:visible').each( function() {
			var searchBarDropdown = $(this);
			searchBarDropdown
				.hide()
				.removeData('searchBarDropdown-trigger')
				.trigger('hide', { searchBarDropdown: searchBarDropdown });
		});
		
		// Remove all searchBarDropdown-open classes
		$(document).find('.searchBarDropdown-open').removeClass('searchBarDropdown-open');
		
	}
	
	function position() {
		
		var searchBarDropdown = $('.searchBarDropdown:visible').eq(0),
			trigger = searchBarDropdown.data('searchBarDropdown-trigger'),
			hOffset = trigger ? parseInt(trigger.attr('data-horizontal-offset') || 0, 10) : null,
			vOffset = trigger ? parseInt(trigger.attr('data-vertical-offset') || 0, 10) : null;
		
		if( searchBarDropdown.length === 0 || !trigger ) return;
		
		// Position the searchBarDropdown relative-to-parent...
		if( searchBarDropdown.hasClass('searchBarDropdown-relative') ) {
			searchBarDropdown.css({
				left: searchBarDropdown.hasClass('searchBarDropdown-anchor-right') ?
					trigger.position().left - (searchBarDropdown.outerWidth(true) - trigger.outerWidth(true)) - parseInt(trigger.css('margin-right')) + hOffset :
					trigger.position().left + parseInt(trigger.css('margin-left')) + hOffset,
				top: trigger.position().top + trigger.outerHeight(true) - parseInt(trigger.css('margin-top')) + vOffset
			});
		} else {
			// ...or relative to document
			searchBarDropdown.css({
				left: searchBarDropdown.hasClass('searchBarDropdown-anchor-right') ? 
					trigger.offset().left - (searchBarDropdown.outerWidth() - trigger.outerWidth()) + hOffset : trigger.offset().left + hOffset,
				top: trigger.offset().top + trigger.outerHeight() + vOffset
			});
		}
	}
	
	$(document).on('click.searchBarDropdown', '[data-searchBarDropdown]', show);
	$(document).on('click.searchBarDropdown', hide);
	$(window).on('resize', position);
	
})(jQuery);
