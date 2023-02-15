import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";
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
  const $partners = document.querySelector(".partners__content");
  const $ul = $partners.querySelector(".partners__list");
  $ul.innerHTML = "";
  fetchApi(vars.apiUserUrl, { op: "get_partners" }).then((data) => {
    data?.msg.forEach((item) => {
      const img = `;media/partner_${item.partner_id}.jpg`;
      $ul.innerHTML += partnerComponent(item.partner_id, item.text, img);
    });
  });
  if (vars.loggedIn !== true) {
    document.querySelector('#add-new-partner').style.display = 'none';
  }
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
