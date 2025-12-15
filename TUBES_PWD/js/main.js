
document.addEventListener("DOMContentLoaded", () => {
  setupLoginValidation();
  setupRegisterValidation();
});

function setupLoginValidation() {
  const form = document.getElementById("loginForm");
  if (!form) return;

  const inputLogin = form.querySelector("[name='username']");
  const inputPass  = form.querySelector("[name='password']");
  const alertBox   = document.getElementById("alertError");

  form.addEventListener("submit", (e) => {
    const errors = [];

    if (!inputLogin.value.trim()) {
      errors.push("Username / email wajib diisi.");
    }

    if (!inputPass.value.trim()) {
      errors.push("Password wajib diisi.");
    }

    if (errors.length > 0) {
      e.preventDefault();
      if (alertBox) {
        alertBox.classList.remove("d-none");
        alertBox.innerHTML = `<i class="bi bi-exclamation-triangle"></i> ${errors.join(" ")}`;
      } else {
        alert(errors.join("\n"));
      }
    }
  });
}
  function setupRegisterValidation() {
  const form = document.getElementById("registerForm");
  if (!form) return;

  const nama      = form.querySelector("[name='nama_lengkap']");
  const email     = form.querySelector("[name='email']");
  const username  = form.querySelector("[name='username']");
  const noTelp    = form.querySelector("[name='no_telp']");
  const alamat    = form.querySelector("[name='alamat']");
  const pass      = form.querySelector("[name='password']");
  const pass2     = form.querySelector("[name='confirm_password']");

  form.addEventListener("submit", (e) => {
    let errs = [];

    if (!nama.value.trim())     errs.push("Nama wajib diisi.");
    if (!email.value.trim())    errs.push("Email wajib diisi.");
    if (!username.value.trim()) errs.push("Username wajib diisi.");
    if (!noTelp.value.trim())   errs.push("No. telepon wajib diisi.");
    if (!alamat.value.trim())   errs.push("Alamat wajib diisi.");
    if (!pass.value.trim())     errs.push("Password wajib diisi.");
    if (!pass2.value.trim())    errs.push("Konfirmasi password wajib diisi.");

    if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
      errs.push("Format email tidak valid.");
    }

    if (pass.value && pass.value.length < 8) {
      errs.push("Password minimal 8 karakter.");
    }

    if (pass.value && pass2.value && pass.value !== pass2.value) {
      errs.push("Konfirmasi password tidak sama.");
    }

    if (errs.length > 0) {
      e.preventDefault();
      alert(errs.join("\n"));
    }
  });
  }
document.addEventListener('DOMContentLoaded', function () {
    const motorSelect   = document.getElementById('motor_id');
    const warnaSelect   = document.getElementById('warna');
    const metodeSelect  = document.getElementById('metode_pembayaran');
    const dpInput       = document.getElementById('dp');
    const tenorSelect   = document.getElementById('tenor');
    const kreditOptions = document.getElementById('kreditOptions');

    const summaryMotor  = document.getElementById('summaryMotor');
    const summaryWarna  = document.getElementById('summaryWarna');
    const summaryMetode = document.getElementById('summaryMetode');
    const summaryTotal  = document.getElementById('summaryTotal');

    function formatRupiah(angka) {
        if (isNaN(angka)) angka = 0;
        return 'Rp ' + angka.toLocaleString('id-ID');
    }

    function updateSummary() {

        let motorText  = '-';
        let hargaMotor = 0;

        if (motorSelect && motorSelect.value) {
            const opt = motorSelect.options[motorSelect.selectedIndex];
            motorText  = opt.text; 
            hargaMotor = parseInt(opt.dataset.price || '0', 10);
        }

        let warnaText = '-';
        if (warnaSelect && warnaSelect.value) {
            const opt = warnaSelect.options[warnaSelect.selectedIndex];
            warnaText = opt.text;
        }

        let metodeText = '-';
        if (metodeSelect && metodeSelect.value) {
            const opt = metodeSelect.options[metodeSelect.selectedIndex];
            metodeText = opt.text;
        }

        const total = hargaMotor;

        if (summaryMotor)  summaryMotor.textContent  = motorText;
        if (summaryWarna)  summaryWarna.textContent  = warnaText;
        if (summaryMetode) summaryMetode.textContent = metodeText;
        if (summaryTotal)  summaryTotal.textContent  = formatRupiah(total);
    }

    function handleMetodeChange() {
        if (!metodeSelect) return;

        const value = metodeSelect.value;
        if (value === 'kredit') {
            kreditOptions.classList.remove('d-none');
        } else {
            kreditOptions.classList.add('d-none');
        }

        updateSummary();
    }

    if (motorSelect)  motorSelect.addEventListener('change', updateSummary);
    if (warnaSelect)  warnaSelect.addEventListener('change', updateSummary);
    if (metodeSelect) metodeSelect.addEventListener('change', handleMetodeChange);

    if (dpInput)     dpInput.addEventListener('input', updateSummary);
    if (tenorSelect) tenorSelect.addEventListener('change', updateSummary);

    handleMetodeChange();
    updateSummary();
});
document.addEventListener('DOMContentLoaded', function () {
    var mapEl = document.getElementById('mapLokasi');
    if (!mapEl) return;

    var map = L.map('mapLokasi').setView([-7.797068, 110.370529], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var marker = L.marker([-7.797068, 110.370529], {
        draggable: true
    }).addTo(map);

    function updateInfo(latlng) {
        var info = document.getElementById('mapInfo');
        if (!info) return;
        info.textContent = 'Lokasi pin: ' +
            latlng.lat.toFixed(5) + ', ' +
            latlng.lng.toFixed(5) +
            ' (geser pin atau klik peta untuk mengubah)';
    }

    updateInfo(marker.getLatLng());

    marker.on('dragend', function (e) {
        updateInfo(e.target.getLatLng());
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateInfo(e.latlng);
    });
});


