import * as React from "react";
import URLSearchParams from "@ungap/url-search-params";
import { MemoryRouter as Router } from "react-router-dom";

import { QueryClient, QueryClientProvider } from "react-query";
const queryClient = new QueryClient();

import ThemesProvider from "../providers/Themes";

import GalleryProvider from "../providers/Gallery";
import GalleryEditor from "../components/GalleryEditor/GalleryEditor";

const App = () => {
  const params = new URLSearchParams(document.location.search.substring(1));

  const id = params.get(`id`);
  // console.log(params.get(`page`));

  return (
    <QueryClientProvider client={queryClient}>
      <ThemesProvider>
        <GalleryProvider id={id}>
          <Router>
            <GalleryEditor />
          </Router>
        </GalleryProvider>
      </ThemesProvider>
    </QueryClientProvider>
  );
};

export default App;
