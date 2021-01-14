import * as React from "react";
import { useQuery } from "react-query";
import ListFilters from "./ListFilters";
import NewFilter from "./NewFilter";
import EditFilter from "./EditFilter";

import { Switch, Route, Link } from "react-router-dom";

const Filters = () => {
  const { isLoading, error, data } = useQuery([`filters`], () => {
    return fetch(
      (window as any).vimeographyApiSettings.root + `vimeography/v1/filters`
    ).then((res) => {
      return res.json();
    });
  });

  if (isLoading) return <div>Loading filters…</div>;

  return (
    <div>
      <Switch>
        <Route path="/filters" exact>
          <div className="vm-flex vm-items-center">
            <div className="vm-flex-1">
              <h3 className="vm-text-xl vm-text-gray-700">Gallery Filters</h3>
              <p className="vm-text-base vm-text-gray-600">
                Help your viewers find the exact videos they're looking for.
              </p>
            </div>

            <Link to="/filters/new" className="vm-text-blue-500">
              Add new filter
            </Link>
          </div>
          {data.length === 0 ? (
            <div>No filters.</div>
          ) : (
            <ListFilters data={data} />
          )}
        </Route>

        <Route path="/filters/new">
          <NewFilter />
        </Route>

        <Route path="/filters/:filterId">
          <EditFilter />
        </Route>
      </Switch>
    </div>
  );
};

export default Filters;
