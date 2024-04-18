import * as React from "react";
import { useQuery } from "react-query";

import { Route, Switch } from "react-router-dom";

import DuplicateGallery from "./DuplicateGallery";
import GalleryRow from "./GalleryRow";
import Menu from "./Menu";

// Pro tools
import { ErrorBoundary } from "react-error-boundary";
const ProTools = React.lazy(() => import("vimeography_pro/ProTools"));
// const Filters = React.lazy(() => import("vimeography_pro/Filters"));

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

const ProToolsAd = () => {
  return (
    <div>
      <h2 className="vm-text-lg vm-text-gray-700">
        Back up all of your galleries with Vimeography Pro.
      </h2>
      <a
        href="https://vimeography.com/pro"
        className="vm-text-blue-500 vm-text-base"
        target="_blank"
      >
        Learn more
      </a>
    </div>
  );
};

// const FiltersAd = () => {
//   return <div>Coming soon…</div>;
// };

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

  if (isLoading)
    return (
      <div className="vm-bg-gray-200 vm-w-full vm-h-32 vm-my-10 vm-animate-pulse vm-flex vm-items-center vm-justify-center vm-rounded vm-text-gray-700">
        Loading galleries…
      </div>
    );

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

      <div className="vm-container">
        {data.length === 0 ? (
          <NoGalleries />
        ) : (
          <Switch>
            <Route path="/" exact>
              <table className="vm-my-4 vm-w-full vm-border vm-shadow-xl">
                <thead>
                  <tr>
                    <th className="vm-px-4 vm-py-4 vm-text-left">ID</th>
                    <th className="vm-px-4 vm-py-4 vm-text-left">Title</th>
                    <th className="vm-px-4 vm-py-4 vm-text-left">
                      Video Source
                    </th>
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
                        key={gallery.id}
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

            <Route path="/tools" exact>
              <ErrorBoundary FallbackComponent={ProToolsAd}>
                <React.Suspense fallback={<div />}>
                  <ProTools />
                </React.Suspense>
              </ErrorBoundary>
            </Route>

            {/* <Route path="/filters">
              <ErrorBoundary FallbackComponent={FiltersAd}>
                <React.Suspense fallback={<div />}>
                  <Filters />
                </React.Suspense>
              </ErrorBoundary>
            </Route> */}
          </Switch>
        )}
      </div>
    </>
  );
};

export default ListGalleries;
