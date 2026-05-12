    async function loadTotalDevices() {
      try {
        const response = await fetch("api/get_total_devices.php");
        const result = await response.json();

        if (!result.success) {
          console.error(result.message);
          return;
        }

        document.getElementById("total_devices").innerText =
          result.total_devices;

      } catch (error) {
        console.error("Failed to load total devices:", error);
      }
    }