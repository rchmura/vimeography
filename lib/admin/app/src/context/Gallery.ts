import * as React from "react";
import { GalleryState } from "../providers/Gallery";

type GalleryContext = {
  isLoading: boolean;
  error: Error;
  data: GalleryState;
  state: GalleryState;
  dispatch: any;
};

const ctx: GalleryContext = {
  isLoading: true,
  error: null,
  data: null,
  state: null,
  dispatch: () => {},
};

const GalleryContext = React.createContext(ctx);

export default GalleryContext;
