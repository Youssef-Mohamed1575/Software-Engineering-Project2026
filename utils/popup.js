function popup(type) {
  if (type === "device") {
    document.getElementById("device_popup").classList.remove("hidden");
    document.getElementById("overlay").classList.remove("hidden");
  } else if (type === "room") {
    document.getElementById("room_popup").classList.remove("hidden");
    document.getElementById("overlay").classList.remove("hidden");
  } else if (type === "room_edit") {
    document.getElementById("room_edit").classList.remove("hidden");
    document.getElementById("overlay").classList.remove("hidden");
  } else if (type === "device_edit") {
    document.getElementById("device_edit").classList.remove("hidden");
    document.getElementById("overlay").classList.remove("hidden");
  }
}

function closePopup(type) {
  if (type === "device") {
    document.getElementById("device_popup").classList.add("hidden");
    document.getElementById("overlay").classList.add("hidden");
    document.getElementById("device_name").value = "";
    document.getElementById("device_name").placeholder =
      " Enter device name";
    document.getElementById("device_type").selectedIndex = 0;
    document.getElementById("device_electricity").value = "";
    document.getElementById("device_gas").value = "";
    document.getElementById("device_water").value = "";
    document.getElementById("device_type_placeholder").innerHTML =
      "Select device type";
    document
      .getElementById("device_type")
      .classList.remove("border-2", "border-red-500", "text-red-500");
    document
      .getElementById("device_type_placeholder")
      .classList.add("text-gray-400");
    document
      .getElementById("device_name")
      .classList.remove(
        "border-2",
        "border-red-500",
        "placeholder-red-500"
      );
  } else if (type === "room") {
    document.getElementById("room_popup").classList.add("hidden");
    document.getElementById("overlay").classList.add("hidden");
    document
      .getElementById("room_name")
      .classList.remove(
        "border-2",
        "border-red-500",
        "placeholder-red-500"
      );
    document.getElementById("room_name").placeholder = " Enter room name";
  } else if (type === "room_edit") {
    document.getElementById("room_edit").classList.add("hidden");
    document.getElementById("overlay").classList.add("hidden");
  } else if (type === "device_edit") {
    document.getElementById("device_edit").classList.add("hidden");
    document.getElementById("overlay").classList.add("hidden");
  }
}
