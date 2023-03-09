import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";

const inputHero = document.querySelector('.hero__input');
const inputDropdown = document.querySelector('.hero__dropdown');

if (inputHero) {
  inputHero?.addEventListener('input', (e) => {
    const query = e.target.value;

    if (e.target.value.length > 0) {
      inputDropdown.classList.add('hero__dropdown--active');
    }
    else {
      inputDropdown.classList.remove('hero__dropdown--active');
      return;
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

