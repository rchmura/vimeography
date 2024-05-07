import * as React from "react";

const ProSellPanel = () => {
  return (
    <div className="vm-p-5">
      <h2 className="vm-text-xl vm-font-bold vm-text-gray-700 vm-mb-4">
        Add the features your viewers are asking for.
      </h2>

      <ul className="vm-mb-6">
        <li className="vm-flex vm-items-center vm-mb-2">
          <svg
            className="vm-w-5 vm-h-5 vm-text-blue-500 vm-mr-2"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path d="M4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4A1 1 0 0010 6v2.798l-5.445-3.63z" />
          </svg>
          <span className="vm-text-gray-600">Unlimited videos</span>
        </li>
        <li className="vm-flex vm-items-center vm-mb-2">
          <svg
            className="vm-w-5 vm-h-5 vm-text-yellow-500 vm-mr-2"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path d="M9 9a2 2 0 114 0 2 2 0 01-4 0z" />
            <path
              fillRule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a4 4 0 00-3.446 6.032l-2.261 2.26a1 1 0 101.414 1.415l2.261-2.261A4 4 0 1011 5z"
              clipRule="evenodd"
            />
          </svg>
          Searchable galleries
        </li>
        <li className="vm-flex vm-items-center vm-mb-2">
          <svg
            className="vm-w-5 vm-h-5 vm-text-indigo-500 vm-mr-2"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z" />
          </svg>
          Downloadable videos
        </li>
        <li className="vm-flex vm-items-center vm-mb-2">
          <svg
            className="vm-w-5 vm-h-5 vm-text-green-500 vm-mr-2"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L13.586 5H8zM12 15a1 1 0 100-2H6.414l1.293-1.293a1 1 0 10-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L6.414 15H12z" />
          </svg>
          Simple sorting
        </li>
        <li className="vm-flex vm-items-center vm-mb-2">
          <svg
            className="vm-w-5 vm-h-5 vm-text-pink-500 vm-mr-2"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fillRule="evenodd"
              d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
              clipRule="evenodd"
            />
          </svg>
          Show protected videos
        </li>
        <li className="vm-flex vm-items-center vm-mb-2">
          <svg
            className="vm-w-5 vm-h-5 vm-text-red-500 vm-mr-2"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fillRule="evenodd"
              d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z"
              clipRule="evenodd"
            />
          </svg>
          No code required
        </li>
      </ul>

      <a
        href="https://vimeography.com/pro"
        target="_blank"
        className="vm-box-border vm-px-4 vm-py-3 vm-rounded vm-border vm-shadow-sm vm-mx-auto vm-text-base vm-bg-white vm-text-indigo-800 vm-text-center vm-mb-2 vm-inline-flex vm-items-center vm-justify-center vm-w-full"
      >
        <svg
          className="vm-w-5 vm-h-5 vm-mr-1"
          fill="currentColor"
          viewBox="0 0 20 20"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fillRule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
            clipRule="evenodd"
          />
        </svg>
        Watch the video
      </a>
      <p className="vm-text-xs vm-text-gray-500 vm-text-center">
        Over 5,000 happy customers!
      </p>
    </div>
  );
};

export default ProSellPanel;
