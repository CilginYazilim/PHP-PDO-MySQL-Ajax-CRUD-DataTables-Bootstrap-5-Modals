<?php
/**
 * =====================================================================
 *  ANA SAYFA (Sunum Katmanı)
 *  cilginyazilim.com – PHP PDO MySQL Ajax CRUD
 * ---------------------------------------------------------------------
 *  Bu dosya SADECE arayüzü çizer ve JavaScript ile AJAX isteklerini
 *  yönetir. Veritabanı işlemleri burada YAPILMAZ; hepsi
 *  "system/ajax.php" dosyasındadır. Bu ayrım (sunum / iş mantığı)
 *  projeyi büyütürken kodun karışmasını önler.
 *
 *  SAYFANIN AKIŞI:
 *    1. config.php  → oturumu başlatır, veritabanına bağlanır
 *    2. function.php→ yardımcı fonksiyonları yükler
 *    3. csrf_token()→ forma gömülecek güvenlik anahtarını üretir
 *    4. HTML çizilir
 *    5. JavaScript, system/ajax.php ile konuşarak veriyi doldurur
 * =====================================================================
 */

// strict_types=1 : PHP'nin tip dönüşümlerini sıkılaştırır.
// Örn. int bekleyen bir fonksiyona "5" (string) gönderirseniz hata verir.
// Sessizce yanlış çalışan kod yerine, hatayı hemen görmenizi sağlar.
declare(strict_types=1);

// __DIR__ : Bu dosyanın bulunduğu klasörün TAM yolu.
// Göreli yol ("system/config.php") yerine bunu kullanmak, dosyanın
// nereden çağrıldığından bağımsız olarak her zaman doğru çalışır.
require __DIR__ . '/system/config.php';
require __DIR__ . '/system/function.php';

// CSRF anahtarı: Sahte istekleri engellemek için kullanılır.
// Oturuma bir kez yazılır, hem <meta> etiketine hem forma gömülür.
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Çılgın Yazılım - cilginyazilim.com">
    <meta name="description" content="PHP PDO, MySQL, AJAX, DataTables ve Bootstrap 5 modal yapısıyla geliştirilmiş güvenli CRUD örnek uygulaması.">

    <!--
        CSRF anahtarını meta etiketine koyuyoruz.
        JavaScript bunu okuyup her AJAX isteğine ekleyecek.
        Böylece formda olmayan istekler (silme, detay) de korunur.
    -->
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">

    <title>PHP PDO MySQL Ajax CRUD | Çılgın Yazılım</title>

    <!-- Tarayıcı sekmesinde görünen marka ikonu -->
    <link rel="icon" type="image/png" href="assets/images/logo.png">

    <!--
        CSS YÜKLEME SIRASI ÖNEMLİDİR:
        1) bootstrap      → temel çatı
        2) dataTables     → tablo eklentisi stilleri
        3) cilginyazilim  → MARKA TASARIM KALIBI (Bootstrap'i ezer)
        4) style          → sadece bu sayfaya özel küçük eklemeler
        Sonra yüklenen dosya, öncekini geçersiz kılabilir.
    -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/cilginyazilim.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<!-- "cy-app" sınıfı, marka tasarım kalıbını sayfaya uygular. -->
<body class="cy-app">

    <!-- Sayfanın en üstündeki ince marka şeridi -->
    <div class="cy-topbar"></div>

    <div class="container py-4 py-lg-5">

        <!-- ============================================================
             ANA KART: Başlık + Tablo
             ============================================================ -->
        <div class="cy-card">

            <!-- ---------- Kart Başlığı (marka gradyanlı) ---------- -->
            <div class="cy-card__header">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                    <!-- Sol taraf: logo + başlık -->
                    <a class="cy-brand" href="https://cilginyazilim.com" target="_blank" rel="noopener">
                        <span class="cy-brand__mark">
                            <img src="assets/images/logo.png" alt="Çılgın Yazılım logosu">
                        </span>
                        <div>
                            <h1 class="cy-brand__title">PHP PDO MySQL Ajax CRUD</h1>
                            <p class="cy-brand__subtitle">
                                DataTables &middot; Bootstrap 5 &middot; Modal &middot; cilginyazilim.com
                            </p>
                        </div>
                    </a>

                    <!-- Sağ taraf: kayıt sayacı + GitHub + yeni kayıt butonu -->
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="cy-badge cy-badge--glass">
                            Toplam <strong id="total_records">0</strong> kayıt
                        </span>

                        <!--
                            Proje açık kaynaktır. Herkes katkı sağlayabilsin diye
                            depo adresini arayüze de koyduk.
                        -->
                        <a class="btn cy-btn cy-btn--glass"
                           href="https://github.com/CilginYazilim/PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals"
                           target="_blank" rel="noopener" title="Projeyi GitHub'da aç">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" style="vertical-align:-2px">
                                <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8Z"/>
                            </svg>
                            GitHub
                        </a>

                        <button type="button" id="add_button" class="btn cy-btn cy-btn--onbrand">
                            <span aria-hidden="true">＋</span> Yeni Kayıt
                        </button>
                    </div>
                </div>
            </div>

            <!-- ---------- Kart Gövdesi: Tablo ---------- -->
            <div class="cy-card__body">
                <div class="table-responsive">
                    <!--
                        DataTables sadece <thead> ister; <tbody> içeriğini
                        kendisi AJAX ile doldurur. Buradaki sütun SAYISI,
                        ajax.php'den dönen dizi uzunluğuyla AYNI olmalıdır,
                        aksi halde DataTables hata verir.
                    -->
                    <table id="user_data" class="table cy-table w-100">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Foto</th>
                                <th scope="col">Ad</th>
                                <th scope="col">Soyad</th>
                                <th scope="col">Kayıt Tarihi</th>
                                <th scope="col" class="text-center">İşlemler</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <!-- ---------- Kart Alt Bilgisi ---------- -->
            <div class="cy-card__footer d-flex flex-wrap justify-content-between gap-2">
                <span>Sunucu taraflı (server-side) DataTables &middot; CSRF korumalı AJAX</span>
                <span>PHP <?= e(PHP_VERSION) ?></span>
            </div>
        </div>

        <div class="cy-footer-note mt-4">
            <p class="mb-1">
                Bu açık kaynak örnek, <a href="https://cilginyazilim.com" target="_blank" rel="noopener">cilginyazilim.com</a>
                tarafından geliştirilmiştir. MIT lisanslıdır; dilediğiniz gibi indirip kullanabilirsiniz.
            </p>
            <p class="mb-0">
                Katkı sağlamak ister misiniz? Depoyu çatallayın (fork) ve pull request gönderin:
                <a href="https://github.com/CilginYazilim/PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals"
                   target="_blank" rel="noopener">github.com/CilginYazilim</a>
            </p>
        </div>
    </div>


    <!-- ================================================================
         MODAL 1 – EKLEME / DÜZENLEME FORMU
         ----------------------------------------------------------------
         Tek bir modal hem ekleme hem düzenleme için kullanılır.
         Hangi işlemin yapılacağını gizli "action" alanı belirler:
             action=add   → yeni kayıt
             action=edit  → mevcut kaydı güncelle (user_id ile)
         ================================================================ -->
    <div class="modal fade cy-modal" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <!--
                enctype="multipart/form-data" : Dosya yüklemek için ZORUNLU.
                novalidate : Tarayıcının kendi uyarı balonlarını kapatır,
                             biz kendi hata mesajlarımızı gösteriyoruz.
            -->
            <form method="post" id="user_form" enctype="multipart/form-data" novalidate>
                <div class="modal-content">

                    <div class="modal-header">
                        <h2 class="modal-title h6 mb-0" id="userModalLabel">Yeni Kayıt Ekle</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Sunucudan gelen genel hata mesajı buraya yazılır -->
                        <div class="alert alert-danger d-none" id="form_alert" role="alert"></div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Ad <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control"
                                   placeholder="Örn: Evren" maxlength="150" autocomplete="given-name">
                            <!--
                                Bootstrap kuralı: .invalid-feedback yalnızca
                                kardeş elemanda .is-invalid varsa görünür.
                                data-error-for özniteliğiyle JS hangi alanın
                                hatası olduğunu bulur.
                            -->
                            <div class="invalid-feedback" data-error-for="name"></div>
                        </div>

                        <div class="mb-3">
                            <label for="surname" class="form-label">Soyad <span class="text-danger">*</span></label>
                            <input type="text" name="surname" id="surname" class="form-control"
                                   placeholder="Örn: ÇILGIN" maxlength="150" autocomplete="family-name">
                            <div class="invalid-feedback" data-error-for="surname"></div>
                        </div>

                        <div class="mb-2">
                            <label for="image_user" class="form-label">Profil Görseli</label>
                            <div class="upload-box">
                                <!--
                                    accept="..." sadece dosya seçme penceresini
                                    filtreler; GÜVENLİK SAĞLAMAZ.
                                    Asıl doğrulama sunucuda yapılır.
                                -->
                                <input type="file" name="image_user" id="image_user" class="form-control"
                                       accept="image/jpeg,image/png,image/gif,image/webp">
                                <div class="form-text mt-2">
                                    JPG, PNG, GIF veya WEBP &middot; en fazla 2 MB &middot; boş bırakılabilir.
                                </div>
                            </div>
                            <div class="invalid-feedback d-block" data-error-for="image_user"></div>
                        </div>

                        <!-- Seçilen görselin önizlemesi (JS ile doldurulur) -->
                        <div id="image_preview_wrapper" class="d-none mt-3 text-center">
                            <span class="form-label d-block mb-1 cy-muted small">Önizleme</span>
                            <img src="" alt="Seçilen görselin önizlemesi" id="image_preview">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary cy-btn" data-bs-dismiss="modal">
                            İptal
                        </button>
                        <button type="submit" id="submit_button" class="btn cy-btn cy-btn--primary">
                            <!-- Kaydederken dönen yükleniyor animasyonu -->
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="submit_spinner"
                                  role="status" aria-hidden="true"></span>
                            <span id="submit_label">Kaydet</span>
                        </button>
                    </div>

                    <!--
                        GİZLİ ALANLAR
                        action    : add mi edit mi olduğunu sunucuya bildirir
                        user_id   : düzenlemede hangi kaydın güncelleneceği
                        csrf_token: güvenlik anahtarı
                    -->
                    <input type="hidden" name="action"     id="form_action" value="add">
                    <input type="hidden" name="user_id"    id="user_id"     value="">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                </div>
            </form>
        </div>
    </div>


    <!-- ================================================================
         MODAL 2 – KAYIT DETAYI (salt okunur)
         ----------------------------------------------------------------
         Tablodaki "göz" butonuna basılınca açılır. Verileri
         action=fetch isteğiyle sunucudan alır.
         ================================================================ -->
    <div class="modal fade cy-modal" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h2 class="modal-title h6 mb-0" id="detailModalLabel">Kayıt Detayı</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>

                <div class="modal-body">
                    <div class="text-center mb-4">
                        <!-- Görsel varsa <img>, yoksa baş harf rozeti gösterilir -->
                        <img src="" alt="" id="detail_image" class="d-none">
                        <span id="detail_initial" class="cy-avatar cy-avatar--initial cy-avatar--lg d-none"></span>
                        <h3 class="h5 mt-3 mb-0" id="detail_fullname"></h3>
                    </div>

                    <!-- <dl> = tanım listesi: etiket/değer çiftleri için doğru HTML -->
                    <dl class="cy-detail">
                        <dt>Kayıt No</dt>
                        <dd id="detail_id"></dd>

                        <dt>Ad</dt>
                        <dd id="detail_name"></dd>

                        <dt>Soyad</dt>
                        <dd id="detail_surname"></dd>

                        <dt>Görsel</dt>
                        <dd id="detail_file"></dd>

                        <dt>Kayıt Tarihi</dt>
                        <dd id="detail_date"></dd>
                    </dl>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary cy-btn" data-bs-dismiss="modal">Kapat</button>
                    <!-- Detaydan doğrudan düzenlemeye geçiş -->
                    <button type="button" class="btn cy-btn cy-btn--primary" id="detail_edit_button">
                        Bu Kaydı Düzenle
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- ================================================================
         MODAL 3 – SİLME ONAYI
         ----------------------------------------------------------------
         Tarayıcının confirm() kutusu yerine kendi modalımız.
         Hem daha güzel görünür hem de tasarımla uyumludur.
         ================================================================ -->
    <div class="modal fade cy-modal" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">

                <div class="modal-header">
                    <h2 class="modal-title h6 mb-0" id="deleteModalLabel">Kaydı Sil</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>

                <div class="modal-body text-center">
                    <div class="delete-icon" aria-hidden="true">!</div>
                    <p class="mb-0">
                        <strong id="delete_label"></strong> kaydı ve varsa görseli
                        <u>kalıcı olarak</u> silinecek.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary cy-btn btn-sm" data-bs-dismiss="modal">
                        Vazgeç
                    </button>
                    <button type="button" class="btn btn-danger cy-btn btn-sm" id="confirm_delete">
                        Evet, Sil
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Toast (bildirim) balonlarının ekleneceği kapsayıcı -->
    <div class="toast-container cy-toast-container position-fixed top-0 end-0 p-3" id="toast_container"></div>


    <!--
        JAVASCRIPT YÜKLEME SIRASI ÇOK ÖNEMLİDİR:
        1) jQuery            → DataTables ve kodumuz buna bağımlı
        2) bootstrap.bundle  → Modal, Toast (Popper dahil)
        3) jquery.dataTables → Tablo motoru
        4) dataTables.bootstrap5 → Tabloyu Bootstrap görünümüne çevirir
        Sıra bozulursa "$ is not defined" gibi hatalar alırsınız.
    -->
    <script src="assets/js/jquery-3.7.0.js"></script>
    <script src="assets/js/bootstrap.bundle.js"></script>
    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap5.min.js"></script>

    <script>
    /* =================================================================
     *  UYGULAMA JAVASCRIPT'İ
     * -----------------------------------------------------------------
     *  $(function () { ... })  =  "Sayfa hazır olunca çalıştır".
     *  Bu sarmalayıcı olmadan, HTML henüz yüklenmemişken elemanları
     *  bulmaya çalışır ve kod sessizce çalışmaz.
     * ================================================================= */
    $(function () {
        'use strict'; // Tanımsız değişken kullanımı gibi hataları yakalar.

        /* -------------------------------------------------------------
         *  SABİTLER
         * ----------------------------------------------------------- */
        var ENDPOINT   = 'system/ajax.php';                              // Tüm istekler buraya
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');   // Güvenlik anahtarı
        var MAX_BYTES  = 2 * 1024 * 1024;                                // 2 MB (sunucuyla aynı olmalı)

        // Bootstrap modal nesneleri. Bir kez oluşturup tekrar tekrar
        // .show() / .hide() ile kullanırız.
        var userModal   = new bootstrap.Modal(document.getElementById('userModal'));
        var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

        // Silme onayı beklerken hangi kaydın silineceğini burada tutarız.
        var pendingDeleteId = null;
        // Detay modalında görüntülenen kaydın ID'si (düzenlemeye geçmek için).
        var currentDetailId = null;


        /* =============================================================
         *  YARDIMCI FONKSİYONLAR
         * ============================================================= */

        /**
         * Sağ üstte geçici bildirim (toast) gösterir.
         * @param {string} message Gösterilecek metin
         * @param {string} type    'success' | 'danger' | 'info'
         */
        function notify(message, type) {
            type = type || 'success';

            var $toast = $(
                '<div class="toast cy-toast cy-toast--' + type + '" role="alert" aria-live="assertive" aria-atomic="true">' +
                    '<div class="d-flex">' +
                        '<div class="toast-body"></div>' +
                        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Kapat"></button>' +
                    '</div>' +
                '</div>'
            );

            // ÖNEMLİ: .html() değil .text() kullanıyoruz.
            // .html() kullanılsaydı mesaj içindeki HTML çalışır ve
            // XSS açığı oluşurdu.
            $toast.find('.toast-body').text(message);
            $('#toast_container').append($toast);

            var toast = new bootstrap.Toast($toast[0], { delay: 4000 });
            // Kapanınca DOM'dan tamamen kaldır (bellek sızıntısını önler).
            $toast.on('hidden.bs.toast', function () { $toast.remove(); });
            toast.show();
        }

        /** Formdaki tüm hata işaretlerini ve mesajlarını temizler. */
        function clearErrors() {
            var $form = $('#user_form');
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('[data-error-for]').text('');
            $('#form_alert').addClass('d-none').text('');
        }

        /**
         * Sunucudan gelen hataları ilgili alanların altına yazar.
         * @param {Object} errors  { alanAdi: "hata mesajı", ... }
         * @param {string} general Formun üstünde gösterilecek genel mesaj
         */
        function showErrors(errors, general) {
            clearErrors();

            if (general) {
                $('#form_alert').removeClass('d-none').text(general);
            }

            $.each(errors || {}, function (field, message) {
                $('#' + field).addClass('is-invalid');
                $('[data-error-for="' + field + '"]').text(message);
            });
        }

        /** Kaydet butonunu yükleniyor durumuna alır / geri döndürür. */
        function setLoading(isLoading) {
            $('#submit_button').prop('disabled', isLoading);
            $('#submit_spinner').toggleClass('d-none', !isLoading);
            $('#submit_label').text(isLoading ? 'Kaydediliyor…' : 'Kaydet');
        }

        /** Formu ilk haline döndürür (modal her kapandığında çağrılır). */
        function resetForm() {
            $('#user_form')[0].reset();     // Görünen alanları temizler
            $('#user_id').val('');          // Gizli alanları reset() temizlemez
            $('#image_preview_wrapper').addClass('d-none');
            $('#image_preview').attr('src', '');
            clearErrors();
        }

        /**
         * Sunucudan tek bir kaydı getirir.
         * @param {number}   id      Kayıt numarası
         * @param {Function} onDone  Başarılı olursa çalışacak fonksiyon
         */
        function fetchUser(id, onDone) {
            $.ajax({
                url: ENDPOINT,
                method: 'POST',
                dataType: 'json',
                data: { action: 'fetch', id: id, csrf_token: CSRF_TOKEN }
            }).done(onDone).fail(function (xhr) {
                var res = xhr.responseJSON || {};
                notify(res.description || 'Kayıt getirilemedi.', 'danger');
            });
        }

        /** Düzenleme modalını verilen kayıt verisiyle açar. */
        function openEditModal(data) {
            resetForm();
            $('#form_action').val('edit');       // Sunucuya "güncelle" de
            $('#user_id').val(data.id);
            $('#name').val(data.name);
            $('#surname').val(data.surname);
            $('#userModalLabel').text('Kaydı Düzenle');

            // Mevcut görseli önizleme olarak göster
            if (data.image_url) {
                $('#image_preview').attr('src', data.image_url);
                $('#image_preview_wrapper').removeClass('d-none');
            }
            userModal.show();
        }


        /* =============================================================
         *  DATATABLES KURULUMU
         * -------------------------------------------------------------
         *  serverSide: true → Sayfalama, arama ve sıralama SUNUCUDA
         *  yapılır. 50 kayıt için şart değildir; ancak 100.000 kayıtta
         *  tarayıcıya tüm veriyi göndermemek için hayati önemdedir.
         *  Bu modda DataTables her etkileşimde ajax.php'ye POST atar.
         * ============================================================= */
        var dataTable = $('#user_data').DataTable({

            processing: true,   // "İşleniyor…" göstergesi
            serverSide: true,   // Sunucu taraflı mod
            order: [[0, 'desc']], // Varsayılan sıralama: ID'ye göre azalan
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],

            ajax: {
                url: ENDPOINT,
                type: 'POST',
                // Her istekte otomatik eklenecek ek parametreler
                data: function (d) {
                    d.action     = 'list';
                    d.csrf_token = CSRF_TOKEN;
                },
                error: function () {
                    notify('Kayıtlar yüklenirken bir hata oluştu.', 'danger');
                }
            },

            // Sütun davranışları (targets = sütun sırası, 0'dan başlar)
            columnDefs: [
                { targets: 0, className: 'cy-id' },
                // Foto ve İşlemler sütunları sıralanamaz/aranamaz:
                // içlerinde HTML var, veritabanı sütunu değiller.
                { targets: 1, orderable: false, searchable: false, className: 'text-center' },
                { targets: 2, className: 'cy-name' },
                { targets: 5, orderable: false, searchable: false, className: 'text-center' }
            ],

            // Tablo her çizildiğinde çalışır → sayacı günceller
            drawCallback: function (settings) {
                $('#total_records').text(settings.json ? settings.json.recordsTotal : 0);
            },

            // Türkçe arayüz metinleri.
            // CDN'den çekmek yerine buraya gömdük; internet olmadan da çalışır.
            language: {
                emptyTable:     'Henüz kayıt bulunmuyor.',
                info:           '_TOTAL_ kayıttan _START_ – _END_ arası gösteriliyor',
                infoEmpty:      'Gösterilecek kayıt yok',
                infoFiltered:   '(toplam _MAX_ kayıt içinden filtrelendi)',
                lengthMenu:     'Sayfada _MENU_ kayıt göster',
                loadingRecords: 'Yükleniyor…',
                processing:     'İşleniyor…',
                search:         'Ara:',
                searchPlaceholder: 'Ad veya soyad',
                zeroRecords:    'Aramanızla eşleşen kayıt bulunamadı.',
                paginate: {
                    first: 'İlk', last: 'Son', next: 'Sonraki', previous: 'Önceki'
                },
                aria: {
                    sortAscending:  ': artan sırada sıralamak için etkinleştir',
                    sortDescending: ': azalan sırada sıralamak için etkinleştir'
                }
            }
        });


        /* =============================================================
         *  1) YENİ KAYIT BUTONU
         * ============================================================= */
        $('#add_button').on('click', function () {
            resetForm();
            $('#form_action').val('add');
            $('#userModalLabel').text('Yeni Kayıt Ekle');
            userModal.show();
        });


        /* =============================================================
         *  2) GÖRSEL SEÇİLDİĞİNDE: ÖNİZLEME + ÖN KONTROL
         * -------------------------------------------------------------
         *  Buradaki kontroller sadece KULLANICI DENEYİMİ içindir.
         *  Kötü niyetli biri JavaScript'i devre dışı bırakabilir,
         *  bu yüzden aynı kontroller sunucuda TEKRAR yapılır.
         * ============================================================= */
        $('#image_user').on('change', function () {
            var file = this.files && this.files[0];

            $(this).removeClass('is-invalid');
            $('[data-error-for="image_user"]').text('');

            // Kullanıcı seçimi iptal ettiyse önizlemeyi gizle
            if (!file) {
                $('#image_preview_wrapper').addClass('d-none');
                return;
            }

            // Dosya türü kontrolü
            if (!/^image\/(jpeg|png|gif|webp)$/.test(file.type)) {
                $(this).val('').addClass('is-invalid');
                $('[data-error-for="image_user"]').text('Yalnızca JPG, PNG, GIF ve WEBP dosyaları yükleyebilirsiniz.');
                $('#image_preview_wrapper').addClass('d-none');
                return;
            }

            // Dosya boyutu kontrolü
            if (file.size > MAX_BYTES) {
                $(this).val('').addClass('is-invalid');
                $('[data-error-for="image_user"]').text('Görsel boyutu en fazla 2 MB olabilir.');
                $('#image_preview_wrapper').addClass('d-none');
                return;
            }

            // FileReader: Dosyayı sunucuya YÜKLEMEDEN tarayıcıda okur.
            // Sonuç "data:image/png;base64,..." biçiminde bir metindir.
            var reader = new FileReader();
            reader.onload = function (event) {
                $('#image_preview').attr('src', event.target.result);
                $('#image_preview_wrapper').removeClass('d-none');
            };
            reader.readAsDataURL(file);
        });


        /* =============================================================
         *  3) FORM GÖNDERİMİ (Ekleme ve Düzenleme ortak)
         * ============================================================= */
        $('#user_form').on('submit', function (event) {
            // Sayfanın yenilenmesini engelle; işi AJAX yapacak.
            event.preventDefault();

            clearErrors();
            setLoading(true);

            $.ajax({
                url: ENDPOINT,
                method: 'POST',
                // FormData: Dosyayı da içerecek şekilde formu paketler.
                data: new FormData(this),
                // Bu iki ayar dosya yüklemede ZORUNLUDUR:
                contentType: false, // jQuery kendi Content-Type'ını koymasın
                processData: false, // Veriyi metne çevirmeye çalışmasın
                dataType: 'json'
            })
            .done(function (response) {
                // Sunucu 200 döndürdü → işlem başarılı
                userModal.hide();
                resetForm();
                notify(response.description, 'success');

                // Tabloyu tazele. İkinci parametre false:
                // "kullanıcıyı 1. sayfaya atma, bulunduğu sayfada kal".
                dataTable.ajax.reload(null, false);
            })
            .fail(function (xhr) {
                // Sunucu 4xx/5xx döndürdü → hataları göster
                var res = xhr.responseJSON || {};
                showErrors(res.errors, res.description || 'İşlem tamamlanamadı.');
            })
            .always(function () {
                // Başarılı da olsa hatalı da olsa butonu geri aç
                setLoading(false);
            });
        });


        /* =============================================================
         *  4) DETAY BUTONU
         * -------------------------------------------------------------
         *  DİKKAT: Butonlar AJAX ile SONRADAN oluşturulduğu için
         *  doğrudan $('.js-view').click(...) ÇALIŞMAZ.
         *  Bunun yerine olayı sabit bir üst elemana ($('#user_data'))
         *  bağlayıp filtreliyoruz. Buna "event delegation" denir.
         * ============================================================= */
        $('#user_data').on('click', '.js-view', function () {
            var id = $(this).data('id');

            fetchUser(id, function (data) {
                currentDetailId = data.id;

                $('#detail_id').text('#' + data.id);
                $('#detail_name').text(data.name);
                $('#detail_surname').text(data.surname);
                $('#detail_fullname').text(data.name + ' ' + data.surname);
                $('#detail_date').text(data.tarih);
                $('#detail_file').text(data.image || 'Görsel yüklenmemiş');

                if (data.image_url) {
                    // Görsel varsa fotoğrafı göster
                    $('#detail_image').attr('src', data.image_url)
                                      .attr('alt', data.name + ' ' + data.surname)
                                      .removeClass('d-none');
                    $('#detail_initial').addClass('d-none');
                } else {
                    // Görsel yoksa adın baş harfinden rozet üret
                    $('#detail_image').addClass('d-none').attr('src', '');
                    $('#detail_initial').text(data.name.charAt(0).toLocaleUpperCase('tr-TR'))
                                        .removeClass('d-none');
                }

                detailModal.show();
            });
        });

        // Detay modalındaki "Bu Kaydı Düzenle" butonu
        $('#detail_edit_button').on('click', function () {
            if (currentDetailId === null) { return; }

            var id = currentDetailId;
            detailModal.hide();

            // Modal kapanma animasyonu bitmeden yenisini açarsak
            // arka plan karartması üst üste biner. Bu yüzden
            // "kapandı" olayını bekleyip sonra açıyoruz.
            $('#detailModal').one('hidden.bs.modal', function () {
                fetchUser(id, openEditModal);
            });
        });


        /* =============================================================
         *  5) DÜZENLE BUTONU
         * ============================================================= */
        $('#user_data').on('click', '.js-edit', function () {
            fetchUser($(this).data('id'), openEditModal);
        });


        /* =============================================================
         *  6) SİL BUTONU + ONAY
         * ============================================================= */
        $('#user_data').on('click', '.js-delete', function () {
            pendingDeleteId = $(this).data('id');
            $('#delete_label').text($(this).data('label') || '#' + pendingDeleteId);
            deleteModal.show();
        });

        $('#confirm_delete').on('click', function () {
            if (pendingDeleteId === null) { return; }

            // Çift tıklamayı engellemek için butonu kilitle
            var $button = $(this).prop('disabled', true);

            $.ajax({
                url: ENDPOINT,
                method: 'POST',
                dataType: 'json',
                data: { action: 'delete', id: pendingDeleteId, csrf_token: CSRF_TOKEN }
            })
            .done(function (response) {
                notify(response.description, 'success');
                dataTable.ajax.reload(null, false);
            })
            .fail(function (xhr) {
                var res = xhr.responseJSON || {};
                notify(res.description || 'Silme işlemi başarısız oldu.', 'danger');
            })
            .always(function () {
                $button.prop('disabled', false);
                deleteModal.hide();
                pendingDeleteId = null;
            });
        });


        /* =============================================================
         *  7) MODAL KAPANINCA TEMİZLİK
         * -------------------------------------------------------------
         *  Aksi halde bir sonraki açılışta eski veriler ekranda kalır.
         * ============================================================= */
        $('#userModal').on('hidden.bs.modal', resetForm);
    });
    </script>
</body>
</html>
