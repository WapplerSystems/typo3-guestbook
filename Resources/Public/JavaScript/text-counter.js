/**
 * Character counter for the guestbook message field (no dependencies).
 * Fields: .form-control-bstextcounter[data-maximum-chars]
 */
(function () {
    'use strict';

    var WARNING_LIMIT = 10;

    function init(field) {
        var limit = parseInt(field.getAttribute('data-maximum-chars'), 10);
        if (!limit) {
            return;
        }
        var status = document.createElement('span');
        status.className = 'bstextcounter label label-primary';
        status.setAttribute('aria-live', 'polite');
        var wrapper = document.createElement('div');
        wrapper.appendChild(status);
        field.parentNode.insertBefore(wrapper, field.nextSibling);

        function update() {
            if (field.value.length > limit) {
                field.value = field.value.substring(0, limit);
            }
            var remaining = limit - field.value.length;
            status.textContent = remaining + '/' + limit;
            status.classList.toggle('label-danger', remaining <= WARNING_LIMIT);
            status.classList.toggle('label-primary', remaining > WARNING_LIMIT);
        }

        field.addEventListener('input', update);
        update();
    }

    document.querySelectorAll('.form-control-bstextcounter[data-maximum-chars]').forEach(init);
})();
