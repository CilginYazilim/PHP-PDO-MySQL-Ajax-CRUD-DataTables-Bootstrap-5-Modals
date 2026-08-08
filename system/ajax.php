<?php
/**
 * =====================================================================
 *  AJAX UÇ NOKTASI (Endpoint) – Tüm CRUD işlemlerinin merkezi
 *  cilginyazilim.com – PHP PDO MySQL Ajax CRUD
 * ---------------------------------------------------------------------
 *  Bu dosya HTML üretmez; SADECE JSON döndürür.
 *  index.php'deki JavaScript buraya POST isteği atar, gelen JSON'a
 *  bakarak ekranı günceller.
 *
 *  "action" parametresi hangi işlemin yapılacağını belirler:
 *  ---------------------------------------------------------------
 *    action=list    → DataTables için kayıt listesi (varsayılan)
 *    action=add     → Yeni kayıt ekle
 *    action=edit    → Mevcut kaydı güncelle
 *    action=fetch   → Tek kaydı getir (detay ve düzenleme için)
 *    action=delete  → Kaydı sil
 *  ---------------------------------------------------------------
 *
 *  NEDEN TEK DOSYA?
 *  Her işlem için ayrı dosya (add.php, delete.php...) açmak yerine
 *  tek giriş noktası kullanmak, güvenlik kontrollerini (CSRF, POST
 *  zorunluluğu, hata yakalama) TEK YERDE toplamayı sağlar. Böylece
 *  bir kontrolü yanlışlıkla bir dosyada unutma riski kalmaz.
 * =====================================================================
 */

declare(strict_types=1);

// Ayarlar + veritabanı bağlantısı ($db) ve yardımcı fonksiyonlar.
require __DIR__ . '/config.php';
require __DIR__ . '/function.php';

/* ---------------------------------------------------------------------
 *  GÜVENLİK KONTROLÜ 1: Sadece POST kabul edilir.
 * ---------------------------------------------------------------------
 *  Veri değiştiren işlemler asla GET ile yapılmamalıdır. Aksi halde
 *  <img src="ajax.php?action=delete&id=5"> gibi basit bir etiket bile
 *  kayıt silebilirdi.
 * ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Yalnızca POST istekleri kabul edilir.', 405);
}

// İstenen işlemi oku. Gelmemişse varsayılan olarak listeleme yap.
// ?? operatörü: "solundaki değer yoksa/null ise sağdakini kullan".
$action = isset($_POST['action']) ? strtolower(trim((string) $_POST['action'])) : 'list';

/* ---------------------------------------------------------------------
 *  YÖNLENDİRİCİ (Router)
 * ---------------------------------------------------------------------
 *  try/catch bloğu: İçeride NEREDE hata olursa olsun buraya düşer.
 *  Böylece kullanıcı çirkin bir PHP hata sayfası yerine düzgün bir
 *  JSON hata mesajı görür ve JavaScript bunu işleyebilir.
 * ------------------------------------------------------------------ */
try {
    switch ($action) {
        case 'add':
        case 'edit':
            // Ekleme ve güncelleme çok benzer olduğu için aynı fonksiyon
            // ikisini de yönetir; farkı $action belirler.
            handle_save($db, $action);
            break;

        case 'fetch':
            handle_fetch($db);
            break;

        case 'delete':
            handle_delete($db);
            break;

        case 'list':
        default:
            handle_list($db);
            break;
    }
} catch (PDOException $e) {
    // Veritabanı kaynaklı hatalar (bağlantı koptu, sorgu hatalı...)
    error_log('[CRUD] Veritabani hatasi: ' . $e->getMessage());

    // GÜVENLİK: Canlı ortamda hata detayı kullanıcıya GÖSTERİLMEZ.
    // Tablo/sütun isimleri saldırgana bilgi verir.
    json_error(
        APP_DEBUG ? 'Veritabanı hatası: ' . $e->getMessage()
                  : 'Beklenmeyen bir veritabanı hatası oluştu.',
        500
    );
} catch (Throwable $e) {
    // Throwable: PHP 7+ ile tüm hata ve istisnaların ortak atası.
    // Yani buraya AKLA GELEN HER hata düşer.
    error_log('[CRUD] Hata: ' . $e->getMessage());

    json_error(
        APP_DEBUG ? 'Hata: ' . $e->getMessage() : 'Beklenmeyen bir hata oluştu.',
        500
    );
}


/* =====================================================================
 *  1) KAYIT EKLEME / GÜNCELLEME
 * =====================================================================
 *  Adımlar:
 *    a) CSRF anahtarını doğrula
 *    b) Ad/soyad alanlarını doğrula
 *    c) Düzenleme ise kaydın var olduğunu kontrol et
 *    d) Görsel gönderildiyse doğrula ve kaydet
 *    e) Hata varsa 422 ile geri dön, yoksa veritabanına yaz
 * ------------------------------------------------------------------ */
function handle_save(PDO $db, string $action): void
{
    // (a) Sahte istek koruması. Geçersizse burada durur (419 döner).
    require_csrf();

    // Alan bazlı hataları biriktireceğimiz dizi.
    // Örn: ['name' => 'Ad alanı boş bırakılamaz.']
    $errors = [];

    /* (b) ALAN DOĞRULAMA -------------------------------------------
     * validate_name() iki değer döndürür: [temizlenmiş değer, hata].
     * PHP'nin "list assignment" özelliğiyle ikisini tek satırda alırız.
     * -------------------------------------------------------------- */
    [$name, $nameError]       = validate_name($_POST['name'] ?? '', 'Ad');
    [$surname, $surnameError] = validate_name($_POST['surname'] ?? '', 'Soyad');

    if ($nameError !== null) {
        $errors['name'] = $nameError;
    }
    if ($surnameError !== null) {
        $errors['surname'] = $surnameError;
    }

    $isEdit  = ($action === 'edit');
    $current = null; // Düzenlemede mevcut kaydın verisi burada tutulur.

    /* (c) DÜZENLEME KONTROLÜ ---------------------------------------
     * filter_input(...FILTER_VALIDATE_INT): Gelen değerin gerçekten
     * tam sayı olduğunu doğrular. "5abc" veya "1 OR 1=1" gibi değerler
     * false döner ve işlem durur.
     * -------------------------------------------------------------- */
    if ($isEdit) {
        $id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($id === false || $id === null) {
            json_error('Geçersiz kayıt numarası.');
        }

        $current = find_user($db, $id);

        if ($current === null) {
            json_error('Güncellenecek kayıt bulunamadı.', 404);
        }
    }

    /* (d) GÖRSEL YÜKLEME -------------------------------------------
     * UPLOAD_ERR_NO_FILE : Kullanıcı dosya seçmedi demektir.
     * Bu bir HATA DEĞİLDİR; görsel alanı zorunlu değil.
     * -------------------------------------------------------------- */
    $hasNewImage = isset($_FILES['image_user'])
        && is_array($_FILES['image_user'])
        && (int) $_FILES['image_user']['error'] !== UPLOAD_ERR_NO_FILE;

    $newImage = null; // Yeni yüklenen dosyanın adı (yoksa null kalır)

    if ($hasNewImage) {
        try {
            if ($errors === []) {
                // Form temizse dosyayı gerçekten diske kaydet.
                $newImage = upload_image($_FILES['image_user']);
            } else {
                // Formda zaten hata var; dosyayı diske yazmadan sadece
                // doğrula. Böylece kullanıcı tüm hataları bir seferde görür
                // ama diskte gereksiz dosya birikmez.
                upload_image_precheck($_FILES['image_user']);
            }
        } catch (RuntimeException $e) {
            $errors['image_user'] = $e->getMessage();
        }
    }

    /* (e) HATA VARSA GERİ DÖN --------------------------------------
     * 422 = "Unprocessable Entity" (doğrulama hatası) HTTP kodu.
     * JavaScript bu kodu görünce hataları alanların altına yazar.
     * -------------------------------------------------------------- */
    if ($errors !== []) {
        // Dosya kaydedildiyse ama başka bir alan hatalıysa, yetim
        // kalmaması için dosyayı geri sil.
        if ($newImage !== null) {
            delete_upload($newImage);
        }

        json_error('Lütfen formdaki hataları düzeltin.', 422, ['errors' => $errors]);
    }

    /* --- GÜNCELLEME ------------------------------------------------ */
    if ($isEdit) {
        // Yeni görsel yoksa eskisini koru.
        // ÖNEMLİ: Eski görselin adını istemciden DEĞİL veritabanından
        // alıyoruz. Aksi halde saldırgan "../config.php" gönderip
        // sunucudaki başka dosyaları sildirebilirdi.
        $image = $newImage ?? (string) $current['image'];

        $stmt = $db->prepare(
            'UPDATE users SET name = :name, surname = :surname, image = :image WHERE id = :id'
        );

        // Değerler ayrı gönderilir, sorguya yapıştırılmaz → SQL Injection imkânsız.
        $stmt->execute([
            ':name'    => $name,
            ':surname' => $surname,
            ':image'   => $image,
            ':id'      => $current['id'],
        ]);

        // Yeni görsel yüklendiyse eski dosyayı diskten temizle.
        if ($newImage !== null && $current['image'] !== '') {
            delete_upload((string) $current['image']);
        }

        json_success('Kayıt başarıyla güncellendi.', ['id' => (int) $current['id']]);
    }

    /* --- YENİ KAYIT ------------------------------------------------ */
    $stmt = $db->prepare(
        'INSERT INTO users (name, surname, image) VALUES (:name, :surname, :image)'
    );

    $stmt->execute([
        ':name'    => $name,
        ':surname' => $surname,
        ':image'   => $newImage ?? '', // Görsel yoksa boş string
    ]);

    // lastInsertId(): Az önce eklenen kaydın otomatik ID'sini verir.
    json_success('Kayıt başarıyla eklendi.', ['id' => (int) $db->lastInsertId()]);
}


/**
 * Görseli DİSKE YAZMADAN sadece doğrular.
 *
 * Formda başka hatalar varken kullanılır: kullanıcıya "görsel de
 * hatalı" diyebilmemiz gerekir ama dosyayı kaydetmemize gerek yoktur.
 *
 * @param array<string,mixed> $file $_FILES['image_user']
 * @throws RuntimeException Dosya geçersizse
 */
function upload_image_precheck(array $file): void
{
    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Dosya yüklenirken bir hata oluştu.');
    }

    if ((int) $file['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException(
            'Görsel boyutu en fazla ' . (int) (UPLOAD_MAX_BYTES / 1024 / 1024) . ' MB olabilir.'
        );
    }

    // getimagesize(): Dosyanın gerçekten görsel olup olmadığını
    // İÇERİĞİNDEN anlar. Uzantıya asla güvenilmez.
    $info = @getimagesize($file['tmp_name']);

    if ($info === false || !array_key_exists(strtolower((string) $info['mime']), ALLOWED_IMAGE_TYPES)) {
        throw new RuntimeException('Yalnızca JPG, PNG, GIF ve WEBP formatları desteklenir.');
    }
}


/* =====================================================================
 *  2) TEK KAYIT GETİRME (Detay modalı ve düzenleme formu için)
 * ================================================================== */
function handle_fetch(PDO $db): void
{
    require_csrf();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if ($id === false || $id === null) {
        json_error('Geçersiz kayıt numarası.');
    }

    $user = find_user($db, $id);

    if ($user === null) {
        json_error('Kayıt bulunamadı.', 404);
    }

    /* NEDEN HTML DEĞİL DE HAM VERİ DÖNÜYORUZ?
     * Sunucudan hazır HTML göndermek yerine ham veri gönderip
     * ekranı JavaScript'in doldurması daha güvenlidir: JavaScript
     * .text() kullandığında içerik asla HTML olarak yorumlanmaz,
     * yani XSS riski ortadan kalkar.
     *
     * rawurlencode(): Dosya adında boşluk/özel karakter varsa
     * URL'nin bozulmamasını sağlar.
     */
    json_response([
        'success'   => true,
        'id'        => (int) $user['id'],
        'name'      => $user['name'],
        'surname'   => $user['surname'],
        'image'     => $user['image'],
        'image_url' => $user['image'] !== '' ? UPLOAD_URL . rawurlencode((string) $user['image']) : '',
        'tarih'     => format_date($user['tarih']),
    ]);
}


/* =====================================================================
 *  3) KAYIT SİLME
 * ================================================================== */
function handle_delete(PDO $db): void
{
    require_csrf();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if ($id === false || $id === null) {
        json_error('Geçersiz kayıt numarası.');
    }

    // Kaydı silmeden ÖNCE görsel adını öğren; sonra öğrenemeyiz.
    $image = get_user_image($db, $id);

    $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);

    // rowCount(): Kaç satır etkilendi? 0 ise böyle bir kayıt yoktu.
    if ($stmt->rowCount() === 0) {
        json_error('Silinecek kayıt bulunamadı.', 404);
    }

    // Veritabanı kaydı gitti; şimdi diskteki dosyayı da temizle.
    // Bu yapılmazsa zamanla "yetim dosyalar" birikir ve disk şişer.
    if ($image !== '') {
        delete_upload($image);
    }

    json_success('Kayıt başarıyla silindi.', ['id' => $id]);
}


/* =====================================================================
 *  4) LİSTELEME (DataTables Server-Side Protokolü)
 * =====================================================================
 *  DataTables sunucudan ŞU YAPIDA bir JSON bekler:
 *  {
 *    "draw": 1,                 // İstek sırası (aynen geri dönmeli)
 *    "recordsTotal": 50,        // Filtresiz TOPLAM kayıt
 *    "recordsFiltered": 12,     // Aramadan SONRAKİ kayıt sayısı
 *    "data": [ [...], [...] ]   // Satırlar (her satır bir dizi)
 *  }
 *
 *  recordsTotal ve recordsFiltered'ı doğru vermek ŞARTTIR;
 *  sayfalama butonları bu iki sayıya göre hesaplanır.
 * ------------------------------------------------------------------ */
function handle_list(PDO $db): void
{
    /* ---------------------------------------------------------------
     *  SIRALAMA GÜVENLİĞİ (En kritik kısım!)
     * ---------------------------------------------------------------
     *  Sütun adları ve ASC/DESC, prepared statement ile bind EDİLEMEZ;
     *  SQL'in yapısal parçalarıdır. Bu yüzden kullanıcıdan gelen değeri
     *  doğrudan sorguya koymak SQL Injection'a davetiye çıkarır.
     *
     *  ÇÖZÜM: Beyaz liste (whitelist). Kullanıcı bize sadece bir SAYI
     *  gönderir; o sayıya karşılık gelen sütun adını BİZ seçeriz.
     *  Listede olmayan bir sayı gelirse 'id'ye düşer.
     *
     *  Anahtarlar index.php'deki tablo sütun sırasıyla eşleşir:
     *     0=#  1=Foto  2=Ad  3=Soyad  4=Tarih  5=İşlemler
     *  1 ve 5 listede yok, çünkü veritabanı sütunu değiller.
     * ------------------------------------------------------------- */
    $sortableColumns = [
        0 => 'id',
        2 => 'name',
        3 => 'surname',
        4 => 'tarih',
    ];

    // DataTables'ın gönderdiği standart parametreler
    $draw   = (int) ($_POST['draw'] ?? 1);              // İstek sayacı
    $start  = max(0, (int) ($_POST['start'] ?? 0));     // Kaçıncı kayıttan başla
    $length = (int) ($_POST['length'] ?? 10);           // Kaç kayıt getir
    $search = trim((string) ($_POST['search']['value'] ?? ''));

    $orderColumn    = (int) ($_POST['order'][0]['column'] ?? 0);
    $orderDirection = strtolower((string) ($_POST['order'][0]['dir'] ?? 'desc'));

    // Beyaz liste uygulanıyor: güvenli değerlere dönüştür.
    $orderBy  = $sortableColumns[$orderColumn] ?? 'id';
    $orderDir = ($orderDirection === 'asc') ? 'ASC' : 'DESC';

    /* --- SORGUYU KUR ---------------------------------------------- */
    $sql    = 'SELECT id, name, surname, image, tarih FROM users';
    $params = [];

    if ($search !== '') {
        /* NOT: PDO::ATTR_EMULATE_PREPARES kapalıyken aynı isimli
         * yer tutucu (:search) iki kez KULLANILAMAZ. Bu yüzden iki
         * ayrı isim veriyoruz. Bu, yeni başlayanların sık takıldığı
         * "Invalid parameter number" hatasının sebebidir. */
        $sql .= ' WHERE name LIKE :search_name OR surname LIKE :search_surname';

        // escape_like(): Kullanıcı "%" yazarsa bunu joker karakter
        // değil, düz metin olarak arasın diye kaçışlar.
        $pattern = '%' . escape_like($search) . '%';
        $params[':search_name']    = $pattern;
        $params[':search_surname'] = $pattern;
    }

    // Beyaz listeden geçmiş güvenli değerler; kullanıcı girdisi değil.
    $sql .= sprintf(' ORDER BY `%s` %s', $orderBy, $orderDir);

    /* LIMIT / OFFSET de bind edilemez. Ancak %d ile biçimlendirdiğimiz
     * için PHP değeri zorla tam sayıya çevirir; metin geçemez.
     * min(..., 500): Kötü niyetli biri length=999999 gönderip sunucuyu
     * yormasın diye üst sınır koyduk. */
    if ($length > 0) {
        $sql .= sprintf(' LIMIT %d OFFSET %d', min($length, 500), $start);
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    /* --- SATIRLARI TABLO BİÇİMİNE ÇEVİR ---------------------------
     * Her satır, sütun sırasına birebir uyan bir DİZİ olmalıdır.
     * Dizi uzunluğu index.php'deki <th> sayısıyla aynı olmalı (6).
     * ------------------------------------------------------------- */
    $data = [];

    foreach ($rows as $row) {
        $id       = (int) $row['id'];
        $image    = (string) $row['image'];
        $fullName = $row['name'] . ' ' . $row['surname'];

        /* --- Foto sütunu ---
         * Görsel varsa <img>, yoksa adın baş harfinden rozet.
         * e() = htmlspecialchars → XSS koruması. Veritabanındaki
         * bir değerde <script> olsaydı bile zararsız metne dönüşür. */
        if ($image !== '') {
            $imageHtml = '<img src="' . e(UPLOAD_URL . rawurlencode($image)) . '"'
                       . ' class="cy-avatar" alt="' . e($fullName) . '" loading="lazy">';
        } else {
            $initial = mb_strtoupper(mb_substr((string) $row['name'], 0, 1, 'UTF-8'), 'UTF-8');
            $imageHtml = '<span class="cy-avatar cy-avatar--initial" title="Görsel yok">'
                       . e($initial) . '</span>';
        }

        /* --- İşlemler sütunu ---
         * Üç buton tek hücrede toplandı: Detay, Düzenle, Sil.
         * data-id : JavaScript hangi kayıtla ilgilendiğini buradan anlar.
         * js-*    : Sadece JavaScript'in tutunması için sınıf; stil için
         *           kullanılmaz (böylece tasarım değişse de kod bozulmaz).
         * title/aria-label: Fareyle üzerine gelince ipucu + ekran
         *           okuyucular için erişilebilirlik. */
        $actionsHtml =
            '<div class="cy-actions">'
            . '<button type="button" class="cy-btn-icon cy-btn-icon--view js-view"'
                . ' data-id="' . $id . '" title="Detayı Gör" aria-label="Detayı gör">&#128065;</button>'
            . '<button type="button" class="cy-btn-icon cy-btn-icon--edit js-edit"'
                . ' data-id="' . $id . '" title="Düzenle" aria-label="Düzenle">&#9998;</button>'
            . '<button type="button" class="cy-btn-icon cy-btn-icon--delete js-delete"'
                . ' data-id="' . $id . '" data-label="' . e($fullName) . '"'
                . ' title="Sil" aria-label="Sil">&#128465;</button>'
            . '</div>';

        // Sütun sırası: #, Foto, Ad, Soyad, Kayıt Tarihi, İşlemler
        $data[] = [
            $id,
            $imageHtml,
            e((string) $row['name']),
            e((string) $row['surname']),
            '<span class="cy-nowrap">' . e(format_date($row['tarih'])) . '</span>',
            $actionsHtml,
        ];
    }

    /* --- YANITI GÖNDER --------------------------------------------
     * recordsTotal    : Arama yokmuş gibi TOPLAM kayıt (üstteki sayaç)
     * recordsFiltered : Aramadan sonra kalan kayıt (sayfalama bunu kullanır)
     * İkisi farklı sorgudur; karıştırılırsa sayfalama bozulur.
     * ------------------------------------------------------------- */
    json_response([
        'draw'            => $draw,
        'recordsTotal'    => count_users($db),
        'recordsFiltered' => count_users($db, $search),
        'data'            => $data,
    ]);
}
