<?php
/**
 * =====================================================================
 *  YAPILANDIRMA DOSYASI
 *  cilginyazilim.com – PHP PDO MySQL Ajax CRUD
 * ---------------------------------------------------------------------
 *  Bu dosya üç iş yapar:
 *    1. Oturumu (session) başlatır  → CSRF anahtarını saklamak için
 *    2. Ayarları sabit olarak tanımlar
 *    3. Veritabanı bağlantısını ($db) kurar
 *
 *  index.php ve system/ajax.php dosyalarının ikisi de bunu çağırır.
 *
 *  KENDİ SUNUCUNUZA UYARLAMAK İÇİN: Aşağıdaki DB_* satırlarını
 *  düzenleyin ya da sunucunuzda ortam değişkeni tanımlayın.
 * =====================================================================
 */

declare(strict_types=1);

/* ---------------------------------------------------------------------
 *  .env DESTEĞİ
 * ---------------------------------------------------------------------
 *  Veritabanı bilgileri bu dosyanın İÇİNDE durmak zorunda değil.
 *  Depo kökündeki ".env" dosyasına yazarsanız buradaki varsayılanlar
 *  devreye girmez — ve ".env" .gitignore içinde olduğu için parolanız
 *  depoya hiç girmez.
 *
 *  NEDEN AYRI BİR DOSYA?
 *  config.php DEPODA durur ve her dağıtımda depodaki sürümle
 *  DEĞİŞTİRİLİR; içine elle yazdığınız parola bir sonraki deploy'da
 *  silinir. .env ise deploy'un dokunmadığı bir dosyadır: bir kez
 *  oluşturursunuz, kalıcıdır.
 *
 *  DEĞER ARAMA SIRASI
 *      1. config.local.php içinde define() edilmişse o kazanır
 *         (bu dosyada varsa; aşağıdaki "! defined()" kontrolleri)
 *      2. .env dosyası
 *      3. Sunucunun gerçek ortam değişkeni (Apache SetEnv, systemd…)
 *      4. Bu dosyadaki varsayılan
 *
 *  cy_env() bilerek getenv() ile AYNI şeyi döndürür (değer ya da
 *  false). Böylece aşağıdaki satırlar olduğu gibi çalışmaya devam
 *  eder; "?:" ve "!== false" kalıplarının hiçbiri değişmedi.
 * ------------------------------------------------------------------ */
if (! function_exists('cy_env')) {
    /**
     * .env dosyasından (yoksa ortamdan) bir değer okur.
     *
     * @return string|false Değer yoksa false — getenv() ile aynı sözleşme.
     */
    function cy_env(string $key): string|false
    {
        static $env = null;

        if ($env === null) {
            $env  = [];
            $file = dirname(__DIR__) . '/.env';

            if (is_file($file) && is_readable($file)) {
                /* IGNORE_NEW_LINES + SKIP_EMPTY_LINES: satır sonlarını ve
                 * boş satırları baştan eler; ayrıştırma sadeleşir. */
                $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                foreach ($lines as $line) {
                    $line = trim($line);

                    // Yorum satırı ya da "=" içermeyen satır atlanır.
                    if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                        continue;
                    }

                    [$name, $value] = explode('=', $line, 2);

                    $name  = trim($name);
                    $value = trim($value);

                    /* Tırnak içindeki değerlerden tırnakları at:
                     * DB_PASS="a b c" → a b c
                     * Tırnak zorunlu değildir; yalnızca boşluk içeren
                     * parolalar için gerekir. */
                    if (strlen($value) >= 2
                        && ($value[0] === '"' || $value[0] === "'")
                        && $value[strlen($value) - 1] === $value[0]
                    ) {
                        $value = substr($value, 1, -1);
                    }

                    if ($name !== '') {
                        $env[$name] = $value;
                    }
                }
            }
        }

        // .env'de varsa o; yoksa sunucunun gerçek ortam değişkeni.
        return $env[$key] ?? getenv($key);
    }
}

/* ---------------------------------------------------------------------
 *  1) OTURUM
 * ---------------------------------------------------------------------
 *  session_start() birden fazla kez çağrılırsa PHP uyarı verir.
 *  Bu kontrol, dosya iki kez dahil edilse bile hata çıkmasını önler.
 * ------------------------------------------------------------------ */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------------------------------------------------------------------
 *  2) VERİTABANI AYARLARI
 * ---------------------------------------------------------------------
 *  cy_env('DB_HOST') ?: '127.0.0.1'
 *      → Sunucuda DB_HOST ortam değişkeni tanımlıysa onu kullan,
 *        tanımlı değilse (veya boşsa) '127.0.0.1' kullan.
 *
 *  NEDEN ORTAM DEĞİŞKENİ?
 *  Şifreleri koda yazıp GitHub'a yüklemek en sık yapılan güvenlik
 *  hatasıdır. Ortam değişkeni kullanırsanız aynı kod, farklı
 *  sunucularda farklı şifrelerle çalışır ve şifre repoda görünmez.
 *
 *  Aşağıdaki varsayılanlar XAMPP kurulumuna göredir
 *  (kullanıcı: root, şifre: boş).
 * ------------------------------------------------------------------ */
define('DB_HOST', cy_env('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', cy_env('DB_NAME') ?: 'crud');
define('DB_USER', cy_env('DB_USER') ?: 'root');
define('DB_PASS', cy_env('DB_PASS') !== false ? (string) cy_env('DB_PASS') : '');

// utf8mb4: Türkçe karakterler ve emoji dahil tüm Unicode'u destekler.
// Eski "utf8" (utf8mb3) bazı karakterleri saklayamaz, kullanmayın.
define('DB_CHARSET', 'utf8mb4');

/* ---------------------------------------------------------------------
 *  ZAMAN DİLİMİ
 * ---------------------------------------------------------------------
 *  ÖLÇÜLEN SORUN: php.ini'de date.timezone çoğu XAMPP kurulumunda
 *  sunucunun coğrafi diliminden farklıdır. Bu makinede PHP
 *  "Europe/Berlin", MySQL ise sistem dilimi (Europe/Istanbul)
 *  kullanıyordu; aynı anı anlatan iki satır BİR SAAT farklı görünüyordu:
 *
 *      worker günlüğü (PHP date)  : 14:03:17
 *      veritabanı  (MySQL NOW())  : 15:03:17
 *
 *  Bu depodaki zaman ARİTMETİĞİ bilinçli olarak SQL tarafında yapılır
 *  (NOW(), INTERVAL, TIMESTAMPDIFF), bu yüzden hesaplar zaten doğrudur.
 *  Kayan şey, PHP'nin ekrana/günlüğe bastığı saatti — ve demoyu
 *  deneyen biri için bu, "sistem yanlış çalışıyor" gibi görünür.
 *
 *  Çözüm: dilimi ORTAMA bırakmak yerine açıkça sabitliyoruz. Kendi
 *  sunucunuzda farklı bir dilim istiyorsanız APP_TIMEZONE ortam
 *  değişkenini tanımlamanız yeterlidir; kod değiştirmenize gerek yok.
 * ------------------------------------------------------------------ */
define('APP_TIMEZONE', cy_env('APP_TIMEZONE') ?: 'Europe/Istanbul');

// @ kullanmıyoruz: geçersiz bir dilim adı sessizce yutulmamalı.
if (in_array(APP_TIMEZONE, timezone_identifiers_list(), true)) {
    date_default_timezone_set(APP_TIMEZONE);
}

/* ---------------------------------------------------------------------
 *  3) GÖRSEL YÜKLEME AYARLARI
 * ------------------------------------------------------------------ */

// dirname(__DIR__) : Bu dosya "system/" içinde olduğu için bir üst
// klasörü (proje kökünü) verir. Sonuç: .../proje/upload/
define('UPLOAD_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR);

// Tarayıcının göreceği yol (HTML <img src="..."> içinde kullanılır).
define('UPLOAD_URL', 'upload/');

// İzin verilen en büyük dosya boyutu (bayt cinsinden).
// 2 * 1024 * 1024 = 2 MB. Bu değeri artırırsanız php.ini içindeki
// upload_max_filesize ve post_max_size değerlerini de artırın.
define('UPLOAD_MAX_BYTES', 2 * 1024 * 1024);

/**
 * İzin verilen MIME türleri ve karşılık gelen GÜVENLİ uzantılar.
 *
 * ÇOK ÖNEMLİ: Yeni dosyanın uzantısı, kullanıcının gönderdiği dosya
 * adından DEĞİL, bu listeden alınır. Böylece "virus.php.png" gibi
 * çift uzantılı dosyalar sunucuda çalıştırılabilir hale gelemez.
 */
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
]);

/* ---------------------------------------------------------------------
 *  4) UYGULAMA AYARLARI
 * ------------------------------------------------------------------ */

/**
 * APP_DEBUG
 *   true  → Hata detayları ekranda gösterilir (GELİŞTİRME için)
 *   false → Hatalar gizlenir, sadece log'a yazılır (CANLI için)
 *
 * CANLI SUNUCUYA ALIRKEN MUTLAKA false YAPIN.
 * Aksi halde veritabanı tablo/sütun isimleriniz saldırgana görünür.
 */
define('APP_DEBUG', true);

// Ad ve soyad için karakter sınırları (veritabanındaki VARCHAR(150)
// ile uyumlu olmalıdır).
define('NAME_MIN_LENGTH', 2);
define('NAME_MAX_LENGTH', 150);

// Hata gösterimini APP_DEBUG ayarına göre aç/kapat.
error_reporting(APP_DEBUG ? E_ALL : 0);
ini_set('display_errors', APP_DEBUG ? '1' : '0');

/* ---------------------------------------------------------------------
 *  5) VERİTABANI BAĞLANTISI (PDO)
 * ---------------------------------------------------------------------
 *  PDO = PHP Data Objects. Farklı veritabanları için ortak arayüz
 *  sunar ve prepared statement desteğiyle SQL Injection'a karşı
 *  en güvenli yöntemdir.
 *
 *  DSN (Data Source Name): "hangi sürücü, hangi sunucu, hangi veritabanı"
 *  bilgisini taşıyan bağlantı metnidir.
 *  Örn: mysql:host=127.0.0.1;dbname=crud;charset=utf8mb4
 * ------------------------------------------------------------------ */
try {
    $db = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET),
        DB_USER,
        DB_PASS,
        [
            /* ERRMODE_EXCEPTION:
             * Sorgu hata verdiğinde PHP istisna (exception) fırlatır.
             * Varsayılan ayarda hatalar SESSİZCE yutulur ve saatlerce
             * "neden çalışmıyor?" diye ararsınız. Bunu mutlaka açın. */
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            /* FETCH_ASSOC:
             * Sonuçları $row['name'] şeklinde isimle döndürür.
             * Varsayılan ayar hem isimli hem numaralı döndürür ve
             * belleği gereksiz yere iki katına çıkarır. */
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            /* EMULATE_PREPARES = false:
             * PHP'nin sorguyu taklit etmesini kapatır; sorgu gerçekten
             * MySQL tarafından hazırlanır. Bu, SQL Injection'a karşı
             * en güçlü korumadır ve sayıların doğru tipte gitmesini sağlar.
             *
             * DİKKAT: Bu ayar açıkken aynı isimli yer tutucu (:deger)
             * bir sorguda İKİ KEZ kullanılamaz. Farklı isimler verin. */
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Bağlantı kurulamadıysa uygulamanın devam etmesi anlamsızdır.
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');

    echo APP_DEBUG
        ? 'Veritabanı bağlantı hatası: ' . $e->getMessage()
        : 'Veritabanına bağlanılamadı. Lütfen daha sonra tekrar deneyin.';

    exit;
}
