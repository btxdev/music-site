import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";
import { loggedIn } from "../functions/loggedIn";

const isSong = window.location.pathname.split("/")[1] == 'song';
const currentFilename = window.location.pathname.split("/").pop().split(".")[0];
const url = decodeURI(currentFilename);

const $artist = document.querySelector('.lyrics__artist');
const $track = document.querySelector('.lyrics__track');
const $remove = document.querySelector('.lyrics__remove');
const $lyrics = document.querySelector('.lyrics__inner');
const $authors = document.querySelector('.lyrics__authors');
const $prev = document.querySelector('.lyrics__prev');
const $next = document.querySelector('.lyrics__next');

if (isSong) {
  document.addEventListener("DOMContentLoaded", function() {
    loggedIn()
    .then(() => {
      main();
    })
    .catch(() => {
      main();
    });
  });
}

function main() {
  document.title = url + ' Lyrics — Toklyrics';
  fetchApi(vars.apiUserUrl, {op: 'get_song_data', url: url}).then((data) => {
    if(data.status == 'OK') {
      const song = data.msg;

      $artist.innerText = song.artist;
      $artist.setAttribute('href', `/artist/${song.artist}`);

      if (vars.loggedIn === true) {
        $remove.style.display = 'block';
        $remove.addEventListener('click', function() {
          removeSong(song.song_id);
        });
      }

      $track.innerText = `${song.artist} - ${song.title}`;

      $lyrics.innerHTML = '<span data-aos="fade-up" data-aos-once="true">Lyrics</span>';
      // $lyrics.innerHTML += song.lyrics.replace(/\n/g, "<br />");
      const lines = song.lyrics.split('\n');
      for(const line of lines) {
        const $line = document.createElement('p');
        $line.setAttribute('class', 'lyrics__text');
        $line.setAttribute('data-aos', 'fade-up');
        $line.setAttribute('data-aos-once', 'true');
        $line.innerText = line;
        $lyrics.appendChild($line);
      }

      $authors.innerHTML = '';
      $authors.innerHTML += `<a class="lyrics__authors-link" href="/artist/${song.artist}">${song.artist.toUpperCase()}</a>`;
      $authors.innerHTML += `<a class="lyrics__authors-link" href="/song/${song.artist} - ${song.title}">${song.title.toUpperCase()}</a>`;
      $authors.innerHTML += `<a class="lyrics__authors-link">${song.album.toUpperCase()}</a>`;

      if(song.prev_song.length > 0) {
        $prev.innerText = song.prev_song;
        $prev.setAttribute('href', '/song/' + song.prev_song);
      }
      else {
        $prev.style.visibility = 'hidden';
      }
      if(song.next_song.length > 0) {
        $next.innerText = song.next_song;
        $next.setAttribute('href', '/song/' + song.next_song);
      }
      else {
        $next.style.visibility = 'hidden';
      }

    }
    else {
      window.location.replace('/');
    }
  })
  .catch(error => {
    window.location.replace('/');
  });
}

function removeSong(id) {
  fetchApi(vars.apiAdminUrl, {op: 'remove_song', song_id: id}).then((data) => {
    if(data.status == 'OK') {
      window.history.go(-1);
    }
  });
}
