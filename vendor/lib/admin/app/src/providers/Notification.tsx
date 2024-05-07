import * as React from "react";
import * as ReactDOM from "react-dom";
import { motion } from "framer-motion";
import { nanoid } from "nanoid";

import NotificationContext from "../context/Notification";

type Notification = {
  message: string;
  type: "success" | "error";
};

const CLASSES = {
  success: "vm-text-green-500",
  error: "vm-text-red-600",
};

const variants = {
  hidden: {
    y: 200,
    opacity: 0,
  },
  visible: {
    y: 0,
    opacity: 1,
  },
};

const NotificationContainer = (props) => {
  const notifications = props.notifications;

  return ReactDOM.createPortal(
    <div className="vm-absolute vm-bottom-5 vm-w-full vm-z-10 vm-flex vm-flex-col vm-items-center vm-justify-center">
      {notifications.map((n) => (
        <motion.div
          key={n.id}
          variants={variants}
          initial="hidden"
          animate="visible"
          onClick={() => props.removeNotification(n.id)}
          className={`vm-bg-white vm-shadow-xl vm-p-4 vm-rounded vm-mb-3 vm-border vm-border-solid vm-border-gray-100 vm-font-semibold ${
            CLASSES[n.type]
          }`}
        >
          {n.message}
        </motion.div>
      ))}
    </div>,
    document.body
  );
};

const NotificationProvider = ({ children }) => {
  const [notifications, setNotifications] = React.useState([]);

  const showNotification = React.useCallback(
    (type, message) => {
      setNotifications((notifications) => [
        ...notifications,
        { id: nanoid(), message, type },
      ]);
    },
    [setNotifications]
  );

  const removeNotification = React.useCallback(
    (id) => {
      setNotifications((notifications) =>
        notifications.filter((t) => t.id !== id)
      );
    },
    [setNotifications]
  );

  React.useEffect(() => {
    const timers = notifications.map((n) => {
      return setTimeout(() => {
        removeNotification(n.id);
      }, 3000);
    });

    return () => {
      timers.map((timer) => clearTimeout(timer));
    };
  }, [notifications, removeNotification]);

  return (
    <NotificationContext.Provider
      value={{ showNotification, removeNotification }}
    >
      <NotificationContainer
        notifications={notifications}
        removeNotification={removeNotification}
      />
      {children}
    </NotificationContext.Provider>
  );
};

export default NotificationProvider;
