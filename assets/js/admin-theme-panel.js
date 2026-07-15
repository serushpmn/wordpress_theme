(function ($) {
  'use strict';

  function initColorPickers($context) {
    $context.find('.almasland-color-picker').each(function () {
      const $input = $(this);
      if ($input.hasClass('wp-color-picker')) {
        return;
      }
      $input.wpColorPicker();
    });
  }

  function bindImageUpload() {
    $(document).on('click', '.almasland-upload-image', function (e) {
      e.preventDefault();
      const $field = $(this).closest('.almasland-field--image');
      const $input = $field.find('.almasland-image-id');
      const $preview = $field.find('.almasland-image-preview');

      const frame = wp.media({
        title: 'انتخاب تصویر',
        button: { text: 'استفاده از این تصویر' },
        multiple: false,
      });

      frame.on('select', function () {
        const attachment = frame.state().get('selection').first().toJSON();
        $input.val(attachment.id);
        $preview.empty().append($('<img>', { src: attachment.url, alt: '' }));
      });

      frame.open();
    });

    $(document).on('click', '.almasland-remove-image', function (e) {
      e.preventDefault();
      const $field = $(this).closest('.almasland-field--image');
      $field.find('.almasland-image-id').val('');
      $field.find('.almasland-image-preview').empty();
    });
  }

  function bindSortable() {
    const $sort = $('#almasland-section-sort');
    if ($sort.length) {
      $sort.sortable({ axis: 'y', handle: '.dashicons-menu' });
    }
  }

  function bindRepeater() {
    $(document).on('click', '.almasland-remove-row', function () {
      $(this).closest('.almasland-repeater-row').remove();
    });

    $(document).on('click', '.almasland-add-repeater', function () {
      const target = $(this).data('target');
      const $container = $('#almasland-repeater-' + target);
      const index = $container.find('.almasland-repeater-row').length;
      const $last = $container.find('.almasland-repeater-row').last();

      if (!$last.length) {
        return;
      }

      const $clone = $last.clone();
      $clone.attr('data-index', index);
      $clone.find('input, textarea, select').each(function () {
        const name = $(this).attr('name');
        if (name) {
          $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
        }
        if ($(this).is(':checkbox')) {
          $(this).prop('checked', false);
        } else if ($(this).hasClass('almasland-image-id')) {
          $(this).val('');
        } else if ($(this).hasClass('almasland-color-picker')) {
          $(this).val('#f7f9fc').removeClass('wp-color-picker');
          $(this).closest('.wp-picker-container').replaceWith($(this));
        } else {
          $(this).val('');
        }
      });
      $clone.find('.almasland-image-preview').empty();
      $container.append($clone);
      initColorPickers($clone);
    });
  }

  $(function () {
    initColorPickers($('.almasland-panel-wrap'));
    bindImageUpload();
    bindSortable();
    bindRepeater();
  });
})(jQuery);
