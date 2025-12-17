create sequence seq_tamu start 1;
create sequence seq_kamar start 1;
create sequence seq_reservasi start 1;
create sequence seq_pembayaran start 1;


create table tipe_kamar (
    id_tipe serial primary key,
    nama_tipe varchar(50) unique not null,
    harga_per_malam numeric(10,2) not null,
    kapasitas int not null default 2,
    deskripsi text,
    foto_url varchar(255)
);

create table fasilitas (
    id_fasilitas serial primary key,
    nama_fasilitas varchar(100) unique not null,
    kategori varchar(50) not null
);

create table tamu (
    id_tamu int primary key default nextval('seq_tamu'),
    nama_tamu varchar(100) not null,
    no_telp varchar(20) unique not null,
    email varchar(100) unique,
    password varchar(30)
);

create table kamar (
    id_kamar int primary key default nextval('seq_kamar'),
    id_tipe int not null,
    nomor_kamar varchar(10) unique not null,
    lantai int,
    status varchar(20) default 'tersedia'
);

create table reservasi (
    id_reservasi int primary key default nextval('seq_reservasi'),
    id_tamu int not null,
    id_kamar int not null,
    tgl_checkin date not null,
    tgl_checkout date not null,
    jumlah_tamu int default 1,
    status_reservasi varchar(20) default 'pending',
    tgl_reservasi timestamp default current_timestamp
);

create table pembayaran (
    id_pembayaran int primary key default nextval('seq_pembayaran'),
    id_reservasi int unique,
    jumlah numeric(12,2),
    metode_bayar varchar(50),
    tgl_bayar timestamp,
    status_bayar varchar(20) default 'belum lunas'
);

create table tipe_fasilitas (
    id_tipe int,
    id_fasilitas int,
    primary key (id_tipe, id_fasilitas)
);


alter table kamar
add constraint fk_kamar_tipe foreign key (id_tipe)
references tipe_kamar(id_tipe);

alter table reservasi
add constraint fk_reservasi_tamu foreign key (id_tamu)
references tamu(id_tamu);

alter table reservasi
add constraint fk_reservasi_kamar foreign key (id_kamar)
references kamar(id_kamar);

alter table pembayaran
add constraint fk_pembayaran_reservasi foreign key (id_reservasi)
references reservasi(id_reservasi);

alter table tipe_fasilitas
add constraint fk_tipe_fasilitas_tipe foreign key (id_tipe)
references tipe_kamar(id_tipe);

alter table tipe_fasilitas
add constraint fk_tipe_fasilitas_fasilitas foreign key (id_fasilitas)
references fasilitas(id_fasilitas);


insert into tipe_kamar (nama_tipe, harga_per_malam, kapasitas, deskripsi, foto_url) values
    ('superior room', 850000, 2, 'kamar nyaman dengan tempat tidur double bed, cocok untuk business traveler', '/img/superior-room.jpg'),
    ('deluxe room', 1250000, 2, 'kamar lebih luas dengan view kota, dilengkapi working desk', '/img/deluxe-room.jpg'),
    ('executive room', 1650000, 2, 'kamar eksklusif dengan akses executive lounge dan fasilitas premium', '/img/executive-room.jpg'),
    ('junior suite', 2100000, 3, 'suite dengan ruang tamu terpisah, cocok untuk romantis getaway', '/img/junior-suite.jpg'),
    ('family room', 2850000, 5, 'kamar luas dengan 2 double bed dan 1 extra bed, ideal untuk keluarga', '/img/family-room.jpg'),
    ('suite room', 3500000, 4, 'suite mewah dengan ruang tamu dan kamar terpisah', '/img/suite-room.jpg'),
    ('presidential suite', 8500000, 6, 'kamar terluas dan termewah dengan 2 kamar tidur terpisah', '/img/presidential-suite.jpg'),
    ('twin room', 1350000, 2, 'kamar dengan 2 single bed, cocok untuk teman atau saudara', '/img/twin-room.jpg'),
    ('superior twin', 950000, 2, 'kamar twin bed dengan fasilitas standar yang nyaman', '/img/superior-twin.jpg'),
    ('deluxe twin', 1450000, 2, 'kamar twin bed yang lebih luas dengan view better', '/img/deluxe-twin.jpg'),
    ('executive suite', 4500000, 4, 'suite untuk eksekutif dengan ruang meeting kecil', '/img/executive-suite.jpg'),
    ('hollywood twin', 1200000, 2, 'kamar dengan 2 single bed yang dapat disatukan', '/img/hollywood-twin.jpg'),
    ('superior double', 900000, 2, 'kamar double bed dengan fasilitas lengkap', '/img/superior-double.jpg'),
    ('deluxe double', 1400000, 2, 'kamar double bed yang lebih luas', '/img/deluxe-double.jpg'),
    ('family suite', 3200000, 6, 'suite dengan 2 kamar terpisah untuk keluarga besar', '/img/family-suite.jpg'),
    ('accessible room', 850000, 2, 'kamar khusus untuk tamu disabilitas', '/img/accessible-room.jpg'),
    ('connecting room', 2400000, 6, '2 kamar yang terhubung, ideal untuk keluarga besar', '/img/connecting-room.jpg'),
    ('honeymoon suite', 3800000, 2, 'suite spesial untuk pasangan dengan dekorasi romantis', '/img/honeymoon-suite.jpg'),
    ('pool access room', 1950000, 3, 'kamar dengan akses langsung ke kolam renang', '/img/pool-access.jpg'),
    ('corner suite', 2750000, 3, 'suite berada di sudut bangunan dengan view 2 arah', '/img/corner-suite.jpg'),
    ('royal suite', 6500000, 5, 'suite sangat mewah dengan 2 kamar dan ruang tamu besar', '/img/royal-suite.jpg'),
    ('bunk bed room', 1100000, 4, 'kamar dengan bunk bed, cocok untuk grup atau keluarga kecil', '/img/bunk-bed-room.jpg'),
    ('triple room', 1600000, 3, 'kamar dengan 3 single bed untuk grup kecil', '/img/triple-room.jpg'),
    ('quad room', 1900000, 4, 'kamar dengan 4 single bed untuk grup', '/img/quad-room.jpg');

insert into fasilitas (nama_fasilitas, kategori) values
    ('ac', 'kamar'),
    ('tv led 32 inch', 'kamar'),
    ('wi-fi gratis', 'kamar'),
    ('mini bar', 'kamar'),
    ('safety deposit box', 'kamar'),
    ('coffee/tea maker', 'kamar'),
    ('bathub', 'kamar'),
    ('shower', 'kamar'),
    ('kolam renang', 'umum'),
    ('fitness center', 'umum'),
    ('restoran', 'umum'),
    ('room service 24 jam', 'umum'),
    ('parkir gratis', 'umum'),
    ('business center', 'umum'),
    ('sarapan buffet', 'makanan'),
    ('all day dining', 'makanan'),
    ('bar/lounge', 'makanan'),
    ('akses disabilitas', 'lainnya'),
    ('executive lounge', 'lainnya'),
    ('kids club', 'lainnya');

insert into tamu (nama_tamu, no_telp, email, password) values
    ('budi santoso', '081234567890', 'budi.santoso@gmail.com', 'budi123'),
    ('sari dewi', '081234567891', 'sari.dewi@yahoo.com', 'sari123'),
    ('ahmad fauzi', '081234567892', 'ahmad.fauzi@gmail.com', 'ahmad123'),
    ('maya sari', '081234567893', 'maya.sari@email.com', 'maya123'),
    ('rizki pratama', '081234567894', 'rizki.pratama@gmail.com', 'rizki123'),
    ('diana putri', '081234567895', 'diana.putri@yahoo.com', 'diana123'),
    ('hendra wijaya', '081234567896', 'hendra.wijaya@gmail.com', 'hendra123'),
    ('linda suryani', '081234567897', 'linda.suryani@email.com', 'linda123'),
    ('fajar nugroho', '081234567898', 'fajar.nugroho@gmail.com', 'fajar123'),
    ('rina marlina', '081234567899', 'rina.marlina@yahoo.com', 'rina123'),
    ('eko prasetyo', '081234567800', 'eko.prasetyo@gmail.com', 'eko123'),
    ('fitri anggraini', '081234567801', 'fitri.anggraini@email.com', 'fitri123'),
    ('joko susilo', '081234567802', 'joko.susilo@gmail.com', 'joko123'),
    ('nina wulandari', '081234567803', 'nina.wulandari@yahoo.com', 'nina123'),
    ('adi saputra', '081234567804', 'adi.saputra@gmail.com', 'adi123'),
    ('mira oktaviani', '081234567805', 'mira.oktaviani@email.com', 'mira123'),
    ('rudi hartono', '081234567806', 'rudi.hartono@gmail.com', 'rudi123'),
    ('sinta maharani', '081234567807', 'sinta.maharani@yahoo.com', 'sinta123'),
    ('teguh wibowo', '081234567808', 'teguh.wibowo@gmail.com', 'teguh123'),
    ('wulan sari', '081234567809', 'wulan.sari@email.com', 'wulan123');

insert into kamar (id_tipe, nomor_kamar, lantai, status) values
    (1, '201', 2, 'terisi'),
    (1, '202', 2, 'tersedia'),
    (1, '203', 2, 'tersedia'),
    (1, '204', 2, 'tersedia'),
    (9, '205', 2, 'terisi'),
    (9, '206', 2, 'tersedia'),
    (13, '207', 2, 'tersedia'),
    (13, '208', 2, 'tersedia'),
    (2, '301', 3, 'tersedia'),
    (2, '302', 3, 'tersedia'),
    (2, '303', 3, 'tersedia'),
    (2, '304', 3, 'tersedia'),
    (10, '305', 3, 'tersedia'),
    (10, '306', 3, 'tersedia'),
    (14, '307', 3, 'tersedia'),
    (14, '308', 3, 'terisi'),
    (3, '401', 4, 'tersedia'),
    (3, '402', 4, 'tersedia'),
    (3, '403', 4, 'tersedia'),
    (8, '404', 4, 'tersedia'),
    (8, '405', 4, 'tersedia'),
    (12, '406', 4, 'tersedia'),
    (12, '407', 4, 'tersedia'),
    (12, '408', 4, 'tersedia'),
    (4, '501', 5, 'tersedia'),
    (4, '502', 5, 'tersedia'),
    (5, '503', 5, 'tersedia'),
    (5, '504', 5, 'tersedia'),
    (15, '505', 5, 'tersedia'),
    (15, '506', 5, 'tersedia'),
    (17, '507', 5, 'tersedia'),
    (17, '508', 5, 'tersedia'),
    (6, '601', 6, 'tersedia'),
    (6, '602', 6, 'tersedia'),
    (6, '603', 6, 'tersedia'),
    (11, '604', 6, 'tersedia'),
    (11, '605', 6, 'tersedia'),
    (18, '606', 6, 'tersedia'),
    (18, '607', 6, 'tersedia'),
    (20, '608', 6, 'tersedia'),
    (7, '701', 7, 'tersedia'),
    (7, '702', 7, 'tersedia'),
    (16, '703', 7, 'tersedia'),
    (16, '704', 7, 'tersedia'),
    (19, '705', 7, 'tersedia'),
    (19, '706', 7, 'tersedia'),
    (19, '707', 7, 'tersedia'),
    (19, '708', 7, 'tersedia');

insert into tipe_fasilitas (id_tipe, id_fasilitas) values
    (1,1),(1,2),(1,3),(1,5),(1,6),(1,8),(1,13),
    (2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,7),(2,8),(2,13),
    (3,1),(3,2),(3,3),(3,4),(3,5),(3,6),(3,7),(3,8),(3,13),(3,19),
    (4,1),(4,2),(4,3),(4,4),(4,5),(4,6),(4,7),(4,8),(4,13),(4,19),
    (5,1),(5,2),(5,3),(5,4),(5,5),(5,6),(5,7),(5,8),(5,13),(5,19),
    (6,1),(6,2),(6,3),(6,4),(6,5),(6,6),(6,7),(6,8),(6,13),(6,19),
    (7,1),(7,2),(7,3),(7,4),(7,5),(7,6),(7,7),(7,8),(7,13),(7,19),
    (8,1),(8,2),(8,3),(8,5),(8,6),(8,8),(8,13),
    (9,1),(9,2),(9,3),(9,5),(9,6),(9,8),(9,13),
    (10,1),(10,2),(10,3),(10,4),(10,5),(10,6),(10,7),(10,8),(10,13),
    (11,1),(11,2),(11,3),(11,4),(11,5),(11,6),(11,7),(11,8),(11,13),(11,19),
    (12,1),(12,2),(12,3),(12,5),(12,6),(12,8),(12,13),
    (13,1),(13,2),(13,3),(13,5),(13,6),(13,8),(13,13),
    (14,1),(14,2),(14,3),(14,4),(14,5),(14,6),(14,7),(14,8),(14,13),
	(15,1),(15,2),(15,3),(15,4),(15,5),(15,6),(15,7),(15,8),(15,13),(15,19),(15,20),
    (16,1),(16,2),(16,3),(16,5),(16,6),(16,8),(16,13),(16,18),
    (17,1),(17,2),(17,3),(17,4),(17,5),(17,6),(17,7),(17,8),(17,13),(17,20),
    (18,1),(18,2),(18,3),(18,4),(18,5),(18,6),(18,7),(18,8),(18,13),(18,19),
    (19,1),(19,2),(19,3),(19,4),(19,5),(19,6),(19,7),(19,8),(19,13),(19,9),
    (20,1),(20,2),(20,3),(20,4),(20,5),(20,6),(20,7),(20,8),(20,13),(20,19),
	(21,1),(21,2),(21,3),(21,5),(21,6),(21,8),(21,13),
	(22,1),(22,2),(22,3),(22,5),(22,6),(22,8),(22,13),
	(23,1),(23,2),(23,3),(23,5),(23,6),(23,8),(23,13),
	(24,1),(24,2),(24,3),(24,4),(24,5),(24,6),(24,7),(24,8),(24,13),(24,19);

insert into reservasi (id_tamu, id_kamar, tgl_checkin, tgl_checkout, jumlah_tamu, status_reservasi, tgl_reservasi) values
	(16, 22, '2025-10-02', '2025-10-04', 2, 'selesai', '2025-09-25 10:30:00'),
	(1, 28, '2025-10-07', '2025-10-10', 2, 'selesai', '2025-09-27 08:45:00'),
	(9, 42, '2025-10-14', '2025-10-17', 2, 'selesai', '2025-10-12 16:40:00'),
	(20, 34, '2025-10-20', '2025-10-24', 4, 'selesai', '2025-10-17 10:25:00'),
	(6, 41, '2025-10-25', '2025-10-28', 2, 'selesai', '2025-10-19 15:20:00'),
	(2, 32, '2025-10-27', '2025-10-31', 3, 'selesai', '2025-10-23 11:30:00'),
	(7, 44, '2025-10-29', '2025-11-01', 3, 'selesai', '2025-10-24 10:55:00'),
	(4, 17, '2025-11-05', '2025-11-08', 2, 'selesai', '2025-10-28 11:15:00'),
	(15, 37, '2025-11-06', '2025-11-10', 5, 'selesai', '2025-11-01 15:40:00'),
	(16, 29, '2025-11-13', '2025-11-16', 3, 'selesai', '2025-11-05 14:20:00'),
	(5, 38, '2025-11-14', '2025-11-16', 4, 'selesai', '2025-11-11 13:40:00'),
	(9, 31, '2025-11-17', '2025-11-20', 3, 'selesai', '2025-11-12 13:20:00'),
	(3, 35, '2025-11-20', '2025-11-23', 2, 'selesai', '2025-11-18 09:15:00'),
	(17, 43, '2025-11-22', '2025-11-25', 2, 'selesai', '2025-11-19 09:25:00'),
	(20, 45, '2025-11-24', '2025-11-26', 3, 'selesai', '2025-11-20 14:50:00'),
	(8, 2, '2025-11-26', '2025-11-29', 2, 'check-in', '2025-11-21 09:30:00'),
	(9, 24, '2025-11-26', '2025-11-28', 4, 'check-in', '2025-11-23 15:30:00'),
	(11, 12, '2025-11-28', '2025-11-30', 2, 'check-in', '2025-11-25 11:45:00'),
	(1, 23, '2025-12-01', '2025-12-04', 4, 'confirmed', '2025-11-28 10:15:00'),
	(19, 11, '2025-12-07', '2025-12-10', 2, 'confirmed', '2025-12-02 11:35:00'),
	(18, 3, '2025-12-13', '2025-12-16', 3, 'confirmed', '2025-12-05 14:50:00'),
	(2, 33, '2025-12-18', '2025-12-21', 2, 'pending', '2025-12-09 13:20:00'),
	(3, 40, '2025-12-22', '2025-12-25', 3, 'pending', '2025-12-12 16:45:00'),
	(4, 36, '2025-12-26', '2025-12-29', 2, 'pending', '2025-12-15 09:45:00'),
	(20, 14, '2025-12-28', '2025-12-31', 2, 'pending', '2025-12-19 14:10:00');


insert into pembayaran (id_reservasi, jumlah, metode_bayar, tgl_bayar, status_bayar) values
	(1, 2200000, 'transfer', '2025-09-26 10:30:00', 'lunas'),
	(2, 5700000, 'kartu kredit', '2025-09-29 08:45:00', 'lunas'),
	(3, 5850000, 'transfer', '2025-10-13 16:40:00', 'lunas'),
	(4, 5800000, 'kartu debit', '2025-10-19 10:25:00', 'lunas'),
	(5, 5850000, 'transfer', '2025-10-24 15:20:00', 'lunas'),
	(6, 6400000, 'cash', '2025-10-26 11:30:00', 'lunas'),
	(7, 5850000, 'kartu debit', '2025-10-28 10:55:00', 'lunas'),
	(8, 4950000, 'transfer', '2025-11-04 11:15:00', 'lunas'),
	(9, 18000000, 'kartu kredit', '2025-11-05 15:40:00', 'lunas'),
	(10, 9600000, 'kartu debit', '2025-11-12 14:20:00', 'lunas'),
	(11, 9000000, 'kartu debit', '2025-11-13 13:40:00', 'lunas'),
	(12, 8250000, 'transfer', '2025-11-16 13:20:00', 'lunas'),
	(13, 4800000, 'cash', '2025-11-19 09:15:00', 'lunas'),
	(14, 5850000, 'kartu debit', '2025-11-21 09:25:00', 'lunas'),
	(15, 3900000, 'transfer', '2025-11-23 14:50:00', 'lunas'),
	(16, 2550000, 'cash', '2025-11-25 09:30:00', 'lunas'),
	(17, 2400000, NULL, NULL, 'belum lunas'),
	(18, 2800000, 'kartu kredit', '2025-11-27 11:45:00', 'lunas'),
	(19, 5700000, 'transfer', '2025-11-30 10:15:00', 'lunas'),
	(20, 4350000, NULL, NULL, 'belum lunas'),
	(21, 2550000, 'transfer', '2025-12-12 14:50:00', 'lunas'),
	(22, 4800000, NULL, NULL, 'belum lunas'),
	(23, 8250000, NULL, NULL, 'belum lunas'),
	(24, 4950000, NULL, NULL, 'belum lunas'),
	(25, 4200000, 'transfer', '2025-12-19 14:50:00', 'lunas');


-- function

create or replace function hitung_total_reservasi(
	p_id_reservasi int
) returns numeric as $$
declare
    v_total numeric(12,2);
    v_harga_per_malam numeric(10,2);
    v_jumlah_malam int;
begin
    select 
        tk.harga_per_malam,
        (r.tgl_checkout - r.tgl_checkin)
    into v_harga_per_malam, v_jumlah_malam
    from reservasi r
    join kamar k on r.id_kamar = k.id_kamar
    join tipe_kamar tk on k.id_tipe = tk.id_tipe
    where r.id_reservasi = p_id_reservasi;

    v_total := v_harga_per_malam * v_jumlah_malam;
    return v_total;
end;
$$ language plpgsql;


create or replace function cek_ketersediaan_kamar(
    p_id_kamar int,
    p_tgl_checkin date,
    p_tgl_checkout date
) returns boolean as $$
declare
    v_count int;
begin
    select count(*)
    into v_count
    from reservasi
    where id_kamar = p_id_kamar
      and status_reservasi in ('confirmed', 'check-in')
      and ((tgl_checkin < p_tgl_checkout) and (tgl_checkout > p_tgl_checkin));

    return v_count = 0;
end;
$$ language plpgsql;


-- stored procedure

create or replace procedure proses_checkin(
    p_id_reservasi int
) as $$
begin
    update reservasi
    set status_reservasi = 'check-in'
    where id_reservasi = p_id_reservasi;

    update kamar
    set status = 'terisi'
    where id_kamar = (select id_kamar from reservasi where id_reservasi = p_id_reservasi);
end;
$$ language plpgsql;


create or replace procedure proses_checkout(
    p_id_reservasi int
) as $$
begin
    update reservasi
    set status_reservasi = 'selesai'
    where id_reservasi = p_id_reservasi;

    update kamar
    set status = 'tersedia'
    where id_kamar = (select id_kamar from reservasi where id_reservasi = p_id_reservasi);

    update pembayaran
    set status_bayar = 'lunas',
        tgl_bayar = current_timestamp,
        metode_bayar = coalesce(metode_bayar, 'cash')
    where id_reservasi = p_id_reservasi
      and status_bayar = 'belum lunas';
end;
$$ language plpgsql;


-- view & materialized view

create or replace view v_kamar_tersedia as
select 
    k.id_kamar,
    k.nomor_kamar,
    k.lantai,
    tk.nama_tipe,
    tk.harga_per_malam,
    tk.kapasitas,
    k.status
from kamar k
join tipe_kamar tk on k.id_tipe = tk.id_tipe
where k.status = 'tersedia';

create or replace view v_reservasi_detail as
select 
    r.id_reservasi,
    r.tgl_reservasi,
    r.tgl_checkin,
    r.tgl_checkout,
    (r.tgl_checkout - r.tgl_checkin) as lama_menginap,
    r.jumlah_tamu,
    r.status_reservasi,
    t.id_tamu,
    t.nama_tamu,
    t.no_telp,
    t.email,
    k.nomor_kamar,
    k.lantai,
    tk.nama_tipe,
    tk.harga_per_malam,
    (tk.harga_per_malam * (r.tgl_checkout - r.tgl_checkin)) as total_harga,
    coalesce(p.status_bayar, 'belum lunas') as status_bayar,
    p.metode_bayar,
    p.tgl_bayar
from reservasi r
join tamu t on r.id_tamu = t.id_tamu
join kamar k on r.id_kamar = k.id_kamar
join tipe_kamar tk on k.id_tipe = tk.id_tipe
left join pembayaran p on r.id_reservasi = p.id_reservasi;

create or replace view v_laporan_okupansi as
select 
    date_trunc('month', r.tgl_checkin) as bulan,
    count(distinct r.id_reservasi) as total_reservasi,
    count(distinct r.id_kamar) as kamar_terpakai,
    (select count(*) from kamar) as total_kamar,
    round(count(distinct r.id_kamar)::numeric / (select count(*) from kamar)::numeric * 100, 2) as tingkat_okupansi,
    sum(tk.harga_per_malam * (r.tgl_checkout - r.tgl_checkin)) as total_pendapatan
from reservasi r
join kamar k on r.id_kamar = k.id_kamar
join tipe_kamar tk on k.id_tipe = tk.id_tipe
where r.status_reservasi in ('check-in', 'selesai')
group by date_trunc('month', r.tgl_checkin)
order by bulan desc;

create materialized view mv_statistik_hotel as
select 
    (select count(*) from kamar) as total_kamar,

    (select count(distinct r.id_kamar)
	from reservasi r
    where r.status_reservasi = 'check-in') as kamar_terisi,

    ((select count(*) from kamar) - (select count(distinct r.id_kamar)
	from reservasi r
    where r.status_reservasi = 'check-in')) as kamar_tersedia,

    (select count(*) from reservasi where status_reservasi = 'pending') as reservasi_pending,
    (select count(*) from reservasi where status_reservasi = 'confirmed') as reservasi_confirmed,
    (select count(*) from reservasi where status_reservasi = 'check-in') as tamu_checkin,
    (select count(*) from tamu) as total_tamu,

    current_timestamp as last_refresh;

refresh materialized view mv_statistik_hotel;

-- update pembayaran lunas
update pembayaran p
set jumlah = sub.total_harga
from (select 
		r.id_reservasi,
		tk.harga_per_malam * (r.tgl_checkout - r.tgl_checkin) as total_harga
	from reservasi r
	join kamar k on r.id_kamar = k.id_kamar
	join tipe_kamar tk on k.id_tipe = tk.id_tipe) as sub
where p.id_reservasi = sub.id_reservasi
	and p.status_bayar = 'lunas';

-- update pembayaran belum lunas
update pembayaran p
set jumlah = sub.total_harga
from (select
		r.id_reservasi,
		tk.harga_per_malam * (r.tgl_checkout - r.tgl_checkin) as total_harga
	from reservasi r
	join kamar k on r.id_kamar = k.id_kamar
	join tipe_kamar tk on k.id_tipe = tk.id_tipe) as sub
where p.id_reservasi = sub.id_reservasi
	and p.status_bayar = 'belum lunas';

refresh materialized view mv_statistik_hotel;

-- cek index
select * from pembayaran where status_bayar = 'belum lunas';
select * from kamar where status = 'terisi';

-- indexing B-Tree
create index if not exists idx_pembayaran_status on pembayaran(status_bayar);
create index if not exists idx_kamar_status on kamar(status);

-- explain analyze pembayaran (status bayar) 
explain analyze
select * from pembayaran where status_bayar = 'belum lunas';

-- explain analyze kamar (status)
explain analyze
select * from kamar where status = 'terisi';