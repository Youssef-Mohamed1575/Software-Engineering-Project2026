async function checkSession() {
  try {
    const res = await fetch('api/check_session.php');
    return await res.json();
  } catch (err) {
    console.error('Session check failed:', err);
    return { loggedIn: false };
  }
}

window.checkSession = checkSession;
