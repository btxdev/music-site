import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";
import { uploadFile } from "../functions/uploadFile";
import { loggedIn } from "../functions/loggedIn";

const usedInComponents = ["/", "index", "", "trending", "tracks"];
const currentFilename = window.location.pathname.split("/").pop().split(".")[0];

if (usedInComponents.includes(currentFilename)) {
  document.addEventListener("DOMContentLoaded", () => {
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
      $ul.innerHTML += partnerComponent(item.partner_id, item.text, img);
    });
  });
}

function partnerComponent(id, text, img) {
  return `
    <li class="partners__item" id="partner_id${id}">
      <div class="partners__image">
        <img loading="lazy" src="${img}" class="image" width="90" height="90" alt="" aria-hidden="true">
      </div>
      <span class="partners__text">${text}</span>
    </li>
  `;
}
