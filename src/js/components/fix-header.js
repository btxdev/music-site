const header = document?.querySelector('.header');
const headerHeight = header.offsetHeight;
const main = document.querySelector('.main');

window.addEventListener('scroll', () => {
  let scrollDistance = window.scrollY;

  if (scrollDistance >= headerHeight) {
    header.classList.add('header--fixed');
    main.style.marginTop = `${headerHeight}px`;
  } else {
    header.classList.remove('header--fixed');
    main.style.marginTop = null;
  }
});

const getHeaderHeight = () => {
  const headerHeight = document?.querySelector('.header').offsetHeight;
  document.querySelector(':root').style.setProperty('--header-height', `${headerHeight}px`);
}

getHeaderHeight()

window.addEventListener('resize', () => {
  getHeaderHeight();
});
