import vars from "../_vars.js";
import { fetchApi } from "./fetchApi";

export const loggedIn = () => {
  return new Promise((resolve, reject) => {
    if (typeof vars.loggedIn == "undefined") {
      fetchApi(vars.apiAuthUrl, {
        op: "check",
      })
        .then((data) => {
          if (data.status == "AUTHORIZED") {
            vars.loggedIn = true;
            resolve();
          } else {
            vars.loggedIn = false;
            reject();
          }
        })
        .catch((error) => {
          vars.loggedIn = false;
          reject(error);
        });
    } else {
      if (vars.loggedIn === true) {
        resolve();
      } else {
        reject();
      }
    }
  });
};
