async function addDeviceApi(deviceData) {
  const response = await fetch("api/add_device.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      name: deviceData.name,
      type: deviceData.type,
      status: "off",
      room_id: deviceData.room_id,
      electricity: parseFloat(deviceData.electricity) || 0,
      gas: parseFloat(deviceData.gas) || 0,
      water: parseFloat(deviceData.water) || 0
    })
  });
  
  return await response.json();
}
