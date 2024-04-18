import * as React from "react";
import NotificationContext from "../context/Notification";

export function useNotification() {
  const notificationHelpers = React.useContext(NotificationContext);
  return notificationHelpers;
}
