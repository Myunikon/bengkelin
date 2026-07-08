-- CREATE DATABASE IF NOT EXISTS bengkel
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;
-- 
-- USE bengkel;

CREATE TABLE users (
    id_user    INT          NOT NULL AUTO_INCREMENT,
    username   VARCHAR(50)  NOT NULL,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user),
    UNIQUE KEY uq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE pelanggan (
    id_pelanggan INT          NOT NULL AUTO_INCREMENT,
    nama         VARCHAR(100) NOT NULL,
    no_telp      VARCHAR(20)  NOT NULL,
    alamat       TEXT         NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_pelanggan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mekanik (
    id_mekanik INT                                   NOT NULL AUTO_INCREMENT,
    nama       VARCHAR(100)                          NOT NULL,
    status     ENUM('tersedia','sibuk','nonaktif')   NOT NULL DEFAULT 'tersedia',
    PRIMARY KEY (id_mekanik)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kendaraan (
    id_kendaraan  INT         NOT NULL AUTO_INCREMENT,
    id_pelanggan  INT         NOT NULL,
    no_polisi     VARCHAR(15) NOT NULL,
    merk          VARCHAR(50) NOT NULL,
    model         VARCHAR(50) NULL,
    tahun         INT(4)      NULL,
    created_at    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_kendaraan),
    UNIQUE KEY uq_no_polisi (no_polisi),
    CONSTRAINT fk_kendaraan_pelanggan
        FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sparepart (
    id_sparepart INT           NOT NULL AUTO_INCREMENT,
    nama_part    VARCHAR(100)  NOT NULL,
    stok         INT           NOT NULL DEFAULT 0,
    harga_jual   DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id_sparepart)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE servis (
    id_servis     INT                                                    NOT NULL AUTO_INCREMENT,
    id_kendaraan  INT                                                    NOT NULL,
    id_mekanik    INT                                                    NULL,
    tanggal_masuk DATE                                                   NOT NULL,
    status        ENUM('antre','dikerjakan','selesai','diambil','dibatalkan') NOT NULL DEFAULT 'antre',
    biaya_jasa    DECIMAL(10,2)                                          NOT NULL DEFAULT 0,
    keterangan    TEXT                                                   NULL,
    PRIMARY KEY (id_servis),
    CONSTRAINT fk_servis_kendaraan
        FOREIGN KEY (id_kendaraan) REFERENCES kendaraan(id_kendaraan)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_servis_mekanik
        FOREIGN KEY (id_mekanik) REFERENCES mekanik(id_mekanik)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE servis_details (
    id_detail    INT           NOT NULL AUTO_INCREMENT,
    id_servis    INT           NOT NULL,
    id_sparepart INT           NOT NULL,
    qty          INT           NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal     DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id_detail),
    CONSTRAINT fk_detail_servis
        FOREIGN KEY (id_servis) REFERENCES servis(id_servis)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detail_sparepart
        FOREIGN KEY (id_sparepart) REFERENCES sparepart(id_sparepart)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE servis_foto (
    id_foto     INT          NOT NULL AUTO_INCREMENT,
    id_servis   INT          NOT NULL,
    path_file   VARCHAR(255) NOT NULL,
    uploaded_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_foto),
    CONSTRAINT fk_foto_servis
        FOREIGN KEY (id_servis) REFERENCES servis(id_servis)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE riwayat_status (
    id_riwayat       INT         NOT NULL AUTO_INCREMENT,
    id_servis        INT         NOT NULL,
    status_baru      VARCHAR(20) NOT NULL,
    waktu_perubahan  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    keterangan       TEXT        NULL,
    PRIMARY KEY (id_riwayat),
    CONSTRAINT fk_riwayat_servis
        FOREIGN KEY (id_servis) REFERENCES servis(id_servis)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Default admin user (password: admin123)
INSERT INTO users (username, password)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Contoh pelanggan
INSERT INTO pelanggan (nama, no_telp, alamat) VALUES
    ('Budi Santoso', '081234567890', 'Jl. Merdeka No. 10, Jakarta'),
    ('Siti Rahayu',  '082345678901', 'Jl. Sudirman No. 5, Bandung'),
    ('Ahmad Fauzi',  '083456789012', NULL);

-- Contoh mekanik
INSERT INTO mekanik (nama, status) VALUES
    ('Eko Prasetyo', 'tersedia'),
    ('Dedi Kurniawan','tersedia'),
    ('Rudi Hartono',  'tersedia');

-- Contoh sparepart
INSERT INTO sparepart (nama_part, stok, harga_jual) VALUES
    ('Oli Mesin 1L',    50,  45000.00),
    ('Filter Udara',    30,  85000.00),
    ('Busi NGK',        100, 35000.00),
    ('Kampas Rem',      20, 120000.00),
    ('Ban Dalam 90/90', 15,  75000.00);
