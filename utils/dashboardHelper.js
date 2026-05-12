async function loadDashboardData() {
  try {
    const [roomsResult, devicesResult] = await Promise.all([
      getRoomsApi(),
      getDevicesApi()
    ]);

    if (!roomsResult.success || !devicesResult.success) {
      alert("Failed to load household data.");
      return;
    }

    devices = devicesResult.devices;
    rooms = mapRoomsAndDevices(roomsResult.rooms, devicesResult.devices);

    renderRooms();
    
    if (typeof checkRoomLimits === "function") {
      checkRoomLimits();
    }

  } catch (error) {
    console.error("Dashboard load error:", error);
    alert("Failed to load dashboard.");
  }
}
