function clamp(num, min, max) {
    return num <= min ? min : num >= max ? max : num;
}
var PreviewController = /** @class */ (function () {
    function PreviewController(images) {
        this.transparent = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        this.images = images;
        this.detectIndex();
        this.attachEvents();
        this.render(); // maybe it's video
    }
    PreviewController.prototype.detectIndex = function () {
        var file = new URLSearchParams(document.location.search).get('file');
        console.log('file', file);
        for (var i in this.images) {
            if (this.images[i].id == file) {
                this.index = parseInt(i, 10);
                break;
            }
        }
        console.log('index', this.index);
    };
    PreviewController.prototype.attachEvents = function () {
        var _this = this;
        document.addEventListener('keyup', this.onKeyUp.bind(this));
        document.addEventListener('swiped-left', function (e) {
            _this.moveNext();
        });
        document.addEventListener('swiped-right', function (e) {
            _this.movePrev();
        });
    };
    PreviewController.prototype.moveNext = function () {
        if (this.index < this.images.length - 1) {
            this.index += 1;
            this.render();
        }
    };
    PreviewController.prototype.movePrev = function () {
        if (this.index > 0) {
            this.index -= 1;
            this.render();
        }
    };
    PreviewController.prototype.onKeyUp = function (e) {
        // console.log(e);
        if (e.key === 'ArrowRight') {
            this.moveNext();
        }
        if (e.key === 'ArrowLeft') {
            this.movePrev();
        }
        if (e.key === 'ArrowUp') {
            this.movePrev();
        }
        if (e.key === 'ArrowDown') {
            this.moveNext();
        }
        if (e.key === 'Escape') {
            this.onClick(new MouseEvent('click'));
        }
        if (e.key === 'PageUp') {
            this.index = clamp(this.index - 6, 0, this.images.length - 1);
            this.render();
        }
        if (e.key === 'PageDown') {
            this.index = clamp(this.index + 6, 0, this.images.length - 1);
            this.render();
        }
        if (e.key === 'Home') {
            this.index = 0;
            this.render();
        }
        if (e.key === 'End') {
            this.index = this.images.length - 1;
            this.render();
        }
        if (e.key === 'Enter') {
            this.onClick(new MouseEvent('click'));
        }
    };
    Object.defineProperty(PreviewController.prototype, "current", {
        get: function () {
            return this.images[this.index];
        },
        enumerable: true,
        configurable: true
    });
    PreviewController.prototype.render = function () {
        var _this = this;
        console.log(this.current);
        if (this.current && 'videosrc' in this.current) {
            var app = document.querySelector('#app');
            app.innerHTML = this.current.html;
            document.title = this.current.title;
        }
        else {
            var img_1 = document.querySelector('img');
            if (!img_1) {
                var img_2 = document.createElement('img');
                var app = document.querySelector('#app');
                app.innerHTML = img_2.toString();
            }
            // console.log(index, images[index]);
            img_1.style.backgroundImage = 'url(ShowThumb?file=' + this.current.id;
            img_1.src = this.transparent; // show bg
            setTimeout(function () {
                img_1.src = _this.current.src;
            }, 1);
            img_1.addEventListener('click', this.onClick.bind(this));
            document.title = this.current.title;
        }
        this.preloadAround(this.index, 5);
        this.updateURL();
    };
    PreviewController.prototype.preloadAround = function (index, range) {
        for (var i = index - range; i < index + range; i++) {
            if (i >= 0 && i < this.images.length) {
                var thumb = document.createElement('img');
                thumb.src = 'ShowThumb?file=' + this.images[i].id;
                // console.log('preloading', i);
                var img = document.createElement('img');
                img.src = this.images[i].src;
            }
        }
    };
    PreviewController.prototype.updateURL = function () {
        var params = new URLSearchParams(document.location.search);
        params.set('file', this.current.id);
        var newURL = new URL(document.location.href);
        newURL.search = params.toString();
        window.history.replaceState({}, document.title, newURL.toString());
    };
    PreviewController.prototype.onClick = function (e) {
        if (!document.referrer) {
            document.location.href = document.location.href.replace('Preview', 'MonthBrowserDB');
            return;
        }
        var url = new URL(document.referrer);
        url.hash = this.current.id;
        document.location.href = url.toString();
    };
    return PreviewController;
}());
