const inputFile = document.querySelector(".content-file-input");
const clearFiles = document.querySelector(".content-file-close");

if (inputFile) {
  inputFile?.addEventListener('change', function(e) {

    let fileName = '';
    let elValue = inputFile.nextElementSibling.innerHTML;


    if (this.files && this.files.length > 1) {
      fileName = (this.getAttribute("data-count") || '').replace("{count}", this.files.length);
      document.querySelector(".content-file-icon").classList.add("is-hidden");
      document.querySelector(".content-file-close").classList.remove("is-hidden");
    } else {
      fileName = this.files[0]?.name;
      document.querySelector(".content-file-icon").classList.add("is-hidden");
      document.querySelector(".content-file-close").classList.remove("is-hidden");
    }

    if (fileName) {
      inputFile.nextElementSibling.innerHTML = fileName;
    } else {
      inputFile.nextElementSibling = elValue;
      document.querySelector(".content-file-icon").classList.remove("is-hidden");
      document.querySelector(".content-file-close").classList.add("is-hidden");
    }
  });
}

if (clearFiles) {
  clearFiles?.addEventListener("click", (e) => {
    files.length = 0;
    inputFile.nextElementSibling.innerHTML = "Upload icon";
    document.querySelector(".content-file-icon").classList.remove("is-hidden");
    document.querySelector(".content-file-close").classList.add("is-hidden");
  });
}
