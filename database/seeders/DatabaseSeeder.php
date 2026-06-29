<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ─── Utilisateurs ──────────────────────────────────────────────────────────

        $admin = User::firstOrCreate(['email' => 'admin@guema.bj'], [
            'name' => 'Admin BeniMarket', 'password' => Hash::make('admin123'),
            'role' => 'admin', 'status' => 'actif',
        ]);

        $client = User::firstOrCreate(['email' => 'client@guema.bj'], [
            'name' => 'Aïcha Dossou', 'password' => Hash::make('client123'),
            'role' => 'client', 'status' => 'actif',
        ]);

        $vendor1 = User::firstOrCreate(['phone' => '+22997111111'], [
            'name' => 'Mama Chantal', 'password' => Hash::make('vendor123'),
            'role' => 'vendor', 'status' => 'actif', 'zone' => 'Parakou',
        ]);
        $vendor2 = User::firstOrCreate(['phone' => '+22997222222'], [
            'name' => 'Tantie Reine', 'password' => Hash::make('vendor123'),
            'role' => 'vendor', 'status' => 'actif', 'zone' => 'Parakou',
        ]);
        $vendor3 = User::firstOrCreate(['phone' => '+22997333333'], [
            'name' => 'Alhaji Koudjo', 'password' => Hash::make('vendor123'),
            'role' => 'vendor', 'status' => 'actif', 'zone' => 'Parakou',
        ]);
        $vendor4 = User::firstOrCreate(['phone' => '+22997444444'], [
            'name' => 'Mariam Bio', 'password' => Hash::make('vendor123'),
            'role' => 'vendor', 'status' => 'actif', 'zone' => 'Parakou',
        ]);
        $vendor5 = User::firstOrCreate(['phone' => '+22997555555'], [
            'name' => 'Délices du Nord SARL', 'password' => Hash::make('vendor123'),
            'role' => 'vendor', 'status' => 'pending', 'zone' => 'Parakou',
        ]);

        User::firstOrCreate(['phone' => '+22997666666'], [
            'name' => 'Moussa Tchabi', 'password' => Hash::make('courier123'),
            'role' => 'courier', 'status' => 'actif', 'zone' => 'Parakou',
            'vehicle_type' => 'Moto Yamaha', 'plate_number' => 'RB 1234 X',
        ]);

        // ─── Catégories parentes ───────────────────────────────────────────────────

        $cFrais    = Category::firstOrCreate(['slug' => 'frais'], [
            'name' => 'Produits frais', 'icon' => 'Carrot', 'accent' => 'teal',
            'description' => 'Légumes, céréales et denrées alimentaires fraîches de Parakou.',
        ]);
        $cFruits   = Category::firstOrCreate(['slug' => 'fruits'], [
            'name' => 'Fruits & Légumes', 'icon' => 'Apple', 'accent' => 'amber',
            'description' => 'Fruits tropicaux et légumes locaux.',
        ]);
        $cEpices   = Category::firstOrCreate(['slug' => 'epices'], [
            'name' => 'Épices & Condiments', 'icon' => 'Flame', 'accent' => 'plum',
            'description' => 'Épices, piments, gingembre et aromates du Bénin.',
        ]);
        $cBoissons = Category::firstOrCreate(['slug' => 'boissons'], [
            'name' => 'Boissons', 'icon' => 'CupSoda', 'accent' => 'teal',
            'description' => 'Jus naturels, infusions et boissons artisanales.',
        ]);
        $cTissus   = Category::firstOrCreate(['slug' => 'tissus'], [
            'name' => 'Mode & Tissus', 'icon' => 'Shirt', 'accent' => 'plum',
            'description' => 'Wax, bazin, pagnes et accessoires de mode africaine.',
        ]);
        $cCosmetiq = Category::firstOrCreate(['slug' => 'cosmetiques'], [
            'name' => 'Cosmétiques naturels', 'icon' => 'Sparkles', 'accent' => 'plum',
            'description' => 'Karité, savons et soins naturels du terroir béninois.',
        ]);

        // ─── Sous-catégories ───────────────────────────────────────────────────────

        $scTomates   = Category::firstOrCreate(['slug' => 'tomates'], [
            'name' => 'Tomates', 'icon' => 'Carrot', 'accent' => 'teal',
            'parent_id' => $cFrais->id,
            'description' => 'Tomates fraîches de Parakou, du marché au panier.',
        ]);
        $scLegumes   = Category::firstOrCreate(['slug' => 'legumes-verts'], [
            'name' => 'Légumes verts', 'icon' => 'Leaf', 'accent' => 'teal',
            'parent_id' => $cFrais->id,
            'description' => 'Gombo, aubergines, feuilles vertes fraîches.',
        ]);
        $scCereales  = Category::firstOrCreate(['slug' => 'cereales'], [
            'name' => 'Céréales & Tubercules', 'icon' => 'Wheat', 'accent' => 'amber',
            'parent_id' => $cFrais->id,
            'description' => 'Riz, maïs, igname, manioc et autres féculents locaux.',
        ]);
        $scOignons   = Category::firstOrCreate(['slug' => 'oignons'], [
            'name' => 'Oignons & Ail', 'icon' => 'Carrot', 'accent' => 'teal',
            'parent_id' => $cFrais->id,
            'description' => 'Oignons violets, ail et condiments frais.',
        ]);

        $scBananes = Category::firstOrCreate(['slug' => 'bananes'], [
            'name' => 'Bananes & Plantain', 'icon' => 'Apple', 'accent' => 'amber',
            'parent_id' => $cFruits->id,
        ]);
        $scMangues = Category::firstOrCreate(['slug' => 'mangues'], [
            'name' => 'Mangues', 'icon' => 'Apple', 'accent' => 'amber',
            'parent_id' => $cFruits->id,
        ]);

        $scPiment    = Category::firstOrCreate(['slug' => 'piment'], [
            'name' => 'Piment', 'icon' => 'Flame', 'accent' => 'plum',
            'parent_id' => $cEpices->id,
        ]);
        $scGingembre = Category::firstOrCreate(['slug' => 'gingembre'], [
            'name' => 'Gingembre', 'icon' => 'Flame', 'accent' => 'plum',
            'parent_id' => $cEpices->id,
        ]);

        $scKarite = Category::firstOrCreate(['slug' => 'karite'], [
            'name' => 'Karité & Huiles', 'icon' => 'Sparkles', 'accent' => 'plum',
            'parent_id' => $cCosmetiq->id,
        ]);
        $scSavons = Category::firstOrCreate(['slug' => 'savons'], [
            'name' => 'Savons naturels', 'icon' => 'Sparkles', 'accent' => 'plum',
            'parent_id' => $cCosmetiq->id,
        ]);

        // ─── Boutiques ─────────────────────────────────────────────────────────────

        $shop1 = Shop::firstOrCreate(['slug' => 'saveurs-mama-chantal'], [
            'vendor_id'           => $vendor1->id,
            'name'                => 'Saveurs de Mama Chantal',
            'description'         => 'Produits frais du marché de Parakou, livrés chaque matin. Légumes, tomates, céréales.',
            'city'                => 'Parakou',
            'address'             => 'Stand N°12, Allée A, Marché Central',
            'phone'               => '+22997111111',
            'status'              => 'active',
            'documents_submitted' => true,
        ]);

        $shop2 = Shop::firstOrCreate(['slug' => 'karite-nature'], [
            'vendor_id'           => $vendor2->id,
            'name'                => 'Karité Nature',
            'description'         => 'Cosmétiques naturels, beurre de karité, savons et produits du terroir béninois.',
            'city'                => 'Parakou',
            'address'             => 'Stand N°05, Zone Artisanale, Parakou',
            'phone'               => '+22997222222',
            'status'              => 'active',
            'documents_submitted' => true,
        ]);

        $shop3 = Shop::firstOrCreate(['slug' => 'chez-alhaji-koudjo'], [
            'vendor_id'           => $vendor3->id,
            'name'                => 'Chez Alhaji Koudjo',
            'description'         => 'Épices rares, piments forts, gingembre et aromates directement des producteurs.',
            'city'                => 'Parakou',
            'address'             => 'Marché Arzèkè, Allée des Épices',
            'phone'               => '+22997333333',
            'status'              => 'active',
            'documents_submitted' => true,
        ]);

        $shop4 = Shop::firstOrCreate(['slug' => 'jardin-bio-mariam'], [
            'vendor_id'           => $vendor4->id,
            'name'                => 'Jardin Bio de Mariam',
            'description'         => 'Légumes bio cultivés sans pesticides à 5 km de Parakou. Tomates, gombo, aubergines.',
            'city'                => 'Parakou',
            'address'             => 'Quartier Albarika, Rue des Maraîchers',
            'phone'               => '+22997444444',
            'status'              => 'active',
            'documents_submitted' => true,
        ]);

        Shop::firstOrCreate(['slug' => 'delices-du-nord'], [
            'vendor_id'           => $vendor5->id,
            'name'                => 'Délices du Nord',
            'description'         => 'Spécialités culinaires et produits de saison du nord Bénin.',
            'city'                => 'Parakou',
            'status'              => 'pending',
            'documents_submitted' => true,
        ]);

        // ─── Produits ──────────────────────────────────────────────────────────────
        // Intentionnellement 4 boutiques différentes vendent des tomates
        // → la page CategorySellers "Tomates" affiche 4 vendeurs.

        $allProducts = [
            // ── SHOP 1 — Saveurs de Mama Chantal ──────────────────────────────────
            [$shop1->id, $scTomates->id,  'Tomates fraîches (tas)',      500,  30, 4.8, 47, ['Bio', 'Récolte du jour'],     'Tomates mûries au soleil, cueillies le matin. Parfaites pour vos sauces et salades.',          ['/tomates.webp']],
            [$shop1->id, $scTomates->id,  'Tomates cerise',              750,  12, 4.5, 18, ['Premium'],                   'Tomates cerises sucrées, idéales pour les salades et apéritifs.',                              ['/tomates.webp']],
            [$shop1->id, $scOignons->id,  'Oignon Violet',               400,  60, 4.6, 35, ['Légumes'],                   'Oignons violets de Parakou, très parfumés pour vos assaisonnements.',                          ['/oignon violet.jpg']],
            [$shop1->id, $scLegumes->id,  'Gombo frais',                 350,  25, 4.3, 22, ['Local', 'Bio'],              'Gombo frais récolté ce matin. Idéal pour la sauce gombo.',                                      []],
            [$shop1->id, $scCereales->id, 'Riz local (bol)',             600, 120, 4.4, 63, ['Céréales', 'Local'],         'Riz local produit au Bénin, savoureux et parfait pour tous vos plats.',                         ['/riz.jpg']],
            [$shop1->id, $cBoissons->id,  'Jus de Bissap',               500,  20, 4.7, 31, ['Artisanal', 'Naturel'],      'Jus de bissap fait maison, rafraîchissant et riche en antioxydants.',                           ['/photo_jus_bissap_cuisinovores-500x375.webp']],

            // ── SHOP 2 — Karité Nature ─────────────────────────────────────────────
            [$shop2->id, $scKarite->id,   'Beurre de karité (100g)',    1000,  30, 4.9, 84, ['Bio', 'Naturel'],            'Beurre de karité pur et naturel du Bénin. Hydratant pour la peau et les cheveux.',              ['/beurre de karité.jpg']],
            [$shop2->id, $scKarite->id,   'Beurre de karité (250g)',    2500,  15, 4.9, 52, ['Bio', 'Premium'],            'Grand format pour usage quotidien. Karité 100% naturel et non raffiné.',                         ['/beurre de karité.jpg']],
            [$shop2->id, $scSavons->id,   'Savon noir artisanal',        800,  40, 4.6, 29, ['Naturel', 'Traditionnel'],   'Savon noir africain traditionnel pour nettoyage en profondeur de la peau.',                      ['/savoir noir.webp']],
            [$shop2->id, $cTissus->id,    'Tissu Wax 6 yards',          9500,   8, 4.7, 14, ['Premium'],                   'Wax 100% coton, motifs africains authentiques. Lavable en machine.',                            ['/tissus-wax.jpg']],
            [$shop2->id, $scTomates->id,  'Tomates confites au piment', 1200,  10, 4.8, 19, ['Artisanal', 'Épicé'],       'Tomates confites maison relevées au piment. Parfaites pour accompagner vos plats.',             ['/tomates.webp']],

            // ── SHOP 3 — Chez Alhaji Koudjo ───────────────────────────────────────
            [$shop3->id, $scPiment->id,   'Piment rouge séché',          250,  80, 4.5, 66, ['Épices', 'Séché'],           'Piment rouge séché, très fort. Indispensable pour relever vos sauces.',                         ['/epicette-piment-rouge-seche-1.jpg']],
            [$shop3->id, $scPiment->id,   'Piment oiseau frais',         300,  45, 4.4, 38, ['Épices', 'Frais'],           'Petits piments oiseaux très puissants, cultivés à Parakou.',                                    []],
            [$shop3->id, $scGingembre->id,'Gingembre frais',             300,  50, 4.7, 44, ['Bio', 'Épices'],             'Gingembre frais de Parakou, idéal pour tisanes, marinades et plats épicés.',                     ['/gingembre.jpg']],
            [$shop3->id, $scGingembre->id,'Poudre de gingembre',         450,  35, 4.6, 27, ['Épices', 'Séché'],           'Gingembre séché et moulu finement. Pratique pour vos recettes.',                                ['/gingembre.jpg']],
            [$shop3->id, $scTomates->id,  'Tomates fraîches (panier)',  1800,  15, 4.6, 22, ['Local'],                     'Panier de tomates fraîches, direct du jardin. Environ 2 kg.',                                   ['/tomates.webp']],
            [$shop3->id, $scOignons->id,  'Ail local',                   350,  40, 4.3, 17, ['Local', 'Épices'],           'Ail local du Bénin, petites gousses très parfumées.',                                           []],

            // ── SHOP 4 — Jardin Bio de Mariam ─────────────────────────────────────
            [$shop4->id, $scTomates->id,  'Tomates bio (tas)',            600,  20, 4.9, 56, ['Bio', 'Sans pesticides'],    'Tomates certifiées sans pesticides, cultivées dans notre jardin bio à Albarika.',              ['/tomates.webp']],
            [$shop4->id, $scTomates->id,  'Tomates bio (sac 5 kg)',      2800,   8, 4.8, 21, ['Bio', 'Gros volume'],        'Sac de 5 kg de tomates bio. Idéal pour familles et restaurateurs.',                             ['/tomates.webp']],
            [$shop4->id, $scLegumes->id,  'Aubergine locale',             300,  35, 4.4, 19, ['Bio', 'Local'],              'Aubergines locales bio, parfaites pour les sauces et ragoûts.',                                 []],
            [$shop4->id, $scLegumes->id,  'Feuilles de moringa',          200,  50, 4.7, 33, ['Bio', 'Santé'],              'Feuilles de moringa fraîches, riches en nutriments et antioxydants.',                           []],
            [$shop4->id, $scCereales->id, 'Igname blanche',               800,  25, 4.5, 28, ['Local', 'Tubercule'],        'Igname blanche de bonne qualité. Idéale pour le pilé et les ragoûts.',                          []],
            [$shop4->id, $scBananes->id,  'Bananes plantain mûres',       450,  18, 4.6, 41, ['Local'],                     'Bananes plantain bien mûres, idéales pour alloco et accompagnements.',                          ['photo-1603833665858-e61d17a86224']],
            [$shop4->id, $scMangues->id,  'Mangues kent',                 350,  30, 4.8, 24, ['Local', 'Saison'],           'Mangues kent juteuses et sucrées. En saison de mai à juillet.',                                 []],
            [$shop4->id, $cBoissons->id,  'Jus de gingembre citron',      600,  15, 4.5, 16, ['Artisanal', 'Santé'],        'Jus frais de gingembre et citron, tonique et rafraîchissant.',                                  []],
        ];

        foreach ($allProducts as [$shopId, $catId, $name, $price, $stock, $rating, $reviews, $tags, $desc, $images]) {
            Product::firstOrCreate(
                ['shop_id' => $shopId, 'name' => $name],
                [
                    'category_id'  => $catId,
                    'price'        => $price,
                    'stock'        => $stock,
                    'rating'       => $rating,
                    'reviews_count'=> $reviews,
                    'tags'         => $tags,
                    'description'  => $desc,
                    'images'       => $images,
                    'status'       => 'active',
                ]
            );
        }

        // ─── Notification de bienvenue ─────────────────────────────────────────────
        Notification::firstOrCreate(
            ['user_id' => $client->id, 'type' => 'welcome'],
            [
                'title' => 'Bienvenue sur BeniMarket !',
                'body'  => 'Votre compte client est actif. Explorez les catégories pour trouver vos produits.',
            ]
        );
    }
}
