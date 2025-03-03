document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('form-items')) {
        var el = document.getElementById('form-items');
        var sortable = new Sortable(el, {
            handle: '.fa-bars',
            animation: 150,
            onUpdate: function () {
                document.getElementById('form-items-order').value = sortable.toArray().join('|');
            },
        });
    }

    if (document.querySelector('.color-picker')) {
        jQuery(".color-picker").wpColorPicker();
    }



    if (document.querySelector('.property-sidebar-widgets')) {
        let dragSrcEl = null;

        function handleDragStart(e) {
            this.style.opacity = '0.4';  // this / e.target is the source node.
            dragSrcEl = this;

            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
        }

        function handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault(); // Necessary. Allows us to drop.
            }

            e.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.

            return false;
        }

        function handleDragEnter(e) {
            // this / e.target is the current hover target.
            this.classList.add('over');
        }

        function handleDragLeave(e) {
            this.classList.remove('over');  // this / e.target is previous target element.
        }

        function handleDrop(e) {
            // this/e.target is current target element.

            if (e.stopPropagation) {
                e.stopPropagation(); // Stops some browsers from redirecting.
            }

            // Don't do anything if dropping the same column we're dragging.
            if (dragSrcEl != this) {
                // Set the source column's HTML to the HTML of the column we dropped on.
                dragSrcEl.innerHTML = this.innerHTML;
                this.innerHTML = e.dataTransfer.getData('text/html');
            }

            let columnOrder = [];
            [].forEach.call(document.querySelectorAll('.column'), function (column) {
                columnOrder.push(column.querySelector('header').dataset.element);
            });

            document.getElementById('use-single-sidebar-widgets').value = columnOrder.join(',');

            return false;
        }

        function handleDragEnd(e) {
            // this/e.target is the source node.
            this.style.opacity = '1';
            [].forEach.call(cols, function (col) {
                col.classList.remove('over');
            });
        }

        let cols = document.querySelectorAll('#columns .column');
        [].forEach.call(cols, function(col) {
            col.addEventListener('dragstart', handleDragStart, false);
            col.addEventListener('dragenter', handleDragEnter, false);
            col.addEventListener('dragover', handleDragOver, false);
            col.addEventListener('dragleave', handleDragLeave, false);
            col.addEventListener('drop', handleDrop, false);
            col.addEventListener('dragend', handleDragEnd, false);
        });
    }
});
