function getRoomInputs() {
  return {
    name: document.getElementById("room_name").value,
    electricityLimit: document.getElementById("room_electricity").value,
    gasLimit: document.getElementById("room_gas").value,
    waterLimit: document.getElementById("room_water").value
  };
}
