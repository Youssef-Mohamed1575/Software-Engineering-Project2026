async function editRoomApi(roomData) {
  const response = await fetch("api/edit_room.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      room_id: roomData.id,
      name: roomData.name,
      electricity: roomData.electricityLimit,
      gas: roomData.gasLimit,
      water: roomData.waterLimit
    })
  });

  return await response.json();
}
