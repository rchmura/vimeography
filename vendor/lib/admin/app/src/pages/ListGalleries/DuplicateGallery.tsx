import * as React from "react";
import * as ReactDOM from "react-dom";
import { useMutation, useQueryClient } from "react-query";

import { Gallery } from "./ListGalleries";

type DuplicateGalleryProps = {
  gallery: Gallery;
  setDuplicateGalleryModalOpen: (toggle: boolean) => void;
};

type DuplicateGalleryPayload = {
  gallery_id: string;
  title: string;
  source: string;
  copyAppearance: boolean;
};

const DuplicateGallery = (props: DuplicateGalleryProps) => {
  const [title, setTitle] = React.useState(`${props.gallery.title} copy`);
  const [source, setSource] = React.useState(props.gallery.source_url);
  const [copyAppearance, setCopyAppearance] = React.useState(true);
  const queryClient = useQueryClient();

  const mutation = useMutation(
    (payload: DuplicateGalleryPayload) =>
      fetch(
        (window as any).vimeographyApiSettings.root +
          `vimeography/v1/galleries/${payload.gallery_id}/duplicate`,
        {
          method: "POST",
          mode: "same-origin",
          cache: "no-cache",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": window.wpApiSettings
              ? window.wpApiSettings.nonce
              : window.vimeographyApiSettings.nonce,
          },
          body: JSON.stringify({
            id: payload.gallery_id,
            title: payload.title,
            source_url: payload.source,
            copy_appearance: payload.copyAppearance,
          }), // body data type must match "Content-Type" header
        }
      ),
    {
      onSuccess: () => {
        queryClient.invalidateQueries("galleries");
      },
    }
  );

  const handle = async () => {
    // make request, close modal, reload galleries
    try {
      const result = await mutation.mutate({
        gallery_id: props.gallery.id,
        title,
        source,
        copyAppearance,
      });

      await props.setDuplicateGalleryModalOpen(false);
    } catch (error) {
      console.log(error);
    }
  };

  return ReactDOM.createPortal(
    <div className="vm-inset-0 vm-fixed vm-flex vm-items-center vm-justify-center vm-bg-gray-700 vm-bg-opacity-70">
      <div className="vm-bg-gray-100 vm-rounded vm-p-10 vm-shadow vm-w-full vm-relative vm-max-w-lg">
        <div
          className="vm-text-3xl vm-stroke-current vm-text-white vm-cursor-pointer vm-absolute vm-right-0"
          style={{ transform: "translate(5px, -70px)" }}
          onClick={() => props.setDuplicateGalleryModalOpen(false)}
        >
          <svg
            className="vm-w-6 vm-h-6"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fillRule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
              clipRule="evenodd"
            />
          </svg>
        </div>

        <h2 className="vm-text-xl vm-mb-5 vm-font-semibold">
          Duplicate gallery
        </h2>

        <form method="get" action="">
          <div className="vm-mb-4">
            <label className="vm-text-sm vm-block vm-mb-1 vm-font-semibold">
              New Gallery Title
            </label>
            <input
              type="text"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              className="vm-w-72"
            />
          </div>

          <div className="vm-mb-5">
            <label className="vm-text-sm vm-block vm-mb-1 vm-font-semibold">
              Show the videos from
            </label>
            <input
              type="text"
              value={source}
              onChange={(e) => setSource(e.target.value)}
              className="vm-w-72"
            />
          </div>

          <div className="vm-mb-8">
            <label className="vm-flex vm-items-center">
              <input
                type="checkbox"
                checked={copyAppearance}
                onChange={(e) => setCopyAppearance(e.target.checked)}
              />{" "}
              <span className="vm-ml-1">
                Also copy gallery appearance settings
              </span>
            </label>
          </div>

          <button
            onClick={handle}
            className="vm-bg-blue-500 vm-text-white vm-px-3 vm-py-2 vm-rounded hover:vm-bg-blue-700"
            disabled={mutation.isLoading}
          >
            {mutation.isLoading ? "Duplicating…" : "Duplicate gallery"}
          </button>
        </form>
      </div>
    </div>,
    document.body
  );
};

export default DuplicateGallery;
