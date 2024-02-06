window.addEventListener("DOMContentLoaded", () => {
  const helpIcon = document.querySelector(".help");
  const infoDiv = document.querySelector(".information");

  helpIcon.addEventListener("click", () => {
    infoDiv.style.display =
      infoDiv.style.display === "none" ? "inherit" : "none";
  });
});
