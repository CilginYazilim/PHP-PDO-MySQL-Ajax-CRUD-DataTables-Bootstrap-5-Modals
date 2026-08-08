<?php
/**
 * =====================================================================
 *  YARDIMCI FONKSİYONLAR
 *  cilginyazilim.com – PHP PDO MySQL Ajax CRUD
 * ---------------------------------------------------------------------
 *  Burada tekrar tekrar kullanılan küçük işler toplanır:
 *  JSON yanıt üretme, CSRF, doğrulama, dosya yükleme, tarih biçimleme.
 *
 *  TASARIM KARARI: Bu dosya config.php'yi DAHİL ETMEZ.
 *  Veritabanına ihtiyaç duyan fonksiyonlar PDO nesnesini PARAMETRE
 *  olarak alır: count_users($db) gibi.
 *
 *  Neden? Eski kodda her fonksiyon içinde include('config.php')
 *  vardı ve bu, her çağrıda YENİ bir veritabanı bağlantısı açıyordu.
 *  Tek sayfada 3-4 gereksiz bağlantı demekti. Parametre olarak
 *  geçirmek hem hızlı hem de test edilebilir bir yapı sağlar.
 *  (Bu yaklaşımın adı: "Dependency Injection" / Bağımlılık Enjeksiyonu)
 * =====================================================================
 */

declare(strict_types=1);


/* =====================================================================
 *  BÖLÜM 1 – ÇIKTI VE YANIT
 * ================================================================== */

/**
 * Metni HTML'e güvenle basmak için kaçışlar (XSS koruması).
 *
 * XSS NEDİR?
 *   Kullanıcı ad alanına <script>alert(1)</script> yazarsa ve biz
 *   bunu ekrana olduğu gibi basarsak, tarayıcı bunu KOD olarak
 *   çalıştırır. Saldırgan böylece oturum çerezlerini çalabilir.
 *
 * htmlspecialchars() bu karakterleri zararsız hale getirir:
 *   <  →  &lt;      >  →  &gt;      "  →  &quot;      '  →  &#039;
 *
 * ENT_QUOTES     : Hem tek hem çift tırnağı da kaçışla
 * ENT_SUBSTITUTE : Bozuk karakter gelirse hata verme, değiştir
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * JSON yanıtı gönderir ve script'i sonlandırır.
 *
 * exit kullanmamızın sebebi: Yanıt gönderildikten sonra kodun devam
 * edip ikinci bir JSON basmasını önlemek. İki JSON arka arkaya
 * gelirse JavaScript "Unexpected token" hatası verir.
 *
 * @param array<string,mixed> $payload JSON'a çevrilecek dizi
 * @param int                 $status  HTTP durum kodu (200, 404, 422...)
 */
function json_response(array $payload, int $status = 200): void
{
    // headers_sent(): Daha önce ekrana bir şey basıldıysa (örn. bir
    // boşluk veya uyarı) başlık gönderilemez; hata almamak için kontrol.
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        // Tarayıcının içerik türünü tahmin etmeye çalışmasını engeller.
        // Bazı eski tarayıcı açıklarını kapatan basit bir önlemdir.
        header('X-Content-Type-Options: nosniff');
    }

    // JSON_UNESCAPED_UNICODE: Türkçe karakterler ç gibi kodlanmasın,
    // doğrudan "ç" olarak yazılsın. Hem okunur hem daha küçük.
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Standart BAŞARI yanıtı.
 * JavaScript tarafı her zaman aynı alanları beklediği için
 * yanıt biçimini sabitlemek işleri kolaylaştırır.
 */
function json_success(string $description, array $extra = []): void
{
    json_response(array_merge([
        'success'     => true,
        'type'        => 'success',
        'description' => $description,
    ], $extra));
}

/**
 * Standart HATA yanıtı.
 *
 * Kullanılan HTTP kodları:
 *   400 → Geçersiz istek (hatalı ID gibi)
 *   404 → Kayıt bulunamadı
 *   419 → CSRF anahtarı geçersiz / oturum düştü
 *   422 → Form doğrulama hatası
 *   500 → Sunucu hatası
 */
function json_error(string $description, int $status = 400, array $extra = []): void
{
    json_response(array_merge([
        'success'     => false,
        'type'        => 'danger',
        'description' => $description,
    ], $extra), $status);
}


/* =====================================================================
 *  BÖLÜM 2 – CSRF KORUMASI
 * =====================================================================
 *  CSRF (Cross-Site Request Forgery) NEDİR?
 *  Siz sitemize giriş yapmışken başka bir kötü niyetli siteyi
 *  ziyaret edersiniz. O site gizlice bizim ajax.php'mize "kaydı sil"
 *  isteği gönderir. Tarayıcı çerezlerinizi otomatik eklediği için
 *  sunucu bunu SİZİN yaptığınızı sanır.
 *
 *  ÇÖZÜM: Her oturuma özel, tahmin edilemez bir anahtar üretiriz.
 *  Bu anahtarı sayfamıza gömeriz. Başka bir site bu anahtarı
 *  okuyamaz (tarayıcının "same-origin policy" kuralı engeller),
 *  dolayısıyla geçerli istek üretemez.
 * ================================================================== */

/**
 * Oturuma bağlı CSRF anahtarını döndürür (yoksa üretir).
 *
 * random_bytes(32) : Kriptografik olarak güvenli rastgele veri.
 *                    rand() veya mt_rand() KULLANMAYIN, tahmin edilebilir.
 * bin2hex()        : Baytları okunabilir metne çevirir (64 karakter).
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Gelen isteğin CSRF anahtarını doğrular; geçersizse 419 ile durur.
 *
 * Anahtar iki yerden okunabilir:
 *   - POST verisi içinde (form gönderimi)
 *   - X-CSRF-Token başlığı (saf AJAX istekleri)
 *
 * hash_equals() NEDEN?
 *   Normal "===" karşılaştırması ilk farklı karakterde durur.
 *   Saldırgan yanıt SÜRESİNİ ölçerek anahtarı karakter karakter
 *   tahmin edebilir (buna "timing attack" denir). hash_equals()
 *   her zaman aynı sürede çalışarak bunu engeller.
 */
function require_csrf(): void
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (!is_string($token) || $token === ''
        || empty($_SESSION['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], $token)) {

        json_error('Oturum doğrulaması başarısız. Lütfen sayfayı yenileyin.', 419);
    }
}


/* =====================================================================
 *  BÖLÜM 3 – DOĞRULAMA
 * =====================================================================
 *  ALTIN KURAL: İstemci (JavaScript) tarafındaki doğrulama sadece
 *  KULLANICI DENEYİMİ içindir. Kötü niyetli biri tarayıcıyı hiç
 *  kullanmadan doğrudan sunucuya istek atabilir. Bu yüzden her
 *  kontrol SUNUCUDA TEKRAR yapılmalıdır.
 * ================================================================== */

/**
 * Ad / soyad alanını temizler ve doğrular.
 *
 * Dönüş değeri iki elemanlı dizidir:
 *   [0] → temizlenmiş değer
 *   [1] → hata mesajı, hata yoksa null
 *
 * Kullanımı:
 *   [$name, $error] = validate_name($_POST['name'], 'Ad');
 *
 * @return array{0:string,1:?string}
 */
function validate_name(?string $value, string $label): array
{
    // trim()  : Baştaki/sondaki boşlukları siler.
    // preg_replace('/\s+/u', ' ') : Aradaki çoklu boşlukları teke indirir.
    //   ("Ali    Veli" → "Ali Veli")
    // Sondaki /u : Desenin UTF-8 (Türkçe karakterli) metinle çalışmasını sağlar.
    $value = trim(preg_replace('/\s+/u', ' ', (string) $value));

    if ($value === '') {
        return ['', $label . ' alanı boş bırakılamaz.'];
    }

    // mb_strlen(): Çok baytlı karakterleri doğru sayar.
    // strlen("Çılgın") = 8 (yanlış), mb_strlen("Çılgın") = 6 (doğru)
    $length = mb_strlen($value, 'UTF-8');

    if ($length < NAME_MIN_LENGTH) {
        return [$value, $label . ' en az ' . NAME_MIN_LENGTH . ' karakter olmalıdır.'];
    }

    if ($length > NAME_MAX_LENGTH) {
        return [$value, $label . ' en fazla ' . NAME_MAX_LENGTH . ' karakter olabilir.'];
    }

    /* Desen açıklaması:
     *   \p{L}  → Herhangi bir dildeki HARF (ç, ğ, ş, ü, é, 漢 ...)
     *   \p{M}  → Harflere eklenen işaretler (aksan vb.)
     *   \s     → Boşluk
     *   . ' -  → Nokta, kesme işareti, tire ("Ayşe-Nur", "D'Angelo")
     * Bu sayede rakam, <, >, ; gibi karakterler kabul edilmez. */
    if (!preg_match("/^[\p{L}\p{M}\s.'-]+$/u", $value)) {
        return [$value, $label . ' yalnızca harf, boşluk, nokta, kesme işareti ve tire içerebilir.'];
    }

    return [$value, null];
}


/* =====================================================================
 *  BÖLÜM 4 – GÖRSEL YÜKLEME
 * =====================================================================
 *  DOSYA YÜKLEME, WEB'İN EN TEHLİKELİ KISMIDIR.
 *  Saldırgan "shell.php" yükleyip sunucunuzu ele geçirebilir.
 *  Bu yüzden burada üç katmanlı savunma vardır:
 *
 *    1. Dosyanın gerçekten görsel olduğu İÇERİĞİNDEN doğrulanır
 *    2. Yeni dosya adı ve uzantısı BİZ belirleriz (kullanıcı değil)
 *    3. upload/.htaccess ile o klasörde PHP çalıştırma kapatılır
 * ================================================================== */

/**
 * Yüklenen dosyayı doğrular ve upload/ klasörüne taşır.
 *
 * @param array<string,mixed> $file $_FILES['image_user'] dizisi
 * @throws RuntimeException Doğrulama veya taşıma başarısız olursa
 * @return string Diskte oluşan yeni dosya adı (örn. "a3f9....png")
 */
function upload_image(array $file): string
{
    // $_FILES yapısı beklendiği gibi değilse (manipüle edilmişse) dur.
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Geçersiz dosya yükleme isteği.');
    }

    /* PHP, yükleme sonucunu bir hata koduyla bildirir.
     * Bunları tek tek ele almak, kullanıcıya anlamlı mesaj vermeyi sağlar. */
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break; // Sorun yok, devam
        case UPLOAD_ERR_INI_SIZE:   // php.ini limitini aştı
        case UPLOAD_ERR_FORM_SIZE:  // Formdaki MAX_FILE_SIZE limitini aştı
            throw new RuntimeException('Dosya boyutu sunucu limitini aşıyor.');
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('Dosya seçilmedi.');
        default:
            throw new RuntimeException('Dosya yüklenirken bir hata oluştu.');
    }

    // Boyut kontrolü (php.ini limitinden bağımsız kendi kuralımız)
    if ($file['size'] <= 0 || $file['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException(
            'Görsel boyutu en fazla ' . (int) (UPLOAD_MAX_BYTES / 1024 / 1024) . ' MB olabilir.'
        );
    }

    /* is_uploaded_file(): Dosyanın gerçekten HTTP yüklemesiyle geldiğini
     * doğrular. Bu kontrol olmazsa saldırgan tmp_name alanına
     * "/etc/passwd" gibi bir sistem dosyası yazıp onu kopyalatabilir. */
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Geçersiz dosya kaynağı.');
    }

    /* --- EN KRİTİK KONTROL ---------------------------------------
     * getimagesize() dosyanın İÇERİĞİNİ okur. Gerçek bir görsel
     * değilse false döner. Uzantı ".png" olsa bile içinde PHP kodu
     * varsa buradan geçemez.
     *
     * Başındaki @ : Geçersiz dosyada PHP uyarı basmasın diye.
     *               Hatayı biz zaten aşağıda ele alıyoruz. */
    $imageInfo = @getimagesize($file['tmp_name']);

    if ($imageInfo === false) {
        throw new RuntimeException('Yüklenen dosya geçerli bir görsel değil.');
    }

    $mime = strtolower((string) ($imageInfo['mime'] ?? ''));

    // MIME türü izin listemizde yoksa reddet (örn. image/svg+xml
    // içinde JavaScript barındırabildiği için listede yoktur).
    if (!array_key_exists($mime, ALLOWED_IMAGE_TYPES)) {
        throw new RuntimeException('Yalnızca JPG, PNG, GIF ve WEBP formatları desteklenir.');
    }

    // Uzantıyı KULLANICININ dosya adından değil, kendi listemizden alıyoruz.
    $extension = ALLOWED_IMAGE_TYPES[$mime];

    // upload/ klasörü yoksa oluştur.
    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true) && !is_dir(UPLOAD_DIR)) {
        throw new RuntimeException('Yükleme klasörü oluşturulamadı.');
    }

    /* Rastgele, tahmin edilemez bir dosya adı üret.
     * - Aynı adlı dosyanın üzerine yazılmasını önler
     * - Kullanıcı adı gibi bilgilerin dosya adından sızmasını önler
     * - do/while: (çok düşük ihtimalle) çakışma olursa yeniden dener */
    do {
        $newName = bin2hex(random_bytes(16)) . '.' . $extension;
    } while (file_exists(UPLOAD_DIR . $newName));

    // move_uploaded_file(): Geçici klasörden hedefe taşır.
    // copy() yerine bunu kullanın; ek güvenlik kontrolleri yapar.
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $newName)) {
        throw new RuntimeException('Görsel kaydedilemedi.');
    }

    return $newName;
}

/**
 * upload/ klasöründeki bir dosyayı GÜVENLE siler.
 *
 * PATH TRAVERSAL SALDIRISI NEDİR?
 *   Saldırgan dosya adı olarak "../system/config.php" gönderirse,
 *   kontrolsüz bir unlink() sunucudaki başka dosyaları silebilir.
 *
 * basename() bu riski ortadan kaldırır: yoldaki tüm klasör
 * bilgisini atar, sadece son dosya adını bırakır.
 *   "../../system/config.php"  →  "config.php"
 */
function delete_upload(?string $filename): void
{
    $filename = basename(trim((string) $filename));

    // Boş veya klasör işaretiyse hiçbir şey yapma.
    if ($filename === '' || $filename === '.' || $filename === '..') {
        return;
    }

    $path = UPLOAD_DIR . $filename;

    // is_file(): Var mı ve gerçekten dosya mı? (klasör olmasın)
    if (is_file($path)) {
        @unlink($path);
    }
}

/**
 * Bir kaydın görsel dosya adını veritabanından okur.
 * Kayıt silinmeden önce diskteki dosyayı da temizleyebilmek için gerekir.
 */
function get_user_image(PDO $db, int $id): string
{
    $stmt = $db->prepare('SELECT image FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);

    // fetchColumn(): Tek sütunluk sonucu doğrudan değer olarak verir.
    $image = $stmt->fetchColumn();

    return is_string($image) ? $image : '';
}


/* =====================================================================
 *  BÖLÜM 5 – VERİ ERİŞİMİ
 * ================================================================== */

/**
 * Tek bir kaydı getirir. Bulunamazsa null döner.
 *
 * SELECT * yerine sütunları tek tek yazmak iyi bir alışkanlıktır:
 * ileride tabloya "sifre" gibi bir sütun eklenirse yanlışlıkla
 * dışarı sızmaz.
 *
 * @return array<string,mixed>|null
 */
function find_user(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT id, name, surname, image, tarih FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);

    $row = $stmt->fetch();

    // fetch() kayıt yoksa false döner; biz null'a çeviriyoruz.
    return $row ?: null;
}

/**
 * Kayıt sayısını döndürür.
 *
 * $search boşsa  → toplam kayıt sayısı    (DataTables: recordsTotal)
 * $search doluysa → filtrelenmiş sayı     (DataTables: recordsFiltered)
 *
 * SELECT COUNT(*) kullanıyoruz; tüm satırları çekip saymak
 * (fetchAll + count) büyük tablolarda belleği tüketir.
 */
function count_users(PDO $db, string $search = ''): int
{
    if ($search === '') {
        // Parametre yoksa prepare'a gerek yok, query() yeterli ve hızlı.
        return (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    /* NOT: PDO::ATTR_EMULATE_PREPARES kapalıyken aynı yer tutucu
     * iki kez kullanılamaz; bu yüzden :search_name ve :search_surname
     * diye iki ayrı isim verdik. */
    $stmt = $db->prepare(
        'SELECT COUNT(*) FROM users WHERE name LIKE :search_name OR surname LIKE :search_surname'
    );

    $pattern = '%' . escape_like($search) . '%';
    $stmt->execute([
        ':search_name'    => $pattern,
        ':search_surname' => $pattern,
    ]);

    return (int) $stmt->fetchColumn();
}

/**
 * LIKE kalıbındaki joker karakterleri etkisizleştirir.
 *
 * SQL'de LIKE için:
 *   %  → "sıfır veya daha fazla karakter"
 *   _  → "tam olarak bir karakter"
 *
 * Kullanıcı arama kutusuna "%" yazarsa TÜM kayıtlar dönerdi.
 * Bu fonksiyon bu karakterleri düz metin haline getirir.
 * (Prepared statement SQL Injection'ı engeller ama joker
 *  karakterlerin anlamını değiştirmez; bu ayrı bir konudur.)
 */
function escape_like(string $value): string
{
    return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
}

/**
 * Veritabanı tarihini (2025-01-06 19:34:27) okunabilir hale getirir
 * (06.01.2025 19:34).
 *
 * DateTimeImmutable, DateTime'a göre daha güvenlidir: üzerinde
 * işlem yapınca orijinal nesneyi değiştirmez, yenisini döndürür.
 */
function format_date(?string $value): string
{
    if (empty($value)) {
        return '-';
    }

    try {
        return (new DateTimeImmutable($value))->format('d.m.Y H:i');
    } catch (Exception $e) {
        // Tarih bozuksa uygulamayı çökertmek yerine ham değeri göster.
        return (string) $value;
    }
}
