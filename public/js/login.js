/* =====================================================================
   LOGIN.JS — menangani transisi Splash -> Login -> Welcome Splash persis
   seperti pada mockup, tetapi otentikasi dilakukan sungguhan ke backend
   Laravel (POST /login) alih-alih disimulasikan di client.
   ===================================================================== */

const DEMO_USERNAMES = {
  supervisor: 'sri.supervisor',
  teknisi: 'budi.teknisi',
  manajer: 'wahyu.manajer'
};
const ROLE_LABELS = {supervisor:'Supervisor', teknisi:'Teknisi', manajer:'Manajer'};
let selectedLoginRole = 'supervisor';

function csrfToken(){
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

function selectLoginRole(role){
  selectedLoginRole = role;
  document.querySelectorAll('.lrt').forEach(function(b){ b.classList.toggle('on', b.dataset.role===role); });
  document.getElementById('loginUsername').value = DEMO_USERNAMES[role];
  document.getElementById('loginRoleLabel').textContent = ROLE_LABELS[role];
  const err = document.getElementById('loginError');
  if(err) err.style.display = 'none';
}

function showLoginError(message){
  let err = document.getElementById('loginError');
  if(!err){
    err = document.createElement('div');
    err.id = 'loginError';
    err.className = 'callout warn';
    err.style.marginTop = '12px';
    document.querySelector('.login-note').insertAdjacentElement('beforebegin', err);
  }
  err.textContent = message;
  err.style.display = 'block';
}

function enterApp(){
  const btn = document.getElementById('loginSubmitBtn');
  const username = document.getElementById('loginUsername').value.trim();
  const password = document.getElementById('loginPassword').value;
  if(btn) btn.disabled = true;

  fetch('/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({ username: username, password: password, role: selectedLoginRole })
  }).then(function(res){
    return res.json().then(function(body){ return {ok: res.ok, body: body}; });
  }).then(function(result){
    if(btn) btn.disabled = false;
    if(!result.ok){
      showLoginError(result.body.message || 'Username atau password salah.');
      return;
    }
    const loginEl = document.getElementById('loginScreen');
    loginEl.classList.add('login-leaving');
    setTimeout(function(){
      loginEl.style.display='none';
      showWelcomeSplash(result.body.user);
    }, 440);
  }).catch(function(){
    if(btn) btn.disabled = false;
    showLoginError('Tidak dapat menghubungi server. Coba lagi.');
  });
}

function showWelcomeSplash(user){
  document.getElementById('welcomeAvatar').textContent = user.avatar;
  document.getElementById('welcomeName').textContent = user.name;
  document.getElementById('welcomeSub').textContent = user.sub;
  const w = document.getElementById('welcomeSplash');
  w.style.display = 'flex';
  setTimeout(function(){
    w.classList.add('welcome-leaving');
    setTimeout(function(){
      window.location.href = '/app';
    }, 560);
  }, 1550);
}

window.addEventListener('load', function(){
  setTimeout(function(){
    var splash = document.getElementById('splashScreen');
    splash.classList.add('fade-out');
    setTimeout(function(){ splash.style.display = 'none'; }, 620);
  }, 1500);
});
