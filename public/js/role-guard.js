/* Role guard: UI role must follow the authenticated Laravel user. */
(function () {
  const actualRole = window.__SIMPM_BOOTSTRAP__ && window.__SIMPM_BOOTSTRAP__.currentRole;
  if (!actualRole || typeof window.setRole !== 'function') return;

  const originalSetRole = window.setRole;
  window.setRole = function (role) {
    if (role !== actualRole) {
      alert('Akun ini masuk sebagai ' + actualRole + '. Silakan logout lalu login dengan akun role yang sesuai.');
      return;
    }
    return originalSetRole(role);
  };

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.role-pill').forEach(function (pill) {
      const role = pill.dataset.role;
      const allowed = role === actualRole;
      pill.disabled = !allowed;
      pill.classList.toggle('active', allowed);
      pill.setAttribute('aria-disabled', allowed ? 'false' : 'true');
      if (!allowed) pill.style.opacity = '0.45';
    });
  });
})();
