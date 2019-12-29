Pinacothèque
===

The web-based image browser for your GB of photos.

Written with React.

Indexing
---

Before you can quickly(!) browse your image collection we need in index all images slowly(!).

Usage
---

Start the development server with this command:

```
npm start
```

Setup
---

```
npm install
```

Compile
---

```
npm run compile
```

Download example locations
---
from here: https://www.researchgate.net/publication/259802674_World-Wide_Scale_Geotagged_Image_Dataset_for_Automatic_Image_Annotation_and_Reverse_Geotagging


Alternatives/Other tools
---

* https://petermolnar.net/how-to-build-an-exif-database-to-understand-your-photography/

ToDo
----

* [x] Scan folders and build thumbnails
* [x] Scan folders and store metadata
* [ ] Zip thumbnails - I don't like thousands of small files
* [x] Load all meta information into RAM to do queries
* [ ] Accept HTTP requests to query different things
* [ ] Make React app to show images and filter by all metadata
* [ ] ScanDir should skip non-image files
* [ ] Add scanning Exif data to the database
* [ ] Add scanning redis queue and give tasks to the queue

Later:
* [ ] Add editing sources in UI
