function getDeviceInputs(roomId) {
  return {
    name: document.getElementById("device_name").value,
    type: document.getElementById("device_type").value,
    electricity: document.getElementById("device_electricity").value,
    gas: document.getElementById("device_gas").value,
    water: document.getElementById("device_water").value,
    status: false,
    active_minutes: 0,
    lastActivatedTime: null,
    room_id: roomId
  };
}
