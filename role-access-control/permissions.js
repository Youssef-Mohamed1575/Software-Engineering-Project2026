const ROLES = {
  HOME_OWNER: 'homeOwner',
  HOME_ADULT: 'homeAdult',
  HOME_KID: 'homeKid',
  GUEST: 'guest',
};

const PERMISSIONS = {
  VIEW_ADMIN_PANEL: 'view_admin_panel',
  ADD_ROOMS: 'add_rooms',
  ADD_DEVICES: 'add_devices',
  TOGGLE_DEVICES: 'toggle_devices',
  ADD_USERS: 'add_users',
  VIEW_ALL_ROOMS: 'view_all_rooms',
  TOGGLE_VACATION_MODE: 'toggle_vacation_mode',
  VIEW_ACTIVITY_LOG: 'view_activity_log',
};

const ROLE_PERMISSIONS = {
  [ROLES.HOME_OWNER]: [
    PERMISSIONS.VIEW_ADMIN_PANEL,
    PERMISSIONS.ADD_ROOMS,
    PERMISSIONS.ADD_DEVICES,
    PERMISSIONS.TOGGLE_DEVICES,
    PERMISSIONS.ADD_USERS,
    PERMISSIONS.VIEW_ALL_ROOMS,
    PERMISSIONS.TOGGLE_VACATION_MODE,
    PERMISSIONS.VIEW_ACTIVITY_LOG,
  ],
  [ROLES.HOME_ADULT]: [
    PERMISSIONS.VIEW_ALL_ROOMS,
    PERMISSIONS.TOGGLE_DEVICES,
    PERMISSIONS.TOGGLE_VACATION_MODE,
  ],
  [ROLES.HOME_KID]: [
    PERMISSIONS.VIEW_ALL_ROOMS,
  ],
  [ROLES.GUEST]: [
    PERMISSIONS.VIEW_ALL_ROOMS,
    PERMISSIONS.TOGGLE_DEVICES,
  ],
};

function hasPermission(role, permission) {
  const permissions = ROLE_PERMISSIONS[role];
  if (!permissions) {
    return false;
  }
  return permissions.includes(permission);
}

async function checkSession() {
  try {
    const res = await fetch('api/check_session.php');
    return await res.json();
  } catch (err) {
    console.error('Session check failed:', err);
    return { loggedIn: false };
  }
}

window.ROLES = ROLES;
window.PERMISSIONS = PERMISSIONS;
window.hasPermission = hasPermission;
window.checkSession = checkSession;