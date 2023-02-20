import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";
import { loggedIn } from "../functions/loggedIn";

const usedInComponents = ["categories"];
const currentFilename = window.location.pathname.split("/").pop().split(".")[0];

if (usedInComponents.includes(currentFilename)) {
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
  const $addCategory = document.querySelector('section.trend .container');
  loadCategories();
  if (vars.loggedIn !== true) {
    document.querySelector('.add-category').style.display = 'none';
  }
  else {
    // 'add' button
    $addCategory .querySelector('button[name="add-category"]').addEventListener('click', () => {
      const title = $addCategory.querySelector('input[name=category]').value;
      const link = $addCategory.querySelector('input[name=link]').value;
      fetchApi(vars.apiAdminUrl, {op: 'add_category', title: title, link: link}).then((data) => {
        if(data.status == 'OK') {
          loadCategories();
        }
      });
    })
  }
}

function loadCategories() {
  const $categories = document.querySelector("section.trend .container");
  const $ul = $categories.querySelector('.trend__list');
  $ul.innerHTML = "";
  fetchApi(vars.apiUserUrl, { op: "get_categories" }).then((data) => {
    data?.msg.forEach((item) => {
      const id = item.category_id;
      const category = item.title;
      const link = item.link;
      addCategoryComponent($ul, id, category, link);
    });
  });
}

function removeCategory(id) {
  fetchApi(vars.apiAdminUrl, { op: "remove_category", category_id: id}).then((data) => {
    if(data.status == 'OK') {
      loadCategories();
    }
  });
}

function addCategoryComponent($parent, id, text, link) {
  const $component = document.createElement('li');
  $component.classList.add('trend__item');
  $component.setAttribute('id', `category_id${id}`);
  $component.innerHTML = `
    <a href="category/${link}"><span class="trend__song">${text}</span></a>
  `;
  if(vars.loggedIn) {
    const $a = document.createElement('a');
    $a.innerText = 'remove category';
    $component.prepend($a);
    $a.classList.add('removable');
    $a.addEventListener('click', () => {
      removeCategory(id);
    });
  }
  $parent.appendChild($component);
}
