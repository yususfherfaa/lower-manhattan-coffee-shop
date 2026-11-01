<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

class LowerManhattanProductDescriptions
{
    private const CATEGORY_DESCRIPTIONS = [
        'coffee' => [
            'our old time diner blend' => 'House blend seduh dengan karakter kopi yang akrab dan nyaman. Pilihan praktis untuk menemani jeda ngopi harianmu.',
            'columbian medium roast' => 'Kopi Columbian medium roast diseduh untuk menghadirkan rasa seimbang dan aroma yang mengundang. Pas untuk dinikmati kapan saja.',
            'ethiopia' => 'Kopi Ethiopia disajikan sebagai seduhan dengan karakter istimewa. Nikmati perlahan untuk pengalaman ngopi yang berbeda.',
        ],
        'tea' => [
            'lemon grass' => 'Teh serai yang diseduh dengan aroma herbal segar dan menenangkan. Cocok dinikmati hangat saat ingin jeda yang ringan.',
            'peppermint' => 'Teh peppermint seduh dengan aroma mint yang menyegarkan. Nikmati sebagai teman rehat yang ringan dan menenangkan.',
            'english breakfast' => 'Teh hitam English Breakfast yang diseduh dengan rasa mantap dan akrab. Teman pas untuk sarapan maupun jeda minum teh.',
            'earl grey' => 'Teh Earl Grey seduh dengan aroma khas yang elegan. Pilihan istimewa untuk menemani waktu santai.',
            'serenity green tea' => 'Teh hijau Serenity yang diseduh dengan karakter lembut dan ringan. Menyenangkan untuk menemani suasana santai.',
            'traditional blend chai' => 'Chai hangat hasil seduhan racikan klasik dengan aroma rempah. Nikmati kenyamanannya di waktu santai.',
            'morning sunrise chai' => 'Seduhan chai beraroma rempah yang pas untuk membuka hari dengan suasana lebih bersemangat.',
            'spicy eye opener chai' => 'Seduhan chai dengan karakter rempah yang berani dan menggugah, pilihan untuk pencinta teh beraroma kuat.',
        ],
        'drinking chocolate' => [
            'dark chocolate' => 'Minuman cokelat hitam dengan rasa kaya dan memanjakan. Nikmati hangat sebagai teman rehat yang istimewa.',
            'sustainably grown organic' => 'Minuman cokelat organik dari bahan yang ditanam berkelanjutan. Sajian nikmat untuk jeda yang terasa spesial.',
        ],
    ];

    private const DESCRIPTIONS = [
        'brazilian - organic' => 'Biji kopi Brazilian pilihan untuk seduhan dengan karakter kopi yang hangat dan mantap. Nikmati aroma kopinya di rumah dan racik sesuai cara favoritmu.',
        'our old time diner blend' => 'House blend khas yang serbaguna untuk teman memulai pagi atau menemani rehat. Pilihan nyaman untuk dinikmati sebagai seduhan harian.',
        'espresso roast' => 'Biji kopi sangrai untuk racikan espresso dengan karakter yang pekat dan penuh rasa. Cocok menjadi dasar minuman kopi favoritmu.',
        'primo espresso roast' => 'Pilihan espresso roast dengan karakter kopi yang tegas untuk sajian beraroma kuat. Nikmati sebagai espresso atau racik menjadi minuman favorit.',
        'columbian medium roast' => 'Kopi medium roast dengan karakter seimbang dan aroma yang mengundang. Pas untuk dinikmati sebagai seduhan harian kapan saja.',
        'ethiopia' => 'Kopi Ethiopia dengan karakter istimewa yang layak dinikmati perlahan. Seduh untuk menemukan pengalaman kopi yang berbeda di setiap jeda.',
        'jamacian coffee river' => 'Biji kopi Jamaican pilihan dengan karakter premium untuk momen ngopi yang terasa spesial. Nikmati aroma dan cita rasa kopinya dalam seduhan favoritmu.',
        'civet cat' => 'Biji kopi premium pilihan untuk pengalaman ngopi yang istimewa. Sajikan dengan metode seduh favorit agar karakter kopinya lebih terasa.',
        'organic decaf blend' => 'House blend organik tanpa kafein untuk menikmati momen kopi dengan lebih santai. Tetap nikmat sebagai teman rehat kapan pun kamu mau.',
        'guatemalan sustainably grown' => 'Biji kopi Guatemalan yang ditanam secara berkelanjutan, pilihan untuk seduhan dengan cerita asal yang menarik dan rasa kopi yang nyaman.',
        'lemon grass' => 'Teh herbal serai dengan aroma yang segar dan menenangkan. Pilihan ringan untuk menemani jeda dan membuat suasana terasa lebih rileks.',
        'peppermint' => 'Teh peppermint dengan sensasi aroma mint yang menyegarkan. Nikmati hangat untuk momen santai atau sebagai teman rehat yang ringan.',
        'english breakfast' => 'Teh hitam klasik dengan rasa yang mantap dan akrab. Teman pas untuk sarapan maupun jeda minum teh di tengah hari.',
        'earl grey' => 'Teh hitam Earl Grey dengan aroma khas yang elegan. Cocok dinikmati perlahan saat kamu ingin jeda teh yang terasa istimewa.',
        'serenity green tea' => 'Teh hijau dengan karakter lembut dan ringan. Pilihan menyenangkan untuk menemani waktu santai tanpa rasa yang berlebihan.',
        'traditional blend chai' => 'Racikan chai klasik dengan aroma rempah yang hangat. Nikmati sebagai teman bersantai dan temukan kenyamanan dalam setiap tegukan.',
        'morning sunrise chai' => 'Chai hangat untuk mengawali hari dengan suasana yang lebih bersemangat. Aroma rempahnya menjadikan waktu minum teh terasa spesial.',
        'spicy eye opener chai' => 'Chai dengan karakter rempah yang lebih berani dan menggugah. Pilihan tepat untuk kamu yang menyukai teh beraroma kuat.',
        'dark chocolate' => 'Cokelat hitam dengan karakter rasa yang kaya untuk dibuat menjadi minuman cokelat favorit. Nikmati hangat sebagai teman rehat yang memanjakan.',
        'sustainably grown organic' => 'Cokelat organik yang ditanam secara berkelanjutan, pilihan nikmat untuk sajian cokelat dengan perhatian pada asal bahan.',
        'chili mayan' => 'Cokelat bergaya Mayan dengan sentuhan cabai yang unik. Perpaduan karakter cokelat dan sensasi rempah untuk kamu yang ingin mencoba sesuatu berbeda.',
        'brazilian' => 'Kopi Brazilian seduh dengan karakter hangat dan mantap. Nikmati pilihan Small, Regular, atau Large untuk menyesuaikan momen ngopimu.',
        'jamaican coffee river' => 'Seduhan Jamaican Coffee River dengan karakter premium untuk momen ngopi yang terasa spesial. Pilih ukuran favorit dan nikmati perlahan.',
        'espresso shot' => 'Espresso dengan sajian ringkas dan karakter kopi yang pekat. Pilihan mantap untuk pencinta kopi atau sebagai awal racikan favoritmu.',
        'latte' => 'Espresso dan susu berpadu dalam sajian latte yang lembut dan creamy. Pilihan favorit untuk menikmati kopi dengan rasa yang lebih halus.',
        'cappuccino' => 'Perpaduan espresso, susu, dan foam yang menghadirkan sensasi cappuccino klasik. Nikmati teksturnya yang lembut di waktu santai.',
        'carmel syrup' => 'Sirup karamel untuk menambahkan sentuhan rasa manis dan aroma karamel pada minuman pilihanmu.',
        'caramel syrup' => 'Sirup karamel untuk menambahkan sentuhan rasa manis dan aroma karamel pada minuman pilihanmu.',
        'hazelnut syrup' => 'Sirup hazelnut dengan aroma kacang yang khas, pelengkap praktis untuk memberi sentuhan berbeda pada minuman favorit.',
        'sugar free vanilla syrup' => 'Sirup vanila bebas gula untuk memberi aroma vanila pada minuman pilihanmu dengan alternatif tanpa gula.',
        'hazelnut biscotti' => 'Biscotti dengan sentuhan hazelnut yang cocok dicelupkan ke kopi atau teh. Teman renyah untuk menemani obrolan.',
        'cranberry scone' => 'Scone dengan cranberry yang memberi sentuhan rasa buah pada camilan klasik. Pas disandingkan dengan kopi atau teh.',
        'chocolate croissant' => 'Croissant berlapis dengan isian cokelat yang memanjakan. Nikmati sebagai kudapan pagi atau teman ngopi sore.',
        'ginger scone' => 'Scone dengan sentuhan jahe yang menghadirkan aroma hangat dan karakter rasa yang berbeda untuk teman minum teh.',
        'almond croissant' => 'Croissant berlapis dengan sentuhan almond yang menggoda. Pilihan pastry istimewa untuk melengkapi secangkir kopi.',
        'ginger biscotti' => 'Biscotti renyah dengan sentuhan jahe yang hangat. Nikmat dicelupkan ke kopi atau teh saat mengambil jeda.',
        'croissant' => 'Pastry berlapis dengan tekstur renyah di luar dan lembut di dalam. Teman klasik untuk sarapan maupun waktu ngopi.',
        'chocolate chip biscotti' => 'Biscotti renyah dengan kepingan cokelat yang menyenangkan. Pas untuk dicelupkan ke kopi atau dinikmati sebagai camilan.',
        'oatmeal scone' => 'Scone dengan sentuhan oatmeal untuk camilan yang terasa akrab dan mengenyangkan. Cocok ditemani teh atau kopi.',
        'scottish cream scone' => 'Scone bergaya Scottish dengan sajian cream yang terasa istimewa. Teman pilihan untuk menikmati waktu minum teh.',
        'jumbo savory scone' => 'Scone gurih berukuran jumbo untuk kamu yang ingin camilan lebih mengenyangkan. Nikmati hangat bersama kopi favorit.',
        'i need my bean! t-shirt' => 'T-shirt Lower Manhattan Coffee Shop dengan slogan “I Need My Bean!” untuk menunjukkan kecintaanmu pada kopi.',
        'i need my bean! diner mug' => 'Diner mug bergaya klasik dengan slogan “I Need My Bean!”. Teman seru untuk menikmati minuman favorit di rumah.',
        'i need my bean! latte cup' => 'Latte cup dengan slogan “I Need My Bean!” yang cocok melengkapi ritual ngopi dan koleksi merchandise-mu.',
        'chocolate syrup' => 'Sirup cokelat untuk menambahkan sentuhan rasa cokelat pada minuman favorit atau racikan latte pilihanmu.',
        'ouro brasileiro shot' => 'Sajian espresso dengan karakter kopi Brazilian yang tegas. Pilihan ringkas bagi pencinta kopi dengan rasa yang mantap.',
    ];

    public static function fillMissing(BaseConnection $db): void
    {
        if (!$db->fieldExists('deskripsi', 'products')) {
            throw new \RuntimeException('Kolom products.deskripsi belum tersedia. Jalankan migrasi deskripsi produk terlebih dahulu.');
        }

        $products = $db->table('products')
            ->select('id_produk, nama, kategori, kategori_dataset, deskripsi')
            ->like('id_produk', 'PRDM%')
            ->get()
            ->getResultArray();

        foreach ($products as $product) {
            if (trim((string) ($product['deskripsi'] ?? '')) !== '') {
                continue;
            }

            $name = preg_replace('/\s+\((?:Sm|Rg|Lg)\)$/i', '', trim((string) $product['nama']));
            $name = strtolower((string) $name);
            $category = strtolower(trim((string) ($product['kategori_dataset'] ?? $product['kategori'] ?? '')));
            $description = self::CATEGORY_DESCRIPTIONS[$category][$name]
                ?? self::DESCRIPTIONS[$name]
                ?? null;

            if ($description === null) {
                throw new \RuntimeException('Deskripsi katalog belum tersedia untuk produk ' . $product['id_produk'] . ' (' . $product['nama'] . ').');
            }

            $db->table('products')
                ->where('id_produk', $product['id_produk'])
                ->where('(deskripsi IS NULL OR TRIM(deskripsi) = \'\')', null, false)
                ->update(['deskripsi' => $description]);
        }
    }

}
