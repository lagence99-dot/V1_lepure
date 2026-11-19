/* Sélecteur d'image de la médiathèque pour les réglages du thème. */
jQuery(function ($) {
	function refresh(field) {
		var url = field.find('.epure-img').val();
		field.find('.epure-preview').attr('src', url).toggle(!!url);
		field.find('.epure-clear').toggle(!!url);
	}
	$(document).on('click', '.epure-pick', function (e) {
		e.preventDefault();
		var field = $(this).closest('.epure-imgfield');
		var frame = wp.media({ title: 'Choisir une image', button: { text: 'Utiliser cette image' }, multiple: false, library: { type: 'image' } });
		frame.on('select', function () {
			field.find('.epure-img').val(frame.state().get('selection').first().toJSON().url);
			refresh(field);
		});
		frame.open();
	});
	$(document).on('click', '.epure-clear', function (e) {
		e.preventDefault();
		var field = $(this).closest('.epure-imgfield');
		field.find('.epure-img').val('');
		refresh(field);
	});
	$(document).on('change', '.epure-img', function () {
		refresh($(this).closest('.epure-imgfield'));
	});
});
