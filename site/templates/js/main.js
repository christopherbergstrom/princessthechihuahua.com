window.onload = () => {
  console.log("loaded");
  let loader = document.querySelector(".loader");
  window.setTimeout(() => {
    loader.classList.add("uk-animation-fade");
    loader.classList.add("uk-animation-reverse");
    window.setTimeout(() => {
      loader.style.display = "none";
    }, 500);
  }, 3000);
}