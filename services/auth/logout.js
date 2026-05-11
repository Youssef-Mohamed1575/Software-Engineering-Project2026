async function logoutUser() {
    try {
        const response = await fetch("api/logout.php", {
            method: "POST",
            credentials: "same-origin",
            cache: "no-store"
        });

        const result = await response.json();

        if (result.success) {
            sessionStorage.setItem("justLoggedOut", "true");
            localStorage.clear();
            sessionStorage.removeItem("user");
            window.location.replace("index.html");
        }

    } catch (error) {
        console.error("Logout failed:", error);
    }
}
