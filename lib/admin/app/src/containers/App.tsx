import * as React from "react";
import URLSearchParams from "@ungap/url-search-params";
import { MemoryRouter as Router } from "react-router-dom";

import { QueryClient, QueryClientProvider } from "react-query";
const queryClient = new QueryClient();

import GalleryProvider from "../providers/Gallery";
import GalleryEditor from "../components/GalleryEditor/GalleryEditor";

const App = () => {
  const params = new URLSearchParams(document.location.search.substring(1));

  const id = params.get(`id`);
  // console.log(params.get(`page`));

  return (
    <QueryClientProvider client={queryClient}>
      <GalleryProvider id={id}>
        <Router>
          <div className="vm-mx-auto vm-max-w-xl">
            <GalleryEditor />
          </div>
        </Router>
      </GalleryProvider>
    </QueryClientProvider>
  );
};

export default App;
