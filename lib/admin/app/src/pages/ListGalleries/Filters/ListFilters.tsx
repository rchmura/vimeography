import * as React from "react";

import { Link } from "react-router-dom";

type FilterSortType = "RADIO";
type FilterSortBy = "OPTION_POSITION";

type Filter = {
  id: string;
  options: string;
  slug: string;
  sort_by: FilterSortBy;
  title: string;
  type: FilterSortType;
};

type ListFiltersProps = {
  data: Filter[];
};

const ListFilters = (props: ListFiltersProps) => {
  return (
    <>
      {props.data.map((filter: Filter) => (
        <div key={filter.id} className="vm-p-4">
          {filter.title}{" "}
          <Link to={`/filters/${filter.id}`} className="vm-text-blue-500">
            Edit
          </Link>
        </div>
      ))}
    </>
  );
};

export default ListFilters;
