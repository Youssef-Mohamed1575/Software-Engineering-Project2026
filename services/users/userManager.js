function openModal(id) {
  document.getElementById(id).classList.add('active');
}

function closeModal(id) {
  document.getElementById(id).classList.remove('active');
  if (id === 'addModal') document.getElementById('addUserForm').reset();
  if (id === 'editModal') document.getElementById('editUserForm').reset();
}

function fetchUsers() {
  fetch('api/get_users.php')
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const tbody = document.getElementById('usersTableBody');
        tbody.innerHTML = '';
        data.users.forEach(user => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td class="px-5 py-5 border-b border-slate-200 bg-white text-sm">
              <p class="text-slate-900 whitespace-no-wrap">${user.username}</p>
            </td>
            <td class="px-5 py-5 border-b border-slate-200 bg-white text-sm">
              <span class="relative inline-block px-3 py-1 font-semibold text-slate-900 leading-tight">
                <span aria-hidden class="absolute inset-0 bg-blue-200 opacity-50 rounded-full"></span>
                <span class="relative">${user.role}</span>
              </span>
            </td>
            <td class="px-5 py-5 border-b border-slate-200 bg-white text-sm">
              <button onclick="openEditModal(${user.id}, '${user.username}', '${user.role}')" class="text-blue-600 hover:text-blue-900 mr-3 font-semibold">Edit</button>
              ${user.id !== currentUserId ? `<button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-900 font-semibold">Delete</button>` : `<span class="text-slate-400">Owner</span>`}
            </td>
          `;
          tbody.appendChild(tr);
        });
      } else {
        alert(data.message);
      }
    });
}

function handleAddUser(e) {
  e.preventDefault();
  const username = document.getElementById('addUsername').value;
  const password = document.getElementById('addPassword').value;
  const role = document.getElementById('addRole').value;

  fetch('api/add_user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password, role })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      closeModal('addModal');
      fetchUsers();
    } else {
      alert(data.message);
    }
  });
}

function openEditModal(id, username, role) {
  document.getElementById('editUserId').value = id;
  document.getElementById('editUsername').value = username;
  document.getElementById('editPassword').value = '';
  document.getElementById('editRole').value = role;
  openModal('editModal');
}

function handleEditUser(e) {
  e.preventDefault();
  const user_id = document.getElementById('editUserId').value;
  const username = document.getElementById('editUsername').value;
  const password = document.getElementById('editPassword').value;
  const role = document.getElementById('editRole').value;

  fetch('api/update_user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id, username, password, role })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      closeModal('editModal');
      fetchUsers();
    } else {
      alert(data.message);
    }
  });
}

function deleteUser(id) {
  if (confirm('Are you sure you want to delete this user?')) {
    fetch('api/delete_user.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: id })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        fetchUsers();
      } else {
        alert(data.message);
      }
    });
  }
}
