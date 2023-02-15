import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";
import { loggedIn } from "../functions/loggedIn";

const usedInComponents = ["auth"];
const currentFilename = window.location.pathname.split("/").pop().split(".")[0];

const loginRegex = new RegExp(/^[a-z\d_]{4,20}$/i);
const passwordRegex = new RegExp(/^([a-zA-Z0-9-.,_!а-яА-ЯёЁ]){6,32}$/);

if (usedInComponents.includes(currentFilename)) {
  document.addEventListener("DOMContentLoaded", main);
}

function main() {
  const urlParams = new URLSearchParams(window.location.search);
  const referrer = urlParams.get("ref") || "/";

  loggedIn()
    .then(() => {
      document.location.replace(referrer);
    })
    .catch(() => {});

  const $loginField = document.querySelector("input[name=Login]");
  const $passwordField = document.querySelector("input[name=Password]");
  const $submitBtn = document.querySelector(".main-btn");

  $submitBtn.addEventListener("click", (event) => {
    event.preventDefault();

    validate($loginField, $passwordField)
      .then(() => {
        return authorize($loginField, $passwordField);
      })
      .then(() => {
        document.location.reload();
      })
      .catch((error) => {
        if (["WRONG_PASSWORD", "USER_NOT_FOUND"].includes(error?.status)) {
          document.location.reload();
        }
      });
  });
}

function validate($loginField, $passwordField) {
  return new Promise((resolve, reject) => {
    const login = $loginField.value;
    const password = $passwordField.value;
    if (!login.match(loginRegex)) {
      reject("incorrect login");
      return;
    }
    if (!password.match(passwordRegex)) {
      reject("incorrect password");
      return;
    }
    resolve();
  });
}

function authorize($loginField, $passwordField) {
  return new Promise((resolve, reject) => {
    const login = $loginField.value;
    const password = $passwordField.value;
    fetchApi(vars.apiAuthUrl, {
      op: "login",
      login: login,
      password: password,
    })
      .then((data) => {
        if (data.status == "OK") {
          resolve();
        } else {
          reject(data);
        }
      })
      .catch((error) => {
        reject(error);
      });
  });
}
