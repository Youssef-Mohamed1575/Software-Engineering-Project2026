    function updateUsageTable() {
      fetch("api/dailyUsage.php", {
        method: "GET",
        credentials: "same-origin",
        cache: "no-store"
      })
        .then(res => res.json())
        .then(data => {
          console.log("Usage table refreshed:", data);

        })
        .catch(err => {
          console.error("Usage update failed:", err);
        });
    }