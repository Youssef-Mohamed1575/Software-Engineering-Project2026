async function removeRoomApi(roomId) {
    const response = await fetch("api/remove_room.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            room_id: roomId
        })
    });

    return await response.json();
}

window.removeRoomApi = removeRoomApi;