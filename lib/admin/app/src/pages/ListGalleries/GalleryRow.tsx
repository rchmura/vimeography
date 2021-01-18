import * as React from "react";
import { useMutation, useQueryClient } from "react-query";

import { Gallery } from "./ListGalleries";

type GalleryRowProps = {
  gallery: Gallery;
  setGalleryToDuplicate: (id: string) => void;
  setDuplicateGalleryModalOpen: (toggle: boolean) => void;
};

const GalleryRow = (props: GalleryRowProps) => {
  const gallery = props.gallery;
  const queryClient = useQueryClient();

  const mutation = useMutation(
    (id: string) =>
      fetch(
        (window as any).vimeographyApiSettings.root +
          `vimeography/v1/galleries/${id}`,
        {
          method: "DELETE",
          mode: "same-origin",
          cache: "no-cache",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": window.wpApiSettings
              ? window.wpApiSettings.nonce
              : window.vimeographyApiSettings.nonce,
          },
        }
      ),
    {
      onSuccess: () => {
        queryClient.invalidateQueries("galleries");
      },
    }
  );

  const handleDelete = async (id: string) => {
    if (
      !confirm(
        "WARNING: You are about to delete this gallery. 'Cancel' to stop, 'OK' to delete."
      )
    ) {
      return false;
    }

    try {
      await mutation.mutate(id);
    } catch (error) {}
  };

  return (
    <tr
      className="vm-text-sm vm-text-gray-700 odd:vm-bg-gray-50"
      key={gallery.id}
    >
      <td className="vm-p-4">{gallery.id}</td>
      <td>
        <strong>
          <a
            className="vm-no-underline vm-text-gray-600"
            href={`?page=vimeography-edit-galleries&id=${gallery.id}`}
          >
            {gallery.title}
          </a>
        </strong>
      </td>
      <td className="vm-p-4">
        <a
          className="vm-text-blue-500"
          href={gallery.source_url}
          target="_blank"
        >
          {gallery.source_url}
        </a>
      </td>
      <td className="vm-p-4 vm-font-mono">[vimeography id="{gallery.id}"]</td>
      <td className="vm-p-4">{gallery.theme_name}</td>
      <td className="vm-p-4">{gallery.date_created}</td>
      <td className="vm-p-4">
        <div className="vm-flex vm-items-center">
          <a
            href={`?page=vimeography-edit-galleries&id=${gallery.id}`}
            title="Edit Gallery"
            className="vm-cursor-pointer"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="#ad915e"
              className="vm-w-6 vm-h-6 vm-mx-2"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
              />
            </svg>
          </a>

          <a
            href="#"
            className="vm-cursor-pointer"
            onClick={(e) => {
              e.preventDefault();
              props.setGalleryToDuplicate(gallery.id);
              props.setDuplicateGalleryModalOpen(true);
            }}
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="#4d60bf"
              className="vm-w-6 vm-h-6 vm-mx-2"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
              />
            </svg>
          </a>

          <a
            className="vm-cursor-pointer"
            title="Delete Gallery"
            onClick={() => handleDelete(gallery.id)}
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="#B73657"
              className="vm-w-6 vm-h-6 vm-mx-2"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
              />
            </svg>
          </a>
        </div>
      </td>
    </tr>
  );
};

export default GalleryRow;
