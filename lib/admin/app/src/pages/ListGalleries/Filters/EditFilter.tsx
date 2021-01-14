import * as React from "react";

import { useParams, Link } from "react-router-dom";
import { useQuery, useQueryClient } from "react-query";

// id, title, slug for filter option
const EditFilter = () => {
  const params = useParams();
  const queryClient = useQueryClient();

  const { isLoading, error, data } = useQuery(
    ["filters", params.filterId],
    () => {
      return fetch(
        (window as any).vimeographyApiSettings.root +
          `vimeography/v1/filters/${params.filterId}`
      ).then((res) => {
        return res.json();
      });
    },
    {
      placeholderData: () =>
        queryClient
          .getQueryData("filters")
          ?.find((d) => d.id == params.filterId),
      staleTime: 2000,
    }
  );

  if (isLoading) return <div>Loading filter...</div>;
  if (error) return <div>Error!</div>

  return (
    <>
      <div className="vm-flex">
        <div className="vm-flex-1">Edit Filter</div>
        <Link to="/filters" className="vm-text-blue-500">
          Back to all filters
        </Link>
      </div>

      <div>
        <label>Filter name</label>
        <input type="text" />

        <button>Add option</button>
      </div>

      {data.title}
    </>
  );
};

export default EditFilter;
