import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";

const usedInComponents = ["submit-youtube"];
const currentFilename = window.location.pathname.split("/").pop().split(".")[0];

const emailRegex = new RegExp(
  /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/
);

if (usedInComponents.includes(currentFilename)) {
  document.addEventListener("DOMContentLoaded", main);
}

function main() {
  const $submitBtn = document.querySelector(".submit__lyrics-btn");
  document
    .querySelector(".submit__form")
    .addEventListener("submit", (event) => {
      event.preventDefault();
      submitForm();
    });
  $submitBtn.addEventListener("click", (event) => {
    event.preventDefault();
    submitForm();
  });
}

function submitForm() {
  const $artistField = document.querySelector("input[name=artist]");
  const $hrefField = document.querySelector("input[name=href]");
  const $aboutField = document.querySelector(".submit__textarea");
  const $emailField = document.querySelector("input[name=email]");
  const fields = [$artistField, $hrefField, $aboutField, $emailField];
  validate(fields)
    .then(() => {
      return submitLyrics($artistField, $hrefField, $aboutField, $emailField);
    })
    .then(() => {
      document.location.reload();
    })
    .catch((error) => {
      if (["WRONG_FORMAT"].includes(error?.status)) {
        if ("blink" in error) {
          blink(fields[error.blink]);
        }
      }
    });
}

function validate(fields) {
  return new Promise((resolve, reject) => {
    let rejectCount = 0;
    let id = 0;
    fields.forEach((field) => {
      if (id == 3) {
        if (!field.value.match(emailRegex)) {
          blink(field);
          rejectCount++;
        }
      } else {
        if (id != 2 && field.value.length < 2) {
          blink(field);
          rejectCount++;
        }
      }
      id++;
    });
    if (rejectCount == 0) {
      resolve();
    } else {
      reject();
    }
  });
}

function blink(field) {
  function blinkFunc(count) {
    if (count % 2 == 0) {
      field.style.border = "1px solid red";
    } else {
      field.style.border = "1px solid #B4B4B4";
    }
    if (count > 4) return;
    setTimeout(blinkFunc, 150, ++count);
  }
  blinkFunc(0);
}

function submitLyrics($artistField, $hrefField, $aboutField, $emailField) {
  const artist = $artistField.value;
  const href = $hrefField.value;
  const about = $aboutField.value;
  const email = $emailField.value;
  return new Promise((resolve, reject) => {
    fetchApi(vars.apiUserUrl, {
      op: "submit_lyrics_yt",
      artist: artist,
      href: href,
      about: about,
      email: email,
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
