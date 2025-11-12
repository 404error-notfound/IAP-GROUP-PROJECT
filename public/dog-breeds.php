<?php
// dog-breeds.php
// Simple static listing of dog breeds with image placeholders and metadata
// External reference links for each breed (opens in a new tab).
// Default local paths for breed images. Update these files in public/images/breeds/ (use .jpg/.png as you prefer).
$breedLinks = [
    'German Shepherd' => 'images/breeds/german-shepherd.jpg',
    'Golden Retriever' => 'images/breeds/golden-retriever.jpg',
    'Japanese Spitz' => 'images/breeds/japanese-spitz.jpg',
    'Husky' => 'images/breeds/husky.jpg',
    'Rottweiler' => 'images/breeds/rottweiler.jpg',
    'Pug' => 'images/breeds/pug.jpg',
    'Pitbull' => 'images/breeds/pitbull.jpg',
    'Dachshund' => 'images/breeds/dachshund.jpg',
    'Doberman Pinscher' => 'images/breeds/doberman-pinscher.jpg',
    'Poodle' => 'images/breeds/poodle.jpg',
    'Bulldog' => 'images/breeds/bulldog.jpg',
    'Bloodhound' => 'images/breeds/bloodhound.jpg',
    'Cocker Spaniel' => 'images/breeds/cocker-spaniel.jpg'
];

$breeds = [
    ['name'=>'German Shepherd','lifespan'=>'9–13 years','coat'=>'Double (medium)','group'=>'Herding','grooming'=>'Moderate','desc'=>'Loyal, intelligent working dog used for police, military and family companionship.'],
    ['name'=>'Golden Retriever','lifespan'=>'10–12 years','coat'=>'Dense, water-repellent (long)','group'=>'Sporting','grooming'=>'High','desc'=>'Friendly, eager-to-please companion and working dog; great with families and kids.'],
    ['name'=>'Japanese Spitz','lifespan'=>'12–16 years','coat'=>'Fluffy double coat','group'=>'Companion','grooming'=>'Moderate','desc'=>'Small to medium companion dog, lively and affectionate with a striking white coat.'],
    ['name'=>'Husky','lifespan'=>'12–14 years','coat'=>'Thick double coat','group'=>'Working/Sled','grooming'=>'High (seasonal)','desc'=>'Energetic, independent sled dog with high exercise needs and friendly nature.'],
    ['name'=>'Rottweiler','lifespan'=>'8–10 years','coat'=>'Short, dense','group'=>'Working/Security','grooming'=>'Low','desc'=>'Strong, confident guardian and working dog; requires firm, consistent training.'],
    ['name'=>'Pug','lifespan'=>'12–15 years','coat'=>'Short, smooth','group'=>'Companion','grooming'=>'Low','desc'=>'Small, affectionate brachycephalic breed with a playful personality; watch for breathing/eye issues.'],
    ['name'=>'Pitbull','lifespan'=>'12–16 years','coat'=>'Short, smooth','group'=>'Terrier/Working','grooming'=>'Low','desc'=>'Energetic, strong and loyal; benefits from early socialization and regular exercise.'],
    ['name'=>'Dachshund','lifespan'=>'12–16 years','coat'=>'Smooth/Wire/Long','group'=>'Hound','grooming'=>'Low–Moderate','desc'=>'Distinctive long-backed hound, brave and curious; prone to back problems so avoid high jumps.'],
    ['name'=>'Doberman Pinscher','lifespan'=>'10–13 years','coat'=>'Short, smooth','group'=>'Working/Security','grooming'=>'Low','desc'=>'Alert, loyal guard dog with high intelligence and exercise needs.'],
    ['name'=>'Poodle','lifespan'=>'10–18 years (varies)','coat'=>'Curly, non-shedding','group'=>'Sporting/Companion','grooming'=>'High (clipping)','desc'=>'Highly intelligent, versatile (standard/mini/toy), great for allergy-prone owners when clipped.'],
    ['name'=>'Bulldog','lifespan'=>'8–10 years','coat'=>'Short, smooth','group'=>'Companion','grooming'=>'Low','desc'=>'Calm, gentle companion with a distinctive wrinkled face; sensitive to heat due to brachycephalic structure.'],
    ['name'=>'Bloodhound','lifespan'=>'10–12 years','coat'=>'Short, dense','group'=>'Hound','grooming'=>'Low','desc'=>'Outstanding scent hound with a calm disposition; excels at tracking and search work.'],
    ['name'=>'Cocker Spaniel','lifespan'=>'10–14 years','coat'=>'Silky, medium','group'=>'Sporting','grooming'=>'High','desc'=>'Affectionate, gentle hunting companion with long ears and a beautiful coat that needs grooming.']
];

// Helper: placeholder image when no local image is present.
function placeholder($text, $w=420, $h=260) {
    $t = urlencode($text);
    return "https://via.placeholder.com/{$w}x{$h}.png?text={$t}";
}

// Helper: create a slug to match local image filenames (user will add images here)
function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}
?>
<!doctype html>
<html lang="en">
<head>
    <link rel="icon" href="/IAP-GROUP-PROJECT/public/images/favicon.svg" type="image/svg+xml">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dog Breeds — GoPuppyGo</title>
    <style>
        :root{--bg:#f6f8fa;--card:#fff;--accent:#2b5da8;--muted:#666}
        body{font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:var(--bg);margin:0;padding:24px}
        .wrap{max-width:1200px;margin:0 auto}
        header{margin-bottom:18px}
        h1{color:var(--accent);margin:0 0 6px}
        p.lead{color:var(--muted);margin:0 0 18px}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px}
        .card{background:var(--card);border-radius:10px;box-shadow:0 6px 18px rgba(15,30,60,.06);overflow:hidden;display:flex;flex-direction:column}
        /* Cards are anchors; remove default link styling so inner text isn't blue/underlined */
        a.card, a.card * {
            color: inherit;
            text-decoration: none;
        }

        /* Ensure images fit inside the card image area without being cropped */
        .card img{
            width: 100%;
            height: 180px;
            object-fit: contain; /* show whole image */
            object-position: center;
            background: #f4f6f8; /* neutral backdrop for letterboxing */
            display: block;
        }

        /* Hover affordance for the whole card */
        a.card{display:block;transition:transform .18s ease,box-shadow .18s ease}
        a.card:hover{ /* subtle hover lift */
            transform: translateY(-4px);
            box-shadow:0 12px 24px rgba(15,30,60,.12);
        }
        .card img{width:100%;height:180px;object-fit:cover;display:block}
        .card-body{padding:14px;flex:1;display:flex;flex-direction:column}
        .breed-name{font-size:18px;font-weight:700;color:#183b6b;margin-bottom:6px}
        .meta{font-size:13px;color:var(--muted);margin-bottom:8px}
        .meta strong{color:#222}
        .desc{flex:1;color:#333;font-size:14px;margin-bottom:12px}
        .tags{display:flex;gap:8px;flex-wrap:wrap}
        .tag{background:#eef6ff;color:#1f5fb0;padding:6px 8px;border-radius:6px;font-size:12px}
        footer{margin-top:22px;text-align:center;color:var(--muted);font-size:13px}
        @media (max-width:420px){.card img{height:140px}}
    </style>
</head>
<body>
    <div class="wrap">
        <header>
            <h1 style="text-align: center;">Dog Breeds</h1>
        </header>

        <section class="grid">
            <?php foreach ($breeds as $b): ?>
                <?php
                    // Prefer a local image in public/images/breeds/ named like the slug (jpg|jpeg|png). If not present use placeholder.
                    $slug = slugify($b['name']);
                    $localPattern = __DIR__ . '/images/breeds/' . $slug . '.*';
                    $matches = glob($localPattern, GLOB_BRACE);
                    if (!empty($matches)) {
                        $imgSrc = 'images/breeds/' . basename($matches[0]);
                    } else {
                        $imgSrc = placeholder($b['name']);
                    }

                    // If a local image exists, link the card to that image file; otherwise fall back to the external breed reference
                    if (strpos($imgSrc, 'images/breeds/') === 0) {
                        // link to local image
                        $link = $imgSrc;
                    } else {
                        // external reference (Wikipedia) as fallback
                        $link = $breedLinks[$b['name']] ?? 'https://en.wikipedia.org/wiki/Dog';
                    }
                ?>
                <a class="card" href="<?php echo htmlspecialchars($link); ?>" target="_blank" rel="noopener noreferrer">
                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($b['name']); ?>">
                    <div class="card-body">
                        <div class="breed-name"><?php echo htmlspecialchars($b['name']); ?></div>
                        <div class="meta"><strong>Lifespan:</strong> <?php echo htmlspecialchars($b['lifespan']); ?> &nbsp; • &nbsp; <strong>Coat:</strong> <?php echo htmlspecialchars($b['coat']); ?></div>
                        <div class="desc"><?php echo htmlspecialchars($b['desc']); ?></div>
                        <div class="tags">
                            <div class="tag">Specialty: <?php echo htmlspecialchars($b['group']); ?></div>
                            <div class="tag">Grooming: <?php echo htmlspecialchars($b['grooming']); ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </section>

        <footer>
            © GoPuppyGo — Breed summaries are brief; always research specific lines and individual dogs for health & temperament.
        </footer>
    </div>
</body>
</html>
