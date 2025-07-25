-- Script untuk mengaudit konsistensi data slot parkir
-- Mencari slot parkir yang memiliki id_part tidak sesuai dengan id_blok

-- Query 1: Cek slot parkir dengan id_part yang tidak sesuai dengan id_blok
SELECT 
    sp.id as slot_id,
    sp.slot_name,
    sp.id_blok as slot_id_blok,
    sp.id_part as slot_id_part,
    p.id_blok as part_id_blok,
    p.nama as part_nama,
    'INCONSISTENT' as status
FROM slot__parkirs sp 
JOIN parts p ON sp.id_part = p.id 
WHERE sp.id_blok != p.id_blok;

-- Query 2: Statistik konsistensi data
SELECT 
    COUNT(*) as total_slots,
    SUM(CASE WHEN sp.id_blok = p.id_blok THEN 1 ELSE 0 END) as consistent_slots,
    SUM(CASE WHEN sp.id_blok != p.id_blok THEN 1 ELSE 0 END) as inconsistent_slots,
    ROUND((SUM(CASE WHEN sp.id_blok = p.id_blok THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as consistency_percentage
FROM slot__parkirs sp 
JOIN parts p ON sp.id_part = p.id;

-- Query 3: Daftar blok dan part untuk referensi
SELECT 
    b.id as blok_id,
    b.nama as blok_nama,
    p.id as part_id,
    p.nama as part_nama,
    COUNT(sp.id) as total_slots_in_part
FROM bloks b
LEFT JOIN parts p ON b.id = p.id_blok
LEFT JOIN slot__parkirs sp ON p.id = sp.id_part
GROUP BY b.id, b.nama, p.id, p.nama
ORDER BY b.id, p.id;

-- Query 4: Slot parkir yang memiliki id_part NULL
SELECT 
    sp.id as slot_id,
    sp.slot_name,
    sp.id_blok,
    sp.id_part,
    'NULL_PART' as status
FROM slot__parkirs sp 
WHERE sp.id_part IS NULL;