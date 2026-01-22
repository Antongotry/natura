/**
 * Admin scripts for home content management.
 *
 * @package Natura
 */

(function($) {
	'use strict';

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
			
			// Check if wp.media is available
			if (typeof wp === 'undefined' || !wp.media) {
				alert('Медіа-бібліотека WordPress не завантажена. Перезавантажте сторінку.');
				return;
			}

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

			// Create new media uploader instance for each click to avoid conflicts
			const uploader = wp.media({
				title: 'Оберіть зображення',
				button: {
					text: 'Використати зображення'
				},
				library: {
					type: 'image'
				},
				multiple: false
			});

			// Set current selection if image already exists
			if (input.val()) {
				uploader.on('open', function() {
					const selection = uploader.state().get('selection');
					const attachment = wp.media.attachment(input.val());
					attachment.fetch();
					selection.reset([attachment]);
				});
			}

			// Handle selection
			uploader.on('select', function() {
				const attachment = uploader.state().get('selection').first().toJSON();
				currentField.input.val(attachment.id);
				currentField.preview.html('<img src="' + attachment.url + '" alt="" style="max-width: 100%; height: auto;">');
				currentField.removeBtn.removeClass('is-hidden');
				updateHiddenFields();
			});

			uploader.open();
		});

		// Hover image upload
		$(document).on('click', '.natura-image-hover-upload', function(e) {
			e.preventDefault();
			
			// Check if wp.media is available
			if (typeof wp === 'undefined' || !wp.media) {
				alert('Медіа-бібліотека WordPress не завантажена. Перезавантажте сторінку.');
				return;
			}

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

			// Create new media uploader instance for each click to avoid conflicts
			const uploader = wp.media({
				title: 'Оберіть зображення',
				button: {
					text: 'Використати зображення'
				},
				library: {
					type: 'image'
				},
				multiple: false
			});

			// Set current selection if image already exists
			if (input.val()) {
				uploader.on('open', function() {
					const selection = uploader.state().get('selection');
					const attachment = wp.media.attachment(input.val());
					attachment.fetch();
					selection.reset([attachment]);
				});
			}

			// Handle selection
			uploader.on('select', function() {
				const attachment = uploader.state().get('selection').first().toJSON();
				currentField.input.val(attachment.id);
				currentField.preview.html('<img src="' + attachment.url + '" alt="" style="max-width: 100%; height: auto;">');
				currentField.removeBtn.removeClass('is-hidden');
				updateHiddenFields();
			});

			uploader.open();
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

		// Update hidden fields before form submission
		$('#natura-trusted-form').on('submit', function(e) {
			// Update fields immediately before submit
			updateHiddenFields();
			
			// Verify fields were updated
			const topData = $('#trusted-top-data').val();
			const bottomData = $('#trusted-bottom-data').val();
			
			if (typeof console !== 'undefined' && console.log) {
				console.log('Natura: Form submitting with data', {
					topDataLength: topData ? topData.length : 0,
					bottomDataLength: bottomData ? bottomData.length : 0,
					topData: topData ? topData.substring(0, 150) : 'empty',
					bottomData: bottomData ? bottomData.substring(0, 150) : 'empty'
				});
			}
			
			// Allow form to submit normally
			return true;
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

		// Collect top carousel items
		$('.natura-carousel-item[data-carousel="top"]').each(function() {
			const iconId = $(this).find('.natura-image-id').val();
			const iconHoverId = $(this).find('.natura-image-hover-id').val();

			// Include item even if only one image is set
			if (iconId || iconHoverId) {
				topItems.push({
					icon_id: iconId ? parseInt(iconId) : 0,
					icon_hover_id: iconHoverId ? parseInt(iconHoverId) : 0
				});
			}
		});

		// Collect bottom carousel items
		$('.natura-carousel-item[data-carousel="bottom"]').each(function() {
			const iconId = $(this).find('.natura-image-id').val();
			const iconHoverId = $(this).find('.natura-image-hover-id').val();

			// Include item even if only one image is set
			if (iconId || iconHoverId) {
				bottomItems.push({
					icon_id: iconId ? parseInt(iconId) : 0,
					icon_hover_id: iconHoverId ? parseInt(iconHoverId) : 0
				});
			}
		});

		// Update hidden fields with JSON data
		try {
			const topData = JSON.stringify(topItems);
			const bottomData = JSON.stringify(bottomItems);
			
			const topField = $('#trusted-top-data');
			const bottomField = $('#trusted-bottom-data');
			
			if (topField.length) {
				topField.val(topData);
			}
			if (bottomField.length) {
				bottomField.val(bottomData);
			}
			
			// Debug output
			if (typeof console !== 'undefined' && console.log) {
				console.log('Natura: Updated hidden fields', {
					topCount: topItems.length,
					bottomCount: bottomItems.length,
					topItems: topItems,
					bottomItems: bottomItems
				});
			}
		} catch (e) {
			console.error('Error updating hidden fields:', e);
			alert('Помилка при підготовці даних для збереження: ' + e.message);
		}
	}

	// Initialize on document ready
	$(document).ready(init);

})(jQuery);
