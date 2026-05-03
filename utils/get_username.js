export default async function getCurrentUsername() {
  try {
    const res = await fetch('api/check_session.php',);

    const data = await res.json();

    if (data.loggedIn && data.user) {
      return data.user.username;
    }

    return null;
  } catch (err) {
    console.error(err);
    return null;
  }
}

