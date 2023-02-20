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
      const id = item.cat_id;
      const category = item.title;
      const link = item.link;
      addCategoryComponent($ul, id, category, link);
    });
  });
}

function removeCategory(id) {
  fetchApi(vars.apiAdminUrl, { op: "remove_category", cat_id: id}).then((data) => {
    if(data.status == 'OK') {
      loadCategories();
    }
  });
}

function saveCategory(id) {
  const $container = document.querySelector('#category_id' + String(id));
  const title = $container.querySelector('input[name=category-edit]').value;
  const link = $container.querySelector('input[name=link-edit]').value;
  const data = {
    op: "edit_category",
    cat_id: id,
    title: title,
    link: link
  };
  fetchApi(vars.apiAdminUrl, data).then((data) => {
    if(data.status == 'OK') {
      loadCategories();
    }
  });
}

function addCategoryComponent($parent, id, text, link) {
  const $component = document.createElement('li');
  $component.classList.add('trend__item');
  $component.setAttribute('id', `category_id${id}`);
  $component.style.border = '1px solid black';
  $component.style.padding = '10px';
  if(vars.loggedIn) {
    $component.innerHTML = `
      <a href="category/${link}"><span class="trend__song">${text}</span></a>
      <label class="submit__label">
        <span class="submit__label-text">Category name</span>
        <input
          value="${text}"
          class="submit__input input-reset"
          type="text"
          name="category-edit"
          required
        />
      </label>
      <label class="submit__label">
        <span class="submit__label-text">Category URL</span>
        <input
          value="${link}"
          class="submit__input input-reset"
          type="text"
          name="link-edit"
          required
        />
      </label>
    `;
    const $btnSave = document.createElement('button');
    $btnSave.classList.add('btn-reset', 'bottom-line');
    $btnSave.setAttribute('name', 'save-category');
    $btnSave.innerText = 'Save';
    const $btnRemove = document.createElement('button');
    $btnRemove.classList.add('btn-reset', 'bottom-line');
    $btnRemove.setAttribute('name', 'remove-category');
    $btnRemove.innerText = 'Remove';
    $component.appendChild(document.createElement('br'));
    $component.appendChild(document.createElement('br'));
    $component.appendChild($btnSave);
    $component.appendChild(document.createElement('br'));
    $component.appendChild(document.createElement('br'));
    $component.appendChild($btnRemove);
    $component.appendChild(document.createElement('br'));
    $btnSave.addEventListener('click', () => {
      saveCategory(id);
    });
    $btnRemove.addEventListener('click', () => {
      removeCategory(id);
    });
  }
  else {
    $component.innerHTML = `
      <a href="category/${link}"><span class="trend__song">${text}</span></a>
    `;
  }
  $parent.appendChild($component);
}
