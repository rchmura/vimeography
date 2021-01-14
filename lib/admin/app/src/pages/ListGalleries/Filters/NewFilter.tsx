import * as React from "react";

import { Link, useHistory } from "react-router-dom";
import { useMutation, useQueryClient } from "react-query";

const NewFilter = () => {
  const [title, setTitle] = React.useState("");
  const queryClient = useQueryClient();
  const history = useHistory();

  const mutation = useMutation(
    (title: string) =>
      fetch(
        (window as any).vimeographyApiSettings.root + `vimeography/v1/filters`,
        {
          method: "POST",
          mode: "same-origin",
          cache: "no-cache",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": (window as any).vimeographyApiSettings.nonce,
          },
          body: JSON.stringify({
            title,
          }),
        }
      ),
    {
      onSuccess: () => {
        queryClient.invalidateQueries("filters");
      },
    }
  );

  const addFilter = async () => {
    // make request, close modal, reload galleries
    if (title === "") return;

    try {
      const result = await mutation.mutate(title);
      history.push("/filters");
    } catch (error) {
      console.log(error);
    }
  };

  return (
    <>
      <div className="vm-flex vm-mb-4">
        <h2 className="vm-text-xl vm-flex-1">Create a new filter</h2>
        <Link to="/filters">Back to all filters</Link>
      </div>

      <div>
        <label className="vm-font-semibold vm-block vm-text-base vm-mb-1 vm-text-gray-600">
          Filter name
        </label>
        <input
          type="text"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          className="vm-mb-4 vm-w-72 vm-block"
        />
        <button
          onClick={addFilter}
          className="vm-rounded vm-bg-blue-500 vm-text-white vm-px-3 vm-py-2"
        >
          Create filter
        </button>
      </div>
    </>
  );
};

export default NewFilter;
