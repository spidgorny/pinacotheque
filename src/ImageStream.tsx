import React from "react";
import axios from "redaxios";
import { Image } from "./model/Image";
import moment from "moment";
import { AppContext, context } from "./context";
import { ImageFromFilter } from "./model/ImageFromFilter";
import { GalleryInScroll } from "./GalleryInScroll";

interface IAppProps {}

interface IAppState {
  start: Date;
  items: Image[];
  end?: Date;
}

export interface PhotoSetItem {
  src: string;
  width: number;
  height: number;
  image: Image;
}

export default class ImageStream extends React.Component<IAppProps, IAppState> {
  state = {
    start: new Date(),
    items: [],
    end: undefined,
  };

  static contextType = context;
  // @ts-ignore
  context: AppContext;

  baseUrl: URL | undefined;

  componentDidMount() {
    this.baseUrl = this.context.baseUrl;
    if (this.context.lastTopTimestamp) {
      this.setState({
        start: this.context.lastTopTimestamp,
      });
    }
    this.fetchData().then();
  }

  async fetchData() {
    await this.fetchDataFromFilterServer();
  }

  async fetchDataFromFilterServer() {
    const urlImages = new URL(process.env.REACT_APP_API + "Images");
    this.appendSearchParams(urlImages);
    //console.log(urlImages);
    const res = await axios.get(urlImages.toString());
    // console.log(res.data);
    const resData = res.data;
    if (resData.status !== "ok") {
      throw new Error(resData.error);
    }

    const images: Image[] = resData.data.map(
      (el: Image) => new ImageFromFilter(el)
    );
    this.appendImages(images);
  }

  appendImages(images: any[]) {
    const lastImage = images[images.length - 1];
    const lastDate = lastImage.getTimestamp();
    // console.log(lastImage, lastDate);
    this.setState(
      ({ items }) => {
        // append if not id exists
        images.map((nnew) => {
          if (!items.some((el) => el.id === nnew.id)) {
            nnew.baseUrl = this.baseUrl;
            return items.push(nnew);
          }
          return "x";
        });
        return {
          items,
          end: lastDate,
        };
      },
      () => {
        // console.log(this.state.items.map((el: Image) => el.id));
      }
    );
  }

  appendSearchParams(urlImages: URL) {
    urlImages.searchParams.set(
      "since",
      moment(this.state.end || this.state.start).format("YYYY-MM-DD HH:mm:ss")
    );
    let minWidth = this.context.sidebar?.minWidth;
    if (minWidth) {
      urlImages.searchParams.set("minWidth", minWidth.toString());
    }
  }

  async fetchDataFromPHP() {
    const urlImages = new URL("Images", this.baseUrl);
    this.appendSearchParams(urlImages);
    //console.log(urlImages);
    const res = await axios.get(urlImages.toString(), {
      // cors: 'no-cors',
    });
    // console.log(res.data);
    const resData = JSON.parse(res.data);
    if (resData.status !== "ok") {
      throw new Error(resData.error);
    }

    const images: Image[] = resData.data.map((el: Image) => new Image(el));
    this.appendImages(images);
  }

  get queryParams() {
    const url = new URL(document.location.href);
    return url.searchParams;
  }

  render() {
    const photoSet = this.state.items.map((img: Image | ImageFromFilter) => {
      // console.log(img);
      return {
        src: img.thumbURL,
        width: img.getWidth(),
        height: img.getHeight(),
        image: img,
      } as PhotoSetItem;
    });
    //console.log(photoSet.map(el => el.src + ' [' + el.width + 'x' + el.height + ']'));

    if (this.queryParams.has("simple")) {
      return (
        <div>
          {photoSet.map((el) => (
            <div style={{ clear: "both" }} key={el.src}>
              <img src={el.src} style={{ float: "left" }} alt={el.src} />
              {el.src}
              <br />
              {el.width}x{el.height}
              <br />
              {/*{JSON.stringify(el.image, null, 2)}*/}
            </div>
          ))}
        </div>
      );
    }
    return (
      <GalleryInScroll
        items={this.state.items}
        photos={photoSet}
        next={this.fetchData.bind(this)}
        refreshFunction={this.refresh.bind(this)}
      />
    );
  }

  refresh() {
    console.log("refresh");
  }
}
