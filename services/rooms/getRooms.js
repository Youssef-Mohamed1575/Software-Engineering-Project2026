async function getRoomsApi() {
  const response = await fetch("api/get_rooms.php");
  return await response.json();
}
