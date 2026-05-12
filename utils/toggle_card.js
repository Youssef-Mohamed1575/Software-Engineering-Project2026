function toggleCard(card, type) {
      const change = card.querySelector(".killBtn");

      const messages = {
        Light: "Turning OFF<br>all lights",
        AC: "Turning OFF<br>all ACs",
        Heater: "Turning OFF<br>all heaters",
        devices: "Turning OFF<br>all devices",
      };

      const finalMessages = {
        Light: "All lights are<br>OFF",
        AC: "All ACs are<br>OFF",
        Heater: "All heaters are<br>OFF",
        devices: "All devices are<br>OFF",
      };
      if (card.classList.contains("processing")) return;

      card.classList.add("processing");
      const originalBg = card.className;
      const originalTextColor = change.className;
      change.classList.add("opacity-0", "scale-95");

      setTimeout(() => {
        card.classList.remove("bg-slate-700");
        card.classList.add("bg-red-500");

        change.classList.remove("text-slate-200");
        change.classList.add("text-black", "font-bold");
        change.style.fontFamily = "Yellowtail, cursive";

        change.innerHTML = messages[type];

        change.classList.remove("opacity-0", "scale-95");
      }, 150);

      setTimeout(() => {
        change.classList.add("opacity-0", "scale-95");

        setTimeout(() => {
          card.classList.remove("bg-red-500");
          card.classList.add("bg-slate-700");

          change.classList.remove("text-black");
          change.classList.add("text-slate-200");

          change.style.fontFamily = "";

          change.innerHTML = finalMessages[type];

          change.classList.remove("opacity-0", "scale-95");

          card.classList.remove("processing");
        }, 150);
      }, 1000);
      turnOffDevicesByType(type);
    }
