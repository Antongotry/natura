/**
 * Admin scripts for home content management.
 *
 * @package Natura
 */

(function($) {
	'use strict';

	let mediaUploader;

	/**
	 * Initialize admin page.
	 */
	function init() {
		// Add new item
		$(document).on('click', '.natura-add-item', function(e) {
			e.preventDefault();
			const carousel = $(this).data('carousel');
			addNewItem(carousel);
		});

		// Remove item
		$(document).on('click', '.natura-remove-item', function(e) {
			e.preventDefault();
			if (confirm(naturaHomeContent.i18n.removeConfirm)) {
				$(this).closest('.natura-carousel-item').remove();
				updateItemNumbers();
				updateHiddenFields();
			}
		});

		// Image upload
		$(document).on('click', '.natura-image-upload', function(e) {
			e.preventDefault();
			const button = $(this);
			const field = button.closest('.natura-image-field');
			const input = field.find('.natura-image-id');
			const preview = field.find('.natura-image-preview');
			const removeBtn = field.find('.natura-image-remove');

			// Store current field reference
			const currentField = {
				input: input,
				preview: preview,
				removeBtn: removeBtn
			};

			// If media uploader exists, remove previous handlers
			if (mediaUploader) {
				mediaUploader.off('select');
			} else {
				mediaUploader = wp.media({
					title: 'Оберіть зображення',
					button: {
						text: 'Використати зображення'
					},
					multiple: false
				});
			}

			// Set current selection if image already exists
			if (input.val()) {
				mediaUploader.state().get('selection').reset();
				mediaUploader.state().get('selection').add(wp.media.attachment(input.val()));
			}

			mediaUploader.on('select', function() {
				const attachment = mediaUploader.state().get('selection').first().toJSON();
				currentField.input.val(attachment.id);
				currentField.preview.html('<img src="' + attachment.url + '" alt="">');
				currentField.removeBtn.removeClass('is-hidden');
				updateHiddenFields();
			});

			mediaUploader.open();
		});

		// Hover image upload
		$(document).on('click', '.natura-image-hover-upload', function(e) {
			e.preventDefault();
			const button = $(this);
			const field = button.closest('.natura-image-field');
			const input = field.find('.natura-image-hover-id');
			const preview = field.find('.natura-image-hover-preview');
			const removeBtn = field.find('.natura-image-hover-remove');

			// Store current field reference
			const currentField = {
				input: input,
				preview: preview,
				removeBtn: removeBtn
			};

			// If media uploader exists, remove previous handlers
			if (mediaUploader) {
				mediaUploader.off('select');
			} else {
				mediaUploader = wp.media({
					title: 'Оберіть зображення',
					button: {
						text: 'Використати зображення'
					},
					multiple: false
				});
			}

			// Set current selection if image already exists
			if (input.val()) {
				mediaUploader.state().get('selection').reset();
				mediaUploader.state().get('selection').add(wp.media.attachment(input.val()));
			}

			mediaUploader.on('select', function() {
				const attachment = mediaUploader.state().get('selection').first().toJSON();
				currentField.input.val(attachment.id);
				currentField.preview.html('<img src="' + attachment.url + '" alt="">');
				currentField.removeBtn.removeClass('is-hidden');
				updateHiddenFields();
			});

			mediaUploader.open();
		});

		// Remove image
		$(document).on('click', '.natura-image-remove', function(e) {
			e.preventDefault();
			const field = $(this).closest('.natura-image-field');
			const input = field.find('.natura-image-id');
			const preview = field.find('.natura-image-preview');
			const removeBtn = $(this);

			input.val('');
			preview.html('');
			removeBtn.addClass('is-hidden');
			updateHiddenFields();
		});

		// Remove hover image
		$(document).on('click', '.natura-image-hover-remove', function(e) {
			e.preventDefault();
			const field = $(this).closest('.natura-image-field');
			const input = field.find('.natura-image-hover-id');
			const preview = field.find('.natura-image-hover-preview');
			const removeBtn = $(this);

			input.val('');
			preview.html('');
			removeBtn.addClass('is-hidden');
			updateHiddenFields();
		});

		// Make items sortable
		$('.natura-carousel-items').sortable({
			handle: '.natura-carousel-item-header',
			update: function() {
				updateItemNumbers();
				updateHiddenFields();
			}
		});

		// Update hidden fields on any change
		$(document).on('change', '.natura-image-id, .natura-image-hover-id', function() {
			updateHiddenFields();
		});
	}

	/**
	 * Add new carousel item.
	 *
	 * @param {string} carousel Carousel type (top/bottom).
	 */
	function addNewItem(carousel) {
		const template = $('#natura-carousel-item-template').html();
		const container = $('.natura-carousel-items[data-carousel="' + carousel + '"]');
		const item = $(template);
		item.attr('data-carousel', carousel);
		item.attr('data-index', container.children().length);
		container.append(item);
		updateItemNumbers();
		updateHiddenFields();
	}

	/**
	 * Update item numbers.
	 */
	function updateItemNumbers() {
		$('.natura-carousel-items').each(function() {
			$(this).find('.natura-carousel-item').each(function(index) {
				$(this).find('.natura-item-number').text(index + 1);
				$(this).attr('data-index', index);
			});
		});
	}

	/**
	 * Update hidden form fields with current data.
	 */
	function updateHiddenFields() {
		const topItems = [];
		const bottomItems = [];

		$('.natura-carousel-item[data-carousel="top"]').each(function() {
			const iconId = $(this).find('.natura-image-id').val();
			const iconHoverId = $(this).find('.natura-image-hover-id').val();

			if (iconId || iconHoverId) {
				topItems.push({
					icon_id: parseInt(iconId) || 0,
					icon_hover_id: parseInt(iconHoverId) || 0
				});
			}
		});

		$('.natura-carousel-item[data-carousel="bottom"]').each(function() {
			const iconId = $(this).find('.natura-image-id').val();
			const iconHoverId = $(this).find('.natura-image-hover-id').val();

			if (iconId || iconHoverId) {
				bottomItems.push({
					icon_id: parseInt(iconId) || 0,
					icon_hover_id: parseInt(iconHoverId) || 0
				});
			}
		});

		$('#trusted-top-data').val(JSON.stringify(topItems));
		$('#trusted-bottom-data').val(JSON.stringify(bottomItems));
	}

	// Initialize on document ready
	$(document).ready(init);

})(jQuery);
