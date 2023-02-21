import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";

const isCategory = window.location.pathname.split("/")[1] == 'category';
const currentFilename = window.location.pathname.split("/").pop().split(".")[0];

let currentCategoryId = -1;

if (isCategory) {
  document.addEventListener("DOMContentLoaded", main);
}

function main() {
  loadSongs();
}

function loadSongs() {
  const $title = document.querySelector('.trend__title');
  const $ul = document.querySelector('.trend__list');
  $ul.innerHTML = "";
  fetchApi(vars.apiUserUrl, {
    op: "get_category_data",
    url: currentFilename
  })
  .then((data) => {
    if(data.status == 'EMPTY') {
      document.location.replace('/categories.html');
    }
    if(data.status != 'OK') {
      return;
    }
    currentCategoryId = data.msg.cat_id;
    $title.innerText = data.msg.title;
    data.msg.songs.forEach(item => {
      const id = item.song_id;
      const artist = item.artist;
      const title = item.title;
      addSongComponent($ul, id, artist, title);
    })
  })
}

function addSongComponent($ul, id, artist, title) {
  const $component = document.createElement('li');
  $component.classList.add('trend__item');
  $component.setAttribute('id', `song_id${id}`);
  $component.innerHTML = `
    <a class="trend__author" href="/author/${artist}">${artist}</a>
    <a href="/song/${artist} - ${title}"><span class="trend__song">${artist} - ${title}</span></a>
  `;
  $ul.appendChild($component);
}
