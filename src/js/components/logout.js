import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";
import { loggedIn } from "../functions/loggedIn";

document.addEventListener("DOMContentLoaded", main);

function main() {
  loggedIn()
    .then(() => {
      const $logoutBtn = document.createElement("div");
      $logoutBtn.setAttribute("class", "logout-btn");
      $logoutBtn.appendChild(document.createTextNode("Log out"));
      document.body.prepend($logoutBtn);
      $logoutBtn.addEventListener("click", logOut);
    })
    .catch(() => {});
}

function logOut() {
  return new Promise((resolve, reject) => {
    fetchApi(vars.apiAuthUrl, {
      op: "logout",
    })
      .then((data) => {
        document.location.reload();
        resolve(data);
      })
      .catch((error) => {
        reject(error);
      });
  });
}
