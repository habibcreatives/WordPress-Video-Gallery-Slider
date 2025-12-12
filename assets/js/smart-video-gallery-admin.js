(function ($) {
    $(function () {

        const table = $('#svgallery-table');
        if (!table.length) return;

        const tbody = table.find('tbody');
        const addBtn = $('#svgallery-add-row');
        const tpl = tbody.find('.svgallery-row-template');
        let index = tbody.find('tr').not('.svgallery-row-template').length;

        function bindRemove(scope) {
            scope.find('.svgallery-remove-row').on('click', function () {
                $(this).closest('tr').remove();
            });
        }

        bindRemove(tbody);

        addBtn.on('click', function () {
            const row = tpl.clone().removeClass('svgallery-row-template').show();
            row.html(row.html().replace(/INDEX/g, index));
            tbody.append(row);
            bindRemove(row);
            index++;
        });

        function openMedia(button, type) {
            const field = $(button).closest('td')
                .find(type === 'video' ? '.svgallery-video-field' : '.svgallery-poster-field');

            const frame = wp.media({
                title: 'Select',
                library: { type },
                button: { text: 'Use this' },
                multiple: false
            });

            frame.on('select', function () {
                field.val(frame.state().get('selection').first().toJSON().url);
            });

            frame.open();
        }

        tbody.on('click', '.svgallery-select-video', function (e) {
            e.preventDefault();
            openMedia(this, 'video');
        });

        tbody.on('click', '.svgallery-select-poster', function (e) {
            e.preventDefault();
            openMedia(this, 'image');
        });
    });
})(jQuery);
