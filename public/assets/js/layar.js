// Layar TV ruang tunggu: ambil data tiap 2 detik, umumkan panggilan baru dengan nada + suara.
(function () {
  var el = document.getElementById('layar'), SUMBER = el.dataset.sumber;
  var $ = function (id) { return document.getElementById(id); };
  var terakhir = null, suaraAktif = false, ctx = null, antre = Promise.resolve(), selisih = 0, data = null;

  // Angka ke kata dalam Bahasa Indonesia (1-999) agar dibacakan jelas: 7 -> "tujuh", 112 -> "seratus dua belas"
  var SATUAN = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
  function terbilang(n) {
    if (n < 12) return SATUAN[n];
    if (n < 20) return SATUAN[n - 10] + ' belas';
    if (n < 100) return SATUAN[Math.floor(n / 10)] + ' puluh ' + SATUAN[n % 10];
    if (n < 200) return 'seratus ' + terbilang(n - 100);
    return SATUAN[Math.floor(n / 100)] + ' ratus ' + terbilang(n % 100);
  }
  function dingdong() {
    if (!ctx) return Promise.resolve();
    var t = ctx.currentTime + 0.05;
    [[784, 0], [659, 0.5], [523, 1.0]].forEach(function (x) {
      var o = ctx.createOscillator(), g = ctx.createGain();
      o.frequency.value = x[0]; g.gain.setValueAtTime(0.0001, t + x[1]);
      g.gain.exponentialRampToValueAtTime(0.4, t + x[1] + 0.01); g.gain.exponentialRampToValueAtTime(0.0001, t + x[1] + 1.4);
      o.connect(g); g.connect(ctx.destination); o.start(t + x[1]); o.stop(t + x[1] + 1.5);
    });
    return new Promise(function (ok) { setTimeout(ok, 2400); });
  }
  function ucap(teks) {
    return new Promise(function (ok) {
      if (!('speechSynthesis' in window)) return ok();
      var u = new SpeechSynthesisUtterance(teks), v = speechSynthesis.getVoices().filter(function (x) { return /^id/i.test(x.lang); })[0];
      u.lang = 'id-ID'; u.rate = data ? data.kecepatan : 0.9; if (v) u.voice = v;
      var batas = setTimeout(ok, 15000); u.onend = u.onerror = function () { clearTimeout(batas); ok(); };
      speechSynthesis.speak(u);
    });
  }
  function umumkan(p) {
    $('dipanggil').classList.remove('kedip'); void $('dipanggil').offsetWidth; $('dipanggil').classList.add('kedip');
    if (!suaraAktif || !data.suara) return;
    var kalimat = 'Nomor antrean, ' + p.kode_poli.split('').join(' ') + ', ' + terbilang(p.nomor) + '. Silakan menuju ' + p.loket + ', ' + p.nama_poli + '.';
    antre = antre.then(dingdong).then(function () { return ucap(kalimat); }).then(function () { return ucap(kalimat); });
  }
  function gambar(j) {
    var p = j.panggilan[0];
    if (p) {
      $('nomor').textContent = p.kode; $('ke').textContent = p.loket + ' · ' + p.nama_poli;
      $('dipanggil').className = 'dipanggil w-' + p.warna + ($('dipanggil').classList.contains('kedip') ? ' kedip' : '');
    }
    $('riwayat').innerHTML = '';
    j.panggilan.slice(1, 5).forEach(function (x) {
      var li = document.createElement('li'); li.className = 'w-' + x.warna;
      li.innerHTML = '<b></b><span></span>'; li.querySelector('b').textContent = x.kode; li.querySelector('span').textContent = x.loket;
      $('riwayat').appendChild(li);
    });
    $('poli').innerHTML = '';
    j.poli.forEach(function (x) {
      var d = document.createElement('div'); d.className = 'poli-kotak w-' + x.warna;
      d.innerHTML = '<span class="nm"></span><b></b><small></small>';
      d.querySelector('.nm').textContent = x.nama; d.querySelector('b').textContent = x.terakhir || '-'; d.querySelector('small').textContent = x.menunggu + ' menunggu';
      $('poli').appendChild(d);
    });
    if (j.teks && $('teks').textContent !== j.teks) $('teks').textContent = j.teks;
  }
  function ambil() {
    fetch(SUMBER, { cache: 'no-store' }).then(function (r) { return r.json(); }).then(function (j) {
      data = j; selisih = j.server - Date.now();
      var p = j.panggilan[0];
      // Muat pertama: tampilkan saja tanpa diumumkan. Berikutnya: umumkan setiap panggilan baru.
      if (terakhir !== null && p && p.id > terakhir) j.panggilan.filter(function (x) { return x.id > terakhir; }).reverse().forEach(umumkan);
      if (p) terakhir = Math.max(terakhir || 0, p.id); else if (terakhir === null) terakhir = 0;
      gambar(j);
    }).catch(function () {});
  }
  function jam() { var d = new Date(Date.now() + selisih); $('jam').textContent = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2); }
  $('aktifkan').addEventListener('click', function () {
    ctx = new (window.AudioContext || window.webkitAudioContext)(); ctx.resume(); suaraAktif = true;
    if ('speechSynthesis' in window) speechSynthesis.getVoices();
    if (navigator.wakeLock) navigator.wakeLock.request('screen').catch(function () {});
    $('tirai').hidden = true;
  });
  ambil(); setInterval(ambil, 2000); jam(); setInterval(jam, 1000);
})();
