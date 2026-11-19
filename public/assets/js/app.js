// Konfirmasi aksi penting
document.querySelectorAll('form[data-yakin]').forEach(function (f) {
  f.addEventListener('submit', function (e) { if (!confirm(f.dataset.yakin)) e.preventDefault(); });
});
// Cegah tombol ditekan dua kali (kios & panggil berikutnya)
document.querySelectorAll('form[data-satu-kali]').forEach(function (f) {
  f.addEventListener('submit', function (e) {
    var b = e.submitter; if (b && b.name) { var h = document.createElement('input'); h.type = 'hidden'; h.name = b.name; h.value = b.value; f.appendChild(h); }
    setTimeout(function () { f.querySelectorAll('button').forEach(function (x) { x.disabled = true; }); }, 0);
  });
});
// Jam di kios
var jam = document.querySelector('[data-jam]');
if (jam) { var t = function () { var d = new Date(); jam.textContent = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2); }; t(); setInterval(t, 10000); }
// Kios: segarkan jumlah antrean tiap 30 detik saat tidak dipakai
if (document.body.classList.contains('hal-kios')) setTimeout(function () { location.reload(); }, 30000);
