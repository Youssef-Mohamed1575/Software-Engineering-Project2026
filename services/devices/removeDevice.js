async function removeDeviceApi(deviceId) {
  const response = await fetch("api/remove_device.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      device_id: deviceId
    })
  });

  return await response.json();
}
