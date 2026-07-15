(function ($) {
  'use strict';

  function initColorPicker($context) {
    $context.find('[data-color-picker]').each(function () {
      const $input = $(this);

      if ($input.hasClass('wp-color-picker')) {
        return;
      }

      $input.wpColorPicker({
        change: function () {
          $input.trigger('change');
        },
      });
    });
  }

  function reindexCustomBadges() {
    $('[data-custom-badges] [data-custom-badge-row]').each(function (index) {
      $(this)
        .find('.almasland-custom-badge-row__text')
        .attr('name', '_almas_custom_badges[' + index + '][text]');
      $(this)
        .find('.almasland-custom-badge-row__color')
        .attr('name', '_almas_custom_badges[' + index + '][color]');
    });
  }

  function addCustomBadge(text, color) {
    const template = document.getElementById('almasland-custom-badge-template');
    if (!template || !template.content) {
      return;
    }

    const $row = $(template.content.firstElementChild.cloneNode(true));
    $row.find('.almasland-custom-badge-row__text').val(text);
    $row.find('.almasland-custom-badge-row__color').val(color || '#1abf77');

    $('[data-custom-badges]').append($row);
    initColorPicker($row);
    reindexCustomBadges();
  }

  $(function () {
    initColorPicker($('.almasland-product-badges-meta'));

    $(document).on('click', '[data-add-badge]', function () {
      const text = $('#almasland-new-badge-text').val().trim();
      const color = $('#almasland-new-badge-color').val() || '#1abf77';

      if (!text) {
        $('#almasland-new-badge-text').trigger('focus');
        return;
      }

      addCustomBadge(text, color);
      $('#almasland-new-badge-text').val('');
    });

    $(document).on('click', '[data-remove-badge]', function () {
      $(this).closest('[data-custom-badge-row]').remove();
      reindexCustomBadges();
    });
  });
})(jQuery);
