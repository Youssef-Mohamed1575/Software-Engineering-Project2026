window.checkRoomLimits = async function () {
    try {
        const response = await fetch("api/check_room_limits.php", {
            credentials: "same-origin",
            cache: "no-store"
        });

        const data = await response.json();

        if (!data.success || !data.alerts) return;

        data.alerts.forEach(alert => {
            const storageKey = `room_limit_${alert.room_id}_${alert.resource}`;

            if (!sessionStorage.getItem(storageKey)) {
                Notifications.add({
                    title: `${alert.resource.charAt(0).toUpperCase() + alert.resource.slice(1)} Limit Exceeded`,
                    message: `${alert.room_name} exceeded ${alert.resource} limit (${alert.total}/${alert.limit} ${alert.unit}).`,
                    type: "warning"
                });

                sessionStorage.setItem(storageKey, "true");
            }
        });

        // Remove flags if room no longer exceeds limits
        Object.keys(sessionStorage).forEach(key => {
            if (key.startsWith("room_limit_")) {
                const exists = data.alerts.some(alert =>
                    key === `room_limit_${alert.room_id}_${alert.resource}`
                );

                if (!exists) {
                    sessionStorage.removeItem(key);
                }
            }
        });

    } catch (error) {
        console.error("Room limit check failed:", error);
    }
};