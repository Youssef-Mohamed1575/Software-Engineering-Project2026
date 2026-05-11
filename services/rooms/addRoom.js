async function addRoomApi(roomData) {
  const response = await fetch("api/add_room.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      name: roomData.name
    })
  });

  return await response.json();
}
