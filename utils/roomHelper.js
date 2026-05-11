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

function validateRoomEditInputs(roomData) {
  if (!roomData.name.trim()) {
    const roomNameInput = document.getElementById("edit_room_name");
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

function resetEditRoomFormInputs() {
  document.getElementById("edit_room_name").value = "";
  document.getElementById("edit_room_electricity").value = "";
  document.getElementById("edit_room_gas").value = "";
  document.getElementById("edit_room_water").value = "";
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

function mapRoomsAndDevices(roomsData, devicesData) {
  const mappedRooms = roomsData.map(room => ({
    id: room.id,
    name: room.name,
    devices: [],
    electricityLimit: room.electricity_limit || 0,
    gasLimit: room.gas_limit || 0,
    waterLimit: room.water_limit || 0
  }));

  devicesData.forEach(device => {
    const matchingRoom = mappedRooms.find(room => room.id == device.room_id);
    if (matchingRoom) {
      matchingRoom.devices.push({
        id: device.id,
        name: device.name,
        type: device.type,
        status: device.status === "on",
        electricity: device.electricity,
        gas: device.gas,
        water: device.water
      });
    }
  });

  return mappedRooms;
}

function openEditRoomPopup(roomIndex) {
  editingRoomIndex = roomIndex;

  const room = rooms[roomIndex];

  document.getElementById("edit_room_name").value = room.name;
  document.getElementById("edit_room_electricity").value =
    room.electricityLimit;
  document.getElementById("edit_room_gas").value = room.gasLimit;
  document.getElementById("edit_room_water").value = room.waterLimit;

  popup("room_edit");
}
