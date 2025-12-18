(function ($) {
	$(function () {
		const defaults = {
			title: 'Оберіть зображення',
			button: 'Використати',
		};
		const settings = window.naturaCategoryCover ? { ...defaults, ...window.naturaCategoryCover } : defaults;

		const frameKey = 'naturaCategoryCoverFrame';

		function openMediaFrame($field) {
			let frame = $field.data(frameKey);

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: settings.title,
				button: {
					text: settings.button,
				},
				library: {
					type: ['image'],
				},
				multiple: false,
			});

			frame.on('select', () => {
				const attachment = frame.state().get('selection').first().toJSON();
				setImage($field, attachment.id, attachment.url);
			});

			$field.data(frameKey, frame);
			frame.open();
		}

		function setImage($field, id, url) {
			$field.find('input[type="hidden"]').val(id);
			const $preview = $field.find('.natura-category-cover-preview');
			$preview.empty();

			if (url) {
				$preview.append($('<img>').attr('src', url));
				$field.find('.natura-category-cover-remove').removeClass('is-hidden');
			} else {
				$field.find('.natura-category-cover-remove').addClass('is-hidden');
			}
		}

		$(document).on('click', '.natura-category-cover-upload', function (event) {
			event.preventDefault();
			const $field = $(this).closest('.natura-category-cover-field');
			openMediaFrame($field);
		});

		$(document).on('click', '.natura-category-cover-remove', function (event) {
			event.preventDefault();
			const $field = $(this).closest('.natura-category-cover-field');
			setImage($field, '', '');
		});
	});
})(jQuery);

