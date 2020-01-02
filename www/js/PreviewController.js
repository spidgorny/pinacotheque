var PreviewController = /** @class */ (function () {
    function PreviewController(images) {
        this.images = images;
        this.detectIndex();
        this.attachEvents();
        this.render();
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
        document.addEventListener('keyup', this.onKeyUp.bind(this));
    };
    PreviewController.prototype.onKeyUp = function (e) {
        // console.log(e);
        if (e.key === 'ArrowRight') {
            if (this.index < this.images.length - 1) {
                this.index += 1;
            }
            this.render();
        }
        if (e.key === 'ArrowLeft') {
            if (this.index > 0) {
                this.index -= 1;
            }
            this.render();
        }
        if (e.key === 'ArrowUp') {
            if (this.index > 0) {
                this.index += 1;
            }
            this.render();
        }
        if (e.key === 'ArrowDown') {
            if (this.index < this.images.length - 1) {
                this.index += 1;
            }
            this.render();
        }
    };
    PreviewController.prototype.render = function () {
        console.log(this.images[this.index]);
        if ('videosrc' in this.images[this.index]) {
            var app = document.querySelector('#app');
            app.innerHTML = this.images[this.index].html;
        }
        else {
            var img = document.querySelector('img');
            if (!img) {
                var img_1 = document.createElement('img');
                var app = document.querySelector('#app');
                app.innerHTML = img_1.toString();
            }
            // console.log(index, images[index]);
            img.style.backgroundImage = 'url(ShowThumb?file=' + this.images[this.index].id;
            img.src = this.images[this.index].src;
            img.addEventListener('click', this.onClick.bind(this));
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
                // const img = document.createElement('img');
                // img.src = this.images[i].src;
            }
        }
    };
    PreviewController.prototype.updateURL = function () {
        var params = new URLSearchParams(document.location.search);
        params.set('file', this.images[this.index].id);
        var newURL = new URL(document.location.href);
        newURL.search = params.toString();
        window.history.replaceState({}, document.title, newURL.toString());
    };
    PreviewController.prototype.onClick = function (e) {
        var url = new URL(document.referrer);
        url.hash = this.images[this.index].id;
        document.location.href = url.toString();
    };
    return PreviewController;
}());
