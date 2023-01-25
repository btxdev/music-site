import vars from "../_vars.js";
import { fetchApi } from "./fetchApi";

export const loggedIn = () => {
  return new Promise((resolve, reject) => {
    fetchApi(vars.apiAuthUrl, {
      op: "check",
    })
      .then((data) => {
        if (data.status == "AUTHORIZED") {
          resolve();
        } else {
          reject();
        }
      })
      .catch((error) => {
        reject(error);
      });
  });
};
