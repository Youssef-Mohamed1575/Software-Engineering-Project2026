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

window.ROLES = ROLES;
window.PERMISSIONS = PERMISSIONS;
window.hasPermission = hasPermission;