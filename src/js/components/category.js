import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";
import { loggedIn } from "../functions/loggedIn";

const isCategory = window.location.pathname.split("/")[1] == 'category';
const currentFilename = window.location.pathname.split("/").pop().split(".")[0];
const url = decodeURI(currentFilename);

const $title = document.querySelector('.trend__title');

let currentCategoryId = -1;

if (isCategory) {
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
  loadSongs();
  if (vars.loggedIn === true) {
    addSearchComponent();
  }
}

function addSearchComponent() {
  const $search = document.createElement('div');
  const $container = document.createElement('div');
  $container.innerHTML = `
    <label class="hero__label">
      <input class="hero__input input-reset" type="text" placeholder="Search">
      <div class="hero__dropdown">
        <a class="hero__dropdown-item" href="">
          <strong class="hero__dropdown-title">Heart heart</strong>
          <p class="hero__dropdown-descr">Convallis egestas lectus enim id non. Sed tempor dapibus viverra faucibus ultrices aliquet volutpat  tempor dapibus viverra faucibus</p>
        </a>
      </div>
    </label>
  `;
  $title.parentNode.insertBefore($container, $title.nextSibling);

  const inputHero = $container.querySelector('.hero__input');
  const inputDropdown = $container.querySelector('.hero__dropdown');

  inputHero?.addEventListener('input', (e) => {
    const query = e.target.value;

    if (e.target.value.length > 0) {
      inputDropdown.classList.add('hero__dropdown--active');
    }
    else {
      inputDropdown.classList.remove('hero__dropdown--active');
      return;
    }

    function addSong($parent, title, description, hidden = false) {
      const $songElement = document.createElement('a');
      if(hidden) {
        $songElement.style.visibility = 'hidden';
      }
      else {
        $songElement.setAttribute('href', 'song/' + title);
      }
      $songElement.innerHTML = `
        <strong class="hero__dropdown-title">${title}</strong>
        <p class="hero__dropdown-descr">${description}</p>
      `;
      $parent.appendChild($songElement);
    }

    fetchApi(vars.apiUserUrl, {op: 'search', query: query}).then((data) => {
      if(data.status == 'OK') {
        inputDropdown.innerHTML = '';
        // console.log(data);
        const songs = data.msg;
        if(songs.length == 0) {
          inputDropdown.innerHTML = '<p>No results found</p>';
          addSong(inputDropdown, 'bbbbbb', 'bbbbbbbb bbbbbbbb bbbbbbbbbb bbbbbbb bbbbbbbbb bbbbbb bbbbbbbb bbbbbbbbbbb bbbbbbbbb', true);
        }
        for(const song of songs) {
          const songName = song.artist + ' - ' + song.title;
          addSong(inputDropdown, songName, song.lyrics);
        }
      }
    });

  });

}

function loadSongs() {
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
    document.title = data.msg.title + ' :: Toklyrics';
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
    <a class="trend__author" href="/artist/${artist}">${artist}</a>
    <a href="/song/${artist} - ${title}"><span class="trend__song">${artist} - ${title}</span></a>
  `;
  $ul.appendChild($component);
}
