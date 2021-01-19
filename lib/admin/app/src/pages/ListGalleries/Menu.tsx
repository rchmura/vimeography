import * as React from "react";
import { NavLink } from "react-router-dom";

type MenuItemProps = {
  to: string;
  children: any;
};

const MenuItem = (props: MenuItemProps) => {
  return (
    <NavLink
      className="vm-mr-6 vm-border-solid vm-border-0 vm-py-2 vm-text-base focus:vm-outline-none vm-font-semibold vm-text-gray-600 vm-no-underline"
      activeClassName="vm-border-b-4 vm-border-indigo-500"
      to={props.to}
      exact={props.to === "/"}
    >
      {props.children}
    </NavLink>
  );
};

const ListGalleriesMenu = () => {
  return (
    <div className="vm-my-8">
      <MenuItem to="/">Galleries</MenuItem>
      <MenuItem to="/tools">Tools</MenuItem>
      {/* <MenuItem to="/filters">Filters</MenuItem> */}
    </div>
  );
};

export default ListGalleriesMenu;
