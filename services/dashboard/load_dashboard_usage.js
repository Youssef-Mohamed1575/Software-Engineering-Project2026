    function loadDashboardUsage() {
      fetch("api/DashboardUsage.php", {
        method: "GET",
        credentials: "same-origin",
        cache: "no-store"
      })
        .then(response => response.json())
        .then(data => {
          if (!data.success) {
            console.error(data.message);
            return;
          }

          document.getElementById("electricityUsage").textContent =
            data.usage.electricity + " kWh";

          document.getElementById("gasUsage").textContent =
            data.usage.gas + " m³";

          document.getElementById("waterUsage").textContent =
            data.usage.water + " L";
        })
        .catch(error => {
          console.error("Failed to load dashboard usage:", error);
        });
    }