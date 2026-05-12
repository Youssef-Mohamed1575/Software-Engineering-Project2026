    function checkDailyReset() {
      const today = new Date().toISOString().split("T")[0];
      const lastReset = localStorage.getItem("lastDailyReset");

      if (lastReset !== today) {
        return fetch("api/dailyUsage.php", {
          method: "GET",
          credentials: "same-origin",
          cache: "no-store"
        })
          .then(res => res.json())
          .then(data => {
            console.log("Daily reset completed:", data);

            if (data.success) {
              localStorage.setItem("lastDailyReset", today);
            }
          })
          .catch(err => {
            console.error("Daily reset failed:", err);
          });
      }

      return Promise.resolve();
    }