<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BuatTabelAntrean extends Migration
{
    public function up()
    {
        $q = fn(string $sql) => $this->db->query($sql);
        $q("CREATE TABLE pengaturan (kunci VARCHAR(40) PRIMARY KEY, nilai TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $q("CREATE TABLE poli (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kode VARCHAR(2) NOT NULL UNIQUE,          -- huruf awalan nomor, mis. A -> A-007
            nama VARCHAR(80) NOT NULL,
            dokter VARCHAR(100) NOT NULL DEFAULT '',
            kuota INT NOT NULL DEFAULT 0,             -- 0 = tanpa batas
            warna VARCHAR(10) NOT NULL DEFAULT 'hijau',
            urutan INT NOT NULL DEFAULT 1,
            aktif TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $q("CREATE TABLE pengguna (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            username VARCHAR(40) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin','petugas') NOT NULL DEFAULT 'petugas',
            aktif TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // Nomor dimulai lagi dari 1 setiap hari per poli (kunci unik tanggal + poli + nomor)
        $q("CREATE TABLE antrean (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tanggal DATE NOT NULL,
            poli_id INT NOT NULL,
            nomor INT NOT NULL,
            token CHAR(10) NOT NULL UNIQUE,          -- untuk cek status dari HP (QR di tiket)
            status ENUM('menunggu','dipanggil','selesai','lewat') NOT NULL DEFAULT 'menunggu',
            loket VARCHAR(40) NOT NULL DEFAULT '',
            panggil_ke INT NOT NULL DEFAULT 0,
            petugas_id INT NULL,
            dibuat_at DATETIME NOT NULL,
            dipanggil_at DATETIME NULL,
            selesai_at DATETIME NULL,
            UNIQUE KEY nomor_harian (tanggal, poli_id, nomor),
            INDEX (tanggal, poli_id, status),
            FOREIGN KEY (poli_id) REFERENCES poli(id) ON DELETE RESTRICT,
            FOREIGN KEY (petugas_id) REFERENCES pengguna(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // Setiap pemanggilan (termasuk panggil ulang) dicatat; layar TV membaca yang terbaru
        $q("CREATE TABLE panggilan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            antrean_id INT NOT NULL,
            waktu DATETIME NOT NULL,
            loket VARCHAR(40) NOT NULL DEFAULT '',
            FOREIGN KEY (antrean_id) REFERENCES antrean(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $q("CREATE TABLE batas_coba (kunci VARCHAR(80) PRIMARY KEY, n INT NOT NULL DEFAULT 0, mulai DATETIME NOT NULL) ENGINE=InnoDB");
    }

    public function down()
    {
        foreach (['panggilan', 'antrean', 'pengguna', 'poli', 'pengaturan', 'batas_coba'] as $t) $this->forge->dropTable($t, true);
    }
}
