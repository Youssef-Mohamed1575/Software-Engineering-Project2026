async function getDevicesApi() {
  const response = await fetch("api/get_devices.php");
  return await response.json();
}
