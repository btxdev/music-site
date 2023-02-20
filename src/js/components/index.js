import vars from "../_vars.js";
import { loggedIn } from "../functions/loggedIn";

const usedInComponents = ["/", "index", ""];
const currentFilename = window.location.pathname.split("/").pop().split(".")[0];

if (usedInComponents.includes(currentFilename)) {
  document.addEventListener("DOMContentLoaded", main);
}

function main() {
  loggedIn()
  .then(() => {
    const $ul = document.body.querySelector('.header__container nav ul');
    const $li = document.createElement('li');
    $li.classList.add('nav__item');
    const $a = document.createElement('a');
    $a.classList.add('nav__link');
    $a.setAttribute('href', 'categories');
    $ul.appendChild($li);
    $li.appendChild($a);
    $a.textContent = '(Edit categories)';
  })
  .catch(() => {});
}
