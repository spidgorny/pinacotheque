var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
var _this = this;
document.addEventListener("DOMContentLoaded", function () { return __awaiter(_this, void 0, void 0, function () {
    return __generator(this, function (_a) {
        new MapManager();
        return [2 /*return*/];
    });
}); });
var MapManager = /** @class */ (function () {
    function MapManager() {
        this.fetchGPSdata();
    }
    MapManager.prototype.fetchGPSdata = function () {
        return __awaiter(this, void 0, void 0, function () {
            var url, res, json;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        this.url = new URL(document.location.href);
                        this.source = parseInt(this.url.searchParams.get('source'), 10);
                        this.year = parseInt(this.url.searchParams.get('year'), 10);
                        this.month = parseInt(this.url.searchParams.get('month'), 10);
                        url = new URL(this.url.toString());
                        url.searchParams.set('action', 'gps');
                        url.searchParams.set('bounds', this.getBoundsFromURL ? this.getBoundsFromURL : '');
                        return [4 /*yield*/, fetch(url.toString(), {})];
                    case 1:
                        res = _a.sent();
                        return [4 /*yield*/, res.json()];
                    case 2:
                        json = _a.sent();
                        console.log(json);
                        if (json) {
                            this.makeMap(json);
                        }
                        return [2 /*return*/];
                }
            });
        });
    };
    Object.defineProperty(MapManager.prototype, "getBoundsFromURL", {
        get: function () {
            return this.url.searchParams.get('bounds');
        },
        enumerable: true,
        configurable: true
    });
    MapManager.prototype.makeMap = function (json) {
        var _this = this;
        var arrayOfLatLngs = json.map(function (info) {
            return [info.lat, info.lon];
        });
        var bounds = new L.LatLngBounds(arrayOfLatLngs);
        this.map = L.map('mapid');
        //mymap.setView([51.505, -0.09], 13);
        this.map.fitBounds(bounds);
        var osmUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
        L.tileLayer(osmUrl, {
            maxZoom: 18,
        }).addTo(this.map);
        json.map(function (info) {
            var clickURL = "Preview?source=" + _this.source + "&year=" + _this.year + "&month=" + _this.month + "&file=" + info.id;
            var imageURL = "ShowThumb?file=" + info.id;
            L.marker([info.lat, info.lon], {
                title: info.path,
                riseOnHover: true,
            }).addTo(_this.map)
                .bindPopup("\n\t\t\t\t\t<p>\n\t\t\t\t\t<a href=\"" + clickURL + "\">\n\t\t\t\t\t<img src=\"" + imageURL + "\"  alt=\"Photo\"/>\n\t\t\t\t\t</a>\n\t\t\t\t\t</p>\n\t\t\t\t\t<p>" + info.path + "</p>\n\t\t\t\t");
        });
        this.map.on('zoomend', function (e) {
            // console.log('zoom', e);
            _this.updateImageRows();
            _this.updateURL();
        });
        this.map.on('moveend', function (e) {
            // console.log('move', e);
            _this.updateImageRows();
            _this.updateURL();
        });
    };
    Object.defineProperty(MapManager.prototype, "getBoundsJSON", {
        get: function () {
            var bounds = this.map.getBounds();
            // console.log(bounds);
            return JSON.stringify({
                west: bounds.getWest(),
                east: bounds.getEast(),
                north: bounds.getNorth(),
                south: bounds.getSouth(),
            });
        },
        enumerable: true,
        configurable: true
    });
    MapManager.prototype.updateImageRows = function () {
        return __awaiter(this, void 0, void 0, function () {
            var ajaxURL, html;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        ajaxURL = new URL(this.url.toString());
                        ajaxURL.pathname = 'MonthBrowserDB';
                        ajaxURL.searchParams.set('action', 'filterByGPS');
                        ajaxURL.searchParams.set('source', this.source.toString());
                        ajaxURL.searchParams.set('year', this.year.toString());
                        ajaxURL.searchParams.set('month', this.month.toString());
                        ajaxURL.searchParams.set('bounds', this.getBoundsJSON);
                        return [4 /*yield*/, fetch(ajaxURL.toString())];
                    case 1: return [4 /*yield*/, (_a.sent()).text()];
                    case 2:
                        html = _a.sent();
                        document.querySelector('#imageRows').innerHTML = html;
                        return [2 /*return*/];
                }
            });
        });
    };
    MapManager.prototype.updateURL = function () {
        var newURL = new URL(document.location.href);
        newURL.searchParams.set('bounds', this.getBoundsJSON);
        window.history.replaceState({}, document.title, newURL.toString());
    };
    return MapManager;
}());
