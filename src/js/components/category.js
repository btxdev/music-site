import vars from "../_vars.js";
import { fetchApi } from "../functions/fetchApi";

const isCategory = window.location.pathname.split("/")[1] == 'category';
const currentFilename = window.location.pathname.split("/").pop().split(".")[0];

if (isCategory) {
  document.addEventListener("DOMContentLoaded", main);
}

function main() {

  const $title = document.querySelector('.trend__title');

  fetchApi(vars.apiUserUrl, {
    op: "get_category_data",
    url: currentFilename
  })
  .then((data) => {
    if(data.status == 'EMPTY') {
      document.location.replace('/categories.html');
    }
    console.log(data);
  })

}
