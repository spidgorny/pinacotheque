document.addEventListener("DOMContentLoaded", function () {
    var refDom = document.querySelectorAll('span.meta');
    var refArray = Array.from(refDom);
    for (var _i = 0, refArray_1 = refArray; _i < refArray_1.length; _i++) {
        var reference = refArray_1[_i];
        var id = reference.getAttribute('data-id');
        //console.log(id);
        var popper = document.querySelector('#' + id);
        // console.log(id, popper);
        if (popper) {
            new Tooltip(reference, {
                title: popper.innerHTML,
                html: true,
            });
        }
    }
});
