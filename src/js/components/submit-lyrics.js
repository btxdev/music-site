import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";

const usedInComponents = ["submit-lyrics"];
const currentFilename = window.location.pathname.split("/").pop().split(".")[0];

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
  const $titleField = document.querySelector("input[name=title]");
  const $albumField = document.querySelector("input[name=album]");
  const $lyricsField = document.querySelector(".submit__textarea");
  const $hrefField = document.querySelector("input[name=href]");
  const fields = [
    $artistField,
    $titleField,
    $albumField,
    $lyricsField,
    $hrefField,
  ];
  validate(fields)
    .then(() => {
      return submitLyrics(
        $artistField,
        $titleField,
        $albumField,
        $lyricsField,
        $hrefField
      );
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
      if (id != 2 && field.value.length < 2) {
        blink(field);
        rejectCount++;
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

function submitLyrics(
  $artistField,
  $titleField,
  $albumField,
  $lyricsField,
  $hrefField
) {
  return new Promise((resolve, reject) => {
    const artist = $artistField.value;
    const title = $titleField.value;
    const album = $albumField.value;
    const lyrics = $lyricsField.value;
    const href = $hrefField.value;
    fetchApi(vars.apiUserUrl, {
      op: "submit_lyrics",
      artist: artist,
      title: title,
      album: album,
      lyrics: lyrics,
      href: href,
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
