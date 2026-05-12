    function loadBillingDashboard() {
      fetch("api/ResourceManagment.php", {
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

          document.getElementById("estimatedBill").textContent =
            data.billing.projected_month_bill + " EGP";

          document.getElementById("currentBill").textContent =
            data.billing.current_bill + " EGP";

          document.getElementById("lastMonthBill").textContent =
            data.billing.last_month_bill + " EGP";

          const changeEl = document.getElementById("changePercent");
          const change = data.billing.change_percent;

          changeEl.textContent =
            (change >= 0 ? "↑ +" : "↓ ") + change + "%";

          changeEl.style.color =
            change >= 0 ? "red" : "limegreen";

          if (window.billingPieChartInstance) {
            window.billingPieChartInstance.destroy();
          }

          window.billingPieChartInstance = new Chart(
            document.getElementById("billingPie"),
            {
              type: "pie",
              data: {
                labels: ["Electricity", "Gas", "Water"],
                datasets: [
                  {
                    data: [
                      data.distribution.electricity,
                      data.distribution.gas,
                      data.distribution.water
                    ],
                    backgroundColor: [
                      "#facc15",
                      "#22c55e",
                      "#3b82f6"
                    ],
                    borderWidth: 0
                  }
                ]
              },
              plugins: [ChartDataLabels],
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    position: "right",
                    labels: {
                      color: "#e5e7eb"
                    }
                  },
                  datalabels: {
                    color: "#fff",
                    font: {
                      weight: "bold",
                      size: 12
                    },
                    formatter: function (value) {
                      return value.toFixed(1) + "%";
                    }
                  }
                }
              }
            }
          );
        })
        .catch(error => {
          console.error("Failed to load billing dashboard:", error);
        });
    }