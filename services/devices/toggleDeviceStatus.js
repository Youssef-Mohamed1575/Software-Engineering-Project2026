async function toggleDeviceStatusApi(deviceId, newStatus) {
  const response = await fetch("api/toggle_device_status.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      device_id: deviceId,
      status: newStatus ? "on" : "off"
    })
  });

  return await response.json();
}
