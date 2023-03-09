import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";
import { uploadFile } from "../functions/uploadFile";
import { loggedIn } from "../functions/loggedIn";

document.addEventListener("DOMContentLoaded", () => {
  // has partners component
  if(!document.querySelector(".partners__content")) return;
  loggedIn()
    .then(() => {
      main();
    })
    .catch(() => {
      main();
    });
});

function main() {
  const $addPartner = document.querySelector('#add-new-partner');
  loadPartners();
  if (vars.loggedIn !== true) {
    $addPartner.style.display = 'none';
  }
  else {
    // 'add' button
    $addPartner.querySelector('button[name="add-partner"]').addEventListener('click', () => {
      const text = $addPartner.querySelector('textarea').value;
      fetchApi(vars.apiAdminUrl, {op: 'add_partner', text: text}).then((data) => {
        if(data.status == 'OK') {
          loadPartners();
        }
      });
    })
    // file uploading
    $addPartner.querySelector('#files').addEventListener('change', (e) => {
      const files = e.target.files;
      if(files.length === 0) return;
      const file = files[0];
      const data = new FormData();
      data.append('image', file);
      data.append('upload_partner_icon', true);
      uploadFile(vars.apiAdminUrl, data).then(data => {
        $addPartner.querySelector('img').src = './img/tmp/partner.png?t=' + new Date().getTime();
        console.log(data);
      });
    });
  }
}

function loadPartners() {
  const $partners = document.querySelector(".partners__content");
  const $ul = $partners.querySelector('.partners__list');
  $ul.innerHTML = "";
  fetchApi(vars.apiUserUrl, { op: "get_partners" }).then((data) => {
    data?.msg.forEach((item) => {
      const img = `img/partners/partner_${item.partner_id}.png`;
      addPartnerComponent($ul, item.partner_id, item.text, img);
    });
  });
}

function removePartner(id) {
  fetchApi(vars.apiAdminUrl, { op: "remove_partner", partner_id: id}).then((data) => {
    if(data.status == 'OK') {
      loadPartners();
    }
  });
}

function addPartnerComponent($parent, id, text, img) {
  let $component = document.createElement('li');
  $component.classList.add('partners__item');
  $component.setAttribute('id', `partner_id${id}`);
  $component.innerHTML = `
    <div class="partners__image">
      <img loading="lazy" src="${img}" class="image" width="90" height="90" alt="" aria-hidden="true">
    </div>
    <span class="partners__text">${text}</span>
  `;
  $parent.appendChild($component);
  if(vars.loggedIn) {
    $component.classList.add('removable');
    $component.addEventListener('click', () => {
      removePartner(id);
    });
  }
}
