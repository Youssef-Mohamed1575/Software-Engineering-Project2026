async function editDeviceApi(deviceData) {
  const response = await fetch("api/edit_device.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      device_id: deviceData.id,
      name: deviceData.name,
      type: deviceData.type,
      electricity: parseFloat(deviceData.electricity) || 0,
      gas: parseFloat(deviceData.gas) || 0,
      water: parseFloat(deviceData.water) || 0
    })
  });

  return await response.json();
}
