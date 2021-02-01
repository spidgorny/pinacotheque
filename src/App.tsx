import React from "react";
import ImageStream from "./ImageStream";
import { AppContext, context } from "./context";
// @ts-ignore
import axios from "redaxios";

import ScaleLoader from "react-spinners/ScaleLoader";
import { Sidebar } from "./Sidebar";
import "./test.object.assign";
import { Header } from "./widgets/header";
import "./app.css";
import { Route, Switch } from "wouter";
import BrowsePage from "./browse/browse-page";

interface IAppProps {}

export interface Source {
  id: number;
  name: string;
  path: string;
  thumbRoot: string;
  files: number;
  folders: number;
  md5: string;
  _missingProperties: [];
}

export interface IAppState {
  status?: "ok";
  min?: string;
  max?: string;
  sources?: Source[];
  query?: string;
  duration?: number;
}

function StreamPage(props: { state: IAppState }) {
  return (
    <div className="flex flex-row p-2">
      <div className="w-2/12">
        <Sidebar />
      </div>
      <div className="flex-grow">
        {props.state === null ? (
          <ScaleLoader loading={true} color="#4DAF7C" />
        ) : (
          <ImageStream />
        )}
      </div>
    </div>
  );
}

export default class App extends React.Component<IAppProps, IAppState> {
  static contextType = context;
  // @ts-ignore
  context: AppContext;
  baseUrl: URL | undefined;

  state: IAppState = {};

  componentDidMount() {
    this.baseUrl = this.context.baseUrl;
    // noinspection JSIgnoredPromiseFromCall
    this.fetchRange();
  }

  async fetchRange() {
    const urlInfo = new URL("Info", this.baseUrl);
    const res = await axios.get(urlInfo.toString());
    // console.log(res.data);
    const resData = res.data;
    if (resData.status !== "ok") {
      throw new Error(resData.error);
    }
    // console.log(resData);
    this.setState(resData);
  }

  render() {
    return (
      <div className="container" style={{ width: "100%", maxWidth: "100%" }}>
        <Header />
        <Switch>
          <Route path="/">
            <StreamPage state={this.state} />
          </Route>
          <Route path="/browse">
            {this.state.sources ? (
              <BrowsePage
                sources={this.state.sources || []}
                reloadSources={this.fetchRange.bind(this)}
              />
            ) : (
              <ScaleLoader />
            )}
          </Route>
        </Switch>
      </div>
    );
  }
}
