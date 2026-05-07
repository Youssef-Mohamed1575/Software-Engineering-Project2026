function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  if (sidebar) {
    sidebar.classList.toggle("-translate-x-full");
  }
}

function hideSidePanel(role) {
  const userPanelBtn = document.getElementById('userPanelBtn');
  const activityLogBtn = document.getElementById('activityLogBtn');

  if (userPanelBtn) {
    if (window.hasPermission(role, window.PERMISSIONS.VIEW_ADMIN_PANEL)) {
      userPanelBtn.classList.remove('hidden');
    } else {
      userPanelBtn.classList.add('hidden');
    }
  }

  if (activityLogBtn) {
    if (window.hasPermission(role, window.PERMISSIONS.VIEW_ACTIVITY_LOG)) {
      activityLogBtn.classList.remove('hidden');
    } else {
      activityLogBtn.classList.add('hidden');
    }
  }
}

// Close sidebar on click outside
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
window.hideSidePanel = hideSidePanel;
