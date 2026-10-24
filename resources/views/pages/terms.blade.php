<x-layouts.public>
    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">
        <x-breadcrumbs :items="[['Syarat & Ketentuan', route('pages.terms')]]" />
        <h1 class="mt-4 font-display text-4xl font-semibold tracking-tight text-navy-900">Syarat &amp; Ketentuan</h1>
        <div class="rich mt-8 space-y-5 text-slate-700">
            <p><strong>1. Layanan.</strong> {{ setting('site_name') }} menyediakan layanan tryout dan latihan soal secara daring untuk persiapan seleksi CPNS. Soal bersifat latihan dan bukan soal resmi instansi pemerintah.</p>
            <p><strong>2. Akun.</strong> Pengguna wajib memberikan data yang benar dan menjaga kerahasiaan kata sandi. Satu akun hanya untuk satu orang; berbagi akun dapat menyebabkan akun dinonaktifkan.</p>
            <p><strong>3. Pembelian &amp; Pembayaran.</strong> Pembayaran dilakukan melalui metode yang tercantum di halaman pembayaran. Akses tryout aktif setelah pembayaran diverifikasi admin (paling lambat {{ setting('verification_sla') }} pada jam operasional). Pesanan yang tidak dibayar hingga batas waktu akan kedaluwarsa otomatis.</p>
            <p><strong>4. Pengembalian Dana.</strong> Produk digital yang sudah diaktifkan tidak dapat dikembalikan, kecuali terjadi kesalahan sistem yang menyebabkan tryout tidak dapat diakses.</p>
            <p><strong>5. Hak Cipta.</strong> Seluruh soal, pembahasan, dan materi dilindungi hak cipta. Dilarang menyalin, menyebarluaskan, atau memperjualbelikan konten tanpa izin tertulis.</p>
            <p><strong>6. Penilaian &amp; Peringkat.</strong> Nilai dihitung otomatis oleh sistem. Peringkat dihitung dari percobaan pertama setiap peserta dan hanya ditampilkan bila jumlah peserta mencukupi.</p>
            <p><strong>7. Perubahan.</strong> Kami dapat memperbarui syarat &amp; ketentuan sewaktu-waktu. Penggunaan layanan setelah perubahan berarti menyetujui ketentuan terbaru.</p>
        </div>
    </section>
</x-layouts.public>
