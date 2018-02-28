/**
 * fancybox initialization
 * options for
 * - del button
 */

jQuery(document).ready(function($) {
	$("a.del[rel*=fancybox]").fancybox({
		cyclic: false,
		showNavArrows: false,
		titleShow: false,
		width: 320,
		height: 200,
		centerOnScroll: true,
		overlayOpacity: 0.5
	});

	$("a.edit-portrait[rel*=fancybox]").fancybox({
		cyclic: false,
		showNavArrows: false,
		titleShow: false,
		width: 450,
		height: 550,
		centerOnScroll: true
	});

	$("a.edit-landscape[rel*=fancybox]").fancybox({
		cyclic: false,
		showNavArrows: false,
		titleShow: false,
		width: 550,
		height: 400,
		centerOnScroll: true,
		overlayOpacity: 0.5
	});

});
