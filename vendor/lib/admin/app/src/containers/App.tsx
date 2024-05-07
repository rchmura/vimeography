import * as React from "react";
import URLSearchParams from "@ungap/url-search-params";
import { MemoryRouter as Router, Switch, Route } from "react-router-dom";

import { QueryClient, QueryClientProvider } from "react-query";
const queryClient = new QueryClient();

import ThemesProvider from "../providers/Themes";
import GalleryProvider from "../providers/Gallery";
import NotificationProvider from "../providers/Notification";
import GalleryEditor from "../components/GalleryEditor/GalleryEditor";

import ListGalleries from "../pages/ListGalleries/ListGalleries";

const App = () => {
  const params = new URLSearchParams(document.location.search.substring(1));

  const id = params.get(`id`);
  const page = params.get(`page`);

  // Need to fake a top-level router here since
  // these subcomponents implement a MemoryRouter
  const isListGalleries = page === `vimeography-edit-galleries` && id === null;
  const isEditGallery = page === `vimeography-edit-galleries` && id !== null;

  return (
    <QueryClientProvider client={queryClient}>
      <NotificationProvider>
        <ThemesProvider>
          <Router>
            {isListGalleries && <ListGalleries />}

            {isEditGallery && (
              <GalleryProvider id={id}>
                <GalleryEditor />
              </GalleryProvider>
            )}
          </Router>
        </ThemesProvider>
      </NotificationProvider>
    </QueryClientProvider>
  );
};

export default App;
