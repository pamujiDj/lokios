package id.sch.smpn45sby.ilok;

/** Semua pengaturan penting ada di sini. Ubah sesuai kebutuhan sekolah. */
final class Config {
    private Config() {}

    static final String APP_VERSION = "1.1";

    /** Alamat website ujian. */
    static final String EXAM_URL = "https://sas.smpnegeri45sby.sch.id";

    /**
     * Domain yang boleh dibuka. Link ke domain lain diblokir.
     * Tambahkan bila perlu, mis. "docs.google.com" untuk Google Form.
     */
    static final String[] ALLOWED_HOSTS = {
            "sas.smpnegeri45sby.sch.id",
    };

    /** PIN pengawas untuk membuka kunci / keluar dari ujian. GANTI SEBELUM DIPAKAI! */
    static final String PROCTOR_PIN = "450945";

    /**
     * KUNCI RAHASIA untuk tanda pengenal aplikasi (dikirim ke server lewat User-Agent).
     * HARUS SAMA dengan ILOK_SECRET di server/ilok_guard.php. GANTI dengan teks acak panjang!
     */
    static final String SECRET = "GANTI_DENGAN_KUNCI_RAHASIA_ANDA_123";

    /** Lama berlaku satu token (detik). Harus sama dengan ILOK_WINDOW di server. */
    static final int TOKEN_WINDOW_SEC = 600;

    /** Jumlah pelanggaran sebelum ujian dikunci. */
    static final int MAX_VIOLATIONS = 3;

    /** Izinkan tombol Back untuk mundur halaman di dalam website ujian. */
    static final boolean ALLOW_BACK = false;

    /** Tolak perangkat root dan emulator. */
    static final boolean BLOCK_ROOT_EMULATOR = true;

    /** Tolak ujian bila "Debugging USB" aktif (mencegah mirroring layar ke PC). */
    static final boolean BLOCK_USB_DEBUGGING = true;

    /**
     * Wajibkan layar tersemat (screen pinning). Jika siswa menolak, ujian dibatalkan.
     * Biarkan false bila ada HP lama yang tidak mendukung.
     */
    static final boolean REQUIRE_PINNING = false;
}
