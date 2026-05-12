    async function turnOffDevicesByType(type) {
      try {

        const response = await fetch("api/turn_off_devices.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            type: type
          })
        });

        const result = await response.json();

        if (!result.success) {
          Notifications.add({
            title: 'Error',
            message: result.message || 'Failed to turn off devices.',
            type: 'alert'
          });
          return;
        }

        Notifications.add({
          title: "Success",
          message:
            type === "devices"
              ? "All devices have been turned off successfully."
              : `All ${type} devices have been turned off successfully.`,
          type: "success"
        });

      } catch (error) {
        console.error("Failed to turn off devices:", error);
      }
    }