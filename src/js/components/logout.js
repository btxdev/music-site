import { fetchApi } from "../functions/fetchApi";

const apiUrl = "/php/auth.php";

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

function loggedIn() {
  return new Promise((resolve, reject) => {
    fetchApi(apiUrl, {
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
}

function logOut() {
  return new Promise((resolve, reject) => {
    fetchApi(apiUrl, {
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
