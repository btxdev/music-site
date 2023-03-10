import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";

const isArtist = window.location.pathname.split("/")[1] == 'artist';
const currentFilename = window.location.pathname.split("/").pop().split(".")[0];
const url = decodeURI(currentFilename);

const $title = document.querySelector('.trend__title');
const $ul = document.querySelector('.trend__list');

if (isArtist) {
  document.addEventListener("DOMContentLoaded", main);
}

function main() {
  document.title = url + ' — Lyrics — Toklyrics';
  $title.innerText = url;
  loadSongs();
}

function loadSongs() {
  $ul.innerHTML = "";
  fetchApi(vars.apiUserUrl, {
    op: "get_artist_data",
    artist: url
  })
  .then((data) => {
    if(data.status == 'EMPTY') {
      document.location.replace('/');
    }
    if(data.status != 'OK') {
      return;
    }
    const songs = data.msg;
    for(const song of songs) {
      addSongComponent($ul, song.song_id, song.artist, song.title);
    }
  })
}

function addSongComponent($ul, id, artist, title) {
  const $component = document.createElement('li');
  $component.classList.add('trend__item');
  $component.setAttribute('id', `song_id${id}`);
  $component.innerHTML = `
    <a class="trend__author" href="/artist/${artist}">${artist}</a>
    <a href="/song/${artist} - ${title}"><span class="trend__song">${artist} - ${title}</span></a>
  `;
  $ul.appendChild($component);
}
