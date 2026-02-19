
window.addEventListener("DOMContentLoaded", function () {
  // sticky header on scroll
  window.addEventListener("scroll", function () {
    const headerview = document.querySelector("header");
    headerview.classList.toggle("sticky", window.scrollY > 0);
  });

  // mobile menu pop up
  const menuBtn = document.querySelector(".nav-menu-btn");
  const closeBtn = document.querySelector(".nav-close-btn");
  const navigation = document.querySelector(".navigation");

  if (menuBtn && closeBtn && navigation) {
    menuBtn.addEventListener("click", () => {
      navigation.classList.add("active");
    });

    closeBtn.addEventListener("click", () => {
      navigation.classList.remove("active");
    });

    //click outside of the frame to close mobile menu
    navigation.addEventListener("click", (e) => {
      if (e.target === navigation) {
        navigation.classList.remove("active");
      }
    });
  }
});



