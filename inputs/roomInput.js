function getRoomInputs() {
  return {
    name: document.getElementById("room_name").value,
    electricityLimit: document.getElementById("room_electricity").value,
    gasLimit: document.getElementById("room_gas").value,
    waterLimit: document.getElementById("room_water").value
  };
}

function getRoomEditInputs() {
  return {
    name: document.getElementById("edit_room_name").value,
    electricityLimit: parseFloat(
      document.getElementById("edit_room_electricity").value
    ) || 0,
    gasLimit: parseFloat(
      document.getElementById("edit_room_gas").value
    ) || 0,
    waterLimit: parseFloat(
      document.getElementById("edit_room_water").value
    ) || 0
  };
}
