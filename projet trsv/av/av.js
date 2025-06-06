// Smooth scroll for anchor links
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => { // Select all anchor links
    anchor.addEventListener("click", function (e) {
      e.preventDefault();

      const target = document.querySelector(this.getAttribute("href"));
      if (target) { // if targer element is found it'll scroll to it
        window.scrollTo({
          top: target.offsetTop - 60, // adjust the height of nav height if needed
          behavior: "smooth"
        });
      }
    });
  });
});
