import * as React from "react";
import { useQuery } from "react-query";

import { Route, Switch } from "react-router-dom";

import DuplicateGallery from "./DuplicateGallery";
import GalleryRow from "./GalleryRow";
import Menu from "./Menu";

// Pro tools
import { ErrorBoundary } from "react-error-boundary";
const ProTools = React.lazy(() => import("vimeography_pro/ProTools"));

import Filters from "./Filters/Filters";

export type Gallery = {
  id: string;
  source_url: string;
  theme_name: string;
  title: string;
  date_created: string;
};

const NoGalleries = () => {
  return (
    <h1>
      You don't have any galleries.{" "}
      <a
        className="vm-text-blue-500"
        href={`?page=vimeography-new-gallery`}
        title="Create a new gallery"
      >
        Care to make one?
      </a>
    </h1>
  );
};

const ListGalleries = () => {
  const [
    duplicateGalleryModalOpen,
    setDuplicateGalleryModalOpen,
  ] = React.useState(false);

  const [galleryToDuplicate, setGalleryToDuplicate] = React.useState(null);

  const { isLoading, error, data } = useQuery([`galleries`], () => {
    return fetch(
      (window as any).vimeographyApiSettings.root + `vimeography/v1/galleries`
    ).then((res) => {
      return res.json();
    });
  });

  if (isLoading) return <div>Loading...</div>;

  return (
    <>
      {duplicateGalleryModalOpen && (
        <DuplicateGallery
          gallery={data.find(
            (gallery: Gallery) => gallery.id === galleryToDuplicate
          )}
          setDuplicateGalleryModalOpen={setDuplicateGalleryModalOpen}
        />
      )}

      <Menu />

      {data.length === 0 ? (
        <NoGalleries />
      ) : (
        <Switch>
          <Route path="/" exact>
            <table className="vm-my-4 vm-w-full vm-max-w-screen-2xl">
              <thead>
                <tr>
                  <th className="vm-px-4 vm-py-4 vm-text-left">ID</th>
                  <th className="vm-px-4 vm-py-4 vm-text-left">Title</th>
                  <th className="vm-px-4 vm-py-4 vm-text-left">Video Source</th>
                  <th className="vm-px-4 vm-py-4 vm-text-left">Shortcode</th>
                  <th className="vm-px-4 vm-py-4 vm-text-left">
                    Gallery Theme
                  </th>
                  <th className="vm-px-4 vm-py-4 vm-text-left">Created on</th>
                  <th className="vm-px-4 vm-py-4 vm-text-left">Actions</th>
                </tr>
              </thead>
              <tbody>
                {data.map((gallery: Gallery) => {
                  return (
                    <GalleryRow
                      gallery={gallery}
                      setDuplicateGalleryModalOpen={
                        setDuplicateGalleryModalOpen
                      }
                      setGalleryToDuplicate={setGalleryToDuplicate}
                    />
                  );
                })}
              </tbody>
            </table>
          </Route>

          <ErrorBoundary
            FallbackComponent={
              <div>Get import/export tools with Vimeography Pro</div>
            }
          >
            <React.Suspense fallback={<div />}>
              <Route path="/tools" exact>
                <ProTools />
              </Route>
            </React.Suspense>
          </ErrorBoundary>

          {/* <Route path="/filters">
            <Filters />
          </Route> */}
        </Switch>
      )}
    </>
  );
};

export default ListGalleries;
