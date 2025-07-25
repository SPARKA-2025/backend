<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\Fakultas;

class FakultasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            ['nama' => 'Fakultas Matematika & Ilmu Pengetahuan Alam', 'deskripsi' => 'Fakultas Matematika dan Ilmu Pengetahuan Alam (FMIPA) merupakan fakultas yang menawarkan program studi di bidang ilmu-ilmu dasar seperti Matematika, Fisika, Kimia, Biologi, serta Pendidikan di masing-masing bidang. FMIPA berfokus pada pengembangan riset ilmiah dan pendidikan yang berkualitas, dengan tujuan menghasilkan lulusan yang unggul di bidang akademik maupun praktis.'],
            ['nama' => 'Fakultas Bahasa dan Seni', 'deskripsi' => 'Fakultas Bahasa dan Seni (FBS) adalah fakultas yang menawarkan program studi di bidang bahasa, sastra, dan seni. FBS UNNES memiliki berbagai jurusan seperti Bahasa Indonesia, Bahasa Inggris, Bahasa Asing, Seni Rupa, dan Seni Musik. Fakultas ini berfokus pada pengembangan kreativitas, apresiasi budaya, serta keterampilan berkomunikasi dan berkarya.'],
            ['nama' => 'Fakultas Ilmu Sosial dan Ilmu Politik', 'deskripsi' => 'Fakultas Ilmu Sosial dan Ilmu Politik (FISIP) adalah fakultas yang menawarkan pendidikan di bidang ilmu sosial, termasuk program studi seperti Sosiologi, Antropologi, Ilmu Politik, dan Pendidikan Kewarganegaraan. FIS UNNES berfokus pada pengembangan pemahaman tentang dinamika sosial, budaya, dan politik, serta keterampilan analitis dan kritis.'],
            ['nama' => 'Fakultas Ilmu Pendidikan dan Psikologi', 'deskripsi' => 'Fakultas Ilmu Pendidikan dan Psikologi (FIPP) merupakan fakultas yang menggabungkan disiplin ilmu pendidikan dan psikologi dengan fokus keahlian di bidang pengajaran, pembelajaran, serta pemahaman yang mendalam tentang perilaku manusia. Fakultas ini berperan penting dalam mencetak pendidik, konselor, dan psikolog yang profesional serta mampu beradaptasi dengan kebutuhan masyarakat dan perkembangan ilmu pengetahuan.'],
            ['nama' => 'Fakultas Teknik', 'deskripsi' => 'Fakultas Teknik Universitas (FT) adalah salah satu fakultas yang menawarkan pendidikan di bidang teknik dengan berbagai program studi seperti Teknik Sipil, Teknik Mesin, Teknik Elektro, Teknik Kimia, dan Pendidikan Teknik Informatika. Fakultas ini berfokus pada pengembangan inovasi teknologi dan keterampilan praktis, serta mendukung penelitian yang berkaitan dengan rekayasa teknologi.'],
            ['nama' => 'Fakultas Ilmu Keolahragaan', 'deskripsi' => 'Fakultas Ilmu Keolahragaan (FIK) adalah fakultas yang berfokus pada pendidikan dan pengembangan ilmu keolahragaan. FIK UNNES menawarkan program studi di bidang pendidikan jasmani, kesehatan, rekreasi, serta ilmu keolahragaan lainnya. Fakultas ini bertujuan untuk mencetak lulusan yang kompeten sebagai pendidik, pelatih, maupun profesional di bidang olahraga.'],
            ['nama' => 'Fakultas Ekonomika dan Bisnis', 'deskripsi' => 'Fakultas Ekonomika dan Bisnis (FEB) adalah fakultas yang menawarkan program studi di bidang ekonomi, manajemen, dan akuntansi. FEB UNNES berfokus pada pengembangan keterampilan analitis dan praktis di bidang ekonomi serta kewirausahaan. Dengan kurikulum yang relevan dan didukung oleh tenaga pengajar berkualitas, fakultas ini mempersiapkan lulusan yang kompeten dan inovatif, siap bersaing di dunia bisnis dan industri.'],
            ['nama' => 'Fakultas Hukum', 'deskripsi' => 'Fakultas Hukum (FH) adalah fakultas yang menawarkan pendidikan di bidang ilmu hukum dengan tujuan menghasilkan lulusan yang profesional dan berintegritas. FH UNNES menyediakan program studi yang berfokus pada berbagai cabang hukum seperti hukum perdata, pidana, tata negara, dan hukum internasional.', 'image' => ''],
            ['nama' => 'Fakultas Kedokteran', 'deskripsi' => 'Fakultas Kedokteran (FK) adalah fakultas yang menyediakan pendidikan di bidang ilmu kedokteran dengan fokus pada pembentukan tenaga medis yang profesional, berkompeten, dan beretika. FK UNNES menawarkan program studi kedokteran umum yang didukung oleh kurikulum berbasis penelitian serta fasilitas laboratorium dan klinik yang modern.'],
        ];

        foreach ($data as $value) {
            Fakultas::insert([
                'nama' => $value['nama'],
                'deskripsi' => $value['deskripsi'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
