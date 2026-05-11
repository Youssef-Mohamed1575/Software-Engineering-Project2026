function validateRoomInputs(roomData) {
  if (!roomData.name.trim()) {
    const roomNameInput = document.getElementById("room_name");
    roomNameInput.value = "";
    roomNameInput.placeholder = "Please fill this field";
    roomNameInput.classList.add(
      "border-2",
      "border-red-500",
      "placeholder-red-500"
    );
    return false;
  }
  return true;
}

function generateRoomHTML(roomIndex, roomName) {
  return `
  <div id="room_card_${roomIndex}" class="bg-slate-700 rounded-xl p-6 mb-6">
      
      <div class="flex justify-between items-center mb-4">
          
          <h2 id="room_${roomIndex}" class="text-white text-2xl font-bold">
              ${roomName}
          </h2>

          <div class="flex gap-3">
              <button onclick="openDevicePopup(${roomIndex})"
                  class="bg-emerald-500 hover:bg-emerald-400 text-white px-4 py-2 rounded">
                  Add Device
              </button>

              <button onclick="openEditRoomPopup(${roomIndex})"
                  class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded">
                  Edit
              </button>
          </div>
      </div>

      <div id="device_container_${roomIndex}" class="space-y-3"></div>
  </div>
  `;
}

function resetRoomFormInputs() {
  document.getElementById("room_name").value = "";
  document.getElementById("room_electricity").value = "";
  document.getElementById("room_gas").value = "";
  document.getElementById("room_water").value = "";
}

function renderRooms() {
  const roomsContainer = document.getElementById("rooms_container");
  roomsContainer.innerHTML = "";

  rooms.forEach((room, roomIndex) => {
    const roomHTML = generateRoomHTML(roomIndex, room.name);
    roomsContainer.insertAdjacentHTML("beforeend", roomHTML);
    refreshRoomDevices(roomIndex);
  });
}
