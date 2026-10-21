/**
 * Shows the remaining characters below textareas with the class "form-control-bstextcounter".
 * The limit is read from the data-maximum-chars attribute.
 */
(function () {
    'use strict';

    var warningLimit = 10;

    function init(textarea) {
        var limit = parseInt(textarea.dataset.maximumChars, 10) || 200;
        var status = document.createElement('span');
        status.className = 'bstextcounter label label-primary';
        status.setAttribute('aria-live', 'polite');
        var wrapper = document.createElement('div');
        wrapper.appendChild(status);
        textarea.insertAdjacentElement('afterend', wrapper);

        function check() {
            if (textarea.value.length > limit) {
                textarea.value = textarea.value.substring(0, limit);
            }
            var remaining = limit - textarea.value.length;
            status.textContent = remaining + '/' + limit;
            status.classList.toggle('label-danger', remaining <= warningLimit);
            status.classList.toggle('label-primary', remaining > warningLimit);
        }

        textarea.addEventListener('input', check);
        check();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.form-control-bstextcounter').forEach(init);
    });
})();
