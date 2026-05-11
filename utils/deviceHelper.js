function validateDeviceInputs(deviceData) {
  if (!deviceData.name.trim()) {
    const deviceNameInput = document.getElementById("device_name");
    deviceNameInput.value = "";
    deviceNameInput.placeholder = "Please fill this field";
    deviceNameInput.classList.add(
      "border-2",
      "border-red-500",
      "placeholder-red-500"
    );
    return false;
  }

  if (deviceData.type === "Select device type") {
    const deviceTypeSelect = document.getElementById("device_type");
    deviceTypeSelect.selectedIndex = 0;
    deviceTypeSelect.classList.add(
      "border-2",
      "border-red-500",
      "text-red-500"
    );
    document.getElementById("device_type_placeholder").innerHTML =
      "Please select device type";
    return false;
  }
  
  return true;
}

function generateDeviceHTML(currentRoomIndex, currentDeviceIndex, deviceName) {
  return `
  <div class="grid grid-cols-[3fr_1fr_1fr_1fr] gap-4 items-center bg-slate-600 p-4 rounded-lg">

      <div class="text-white font-semibold"
           id="device_title_${currentRoomIndex}_${currentDeviceIndex}">
          ${deviceName}
      </div>

      <button
          id="device_toggle_${currentRoomIndex}_${currentDeviceIndex}"
          onclick="toggleDeviceStatus(${currentRoomIndex}, ${currentDeviceIndex})"
          class="bg-yellow-500 hover:bg-green-400 text-white px-2 py-1 rounded">
          OFF
      </button>

      <button
          onclick="openEditDevicePopup(${currentRoomIndex}, ${currentDeviceIndex})"
          class="bg-blue-500 hover:bg-blue-400 text-white px-2 py-1 rounded">
          Edit
      </button>

      <button
          onclick="removeDevice(${currentRoomIndex}, ${currentDeviceIndex})"
          class="bg-red-500 hover:bg-red-400 text-white px-2 py-1 rounded">
          Remove
      </button>

  </div>
  `;
}

function resetDeviceFormInputs() {
  document.getElementById("device_name").value = "";
  document.getElementById("device_name").placeholder = " Enter device name";

  document.getElementById("device_type").selectedIndex = 0;
  document.getElementById("device_type_placeholder").innerHTML = "Select device type";

  document.getElementById("device_electricity").value = "";
  document.getElementById("device_gas").value = "";
  document.getElementById("device_water").value = "";

  document.getElementById("device_name").classList.remove(
    "border-2",
    "border-red-500",
    "placeholder-red-500"
  );

  document.getElementById("device_type").classList.remove(
    "border-2",
    "border-red-500",
    "text-red-500"
  );
}
