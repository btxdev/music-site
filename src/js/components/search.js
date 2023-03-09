const inputHero = document.querySelector('.hero__input');

if (inputHero) {
  inputHero?.addEventListener('input', (e) => {
    if (e.target.value.length > 0) {
      document.querySelector('.hero__dropdown').classList.add('hero__dropdown--active');
    } else {
      document.querySelector('.hero__dropdown').classList.remove('hero__dropdown--active');
    }
  });
}

