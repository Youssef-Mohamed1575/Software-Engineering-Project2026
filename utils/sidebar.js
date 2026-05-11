function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  if (sidebar) {
    sidebar.classList.toggle("-translate-x-full");
  }
}

document.addEventListener("click", function (event) {
  const sidebar = document.getElementById("sidebar");
  const burgerBtn = event.target.closest("button");
  if (!sidebar) return;

  const isClickInsideSidebar = sidebar.contains(event.target);
  const isBurgerButton = burgerBtn && burgerBtn.getAttribute("onclick") === "toggleSidebar()";
  if (!sidebar.classList.contains("-translate-x-full") && !isClickInsideSidebar && !isBurgerButton) {
    sidebar.classList.add("-translate-x-full");
  }
});

window.toggleSidebar = toggleSidebar;
