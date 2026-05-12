<!DOCTYPE html>
<html lang="fr">
<head>
    <title>PokéGuess - Devine la carte Pokémon !</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./style/pokemon_guess.css">
</head>
<body>
    <div class="game-wrapper">
        <header class="game-header">
            <div class="header-top">
                <h1>PokéGuess</h1>
                <div id="streak-display" class="streak hidden">🔥 <span id="streak-count">0</span></div>
            </div>
            <p class="subtitle">Devine le Pokémon à partir d'un extrait de sa carte TCG !</p>
            <?php if (isset($_SESSION['id'])): ?>
            <a href="index.php?uc=dashboard" class="back-link">← Retour au dashboard</a>
            <?php endif; ?>
        </header>

        <!-- Sélecteur de difficulté -->
        <div class="difficulty-selector">
            <span class="diff-label">Difficulté :</span>
            <button id="diff-normal" class="diff-btn diff-active">
                Normal
                <small>EX · V · GX · Prime · Lv.X…</small>
            </button>
            <button id="diff-hard" class="diff-btn">
                Difficile
                <small>Toutes raretés (Commune incluse)</small>
            </button>
        </div>

        <!-- État : chargement -->
        <div id="loading" class="loading">
            <div class="loading-spinner"></div>
            <p id="loading-text">Chargement de la carte...</p>
        </div>

        <!-- État : erreur réseau -->
        <div id="error-state" class="error-state hidden">
            <p id="error-msg">Impossible de charger la carte.</p>
            <button id="retry-btn" class="new-game-btn">Réessayer</button>
        </div>

        <div id="game-area" class="hidden">

            <div class="image-section">
                <!-- Le cadre a son propre loader tant que l'image n'est pas prête -->
                <div class="pokemon-frame" id="pokemon-frame">
                    <div id="card-skeleton" class="card-skeleton"></div>
                    <img id="pokemon-img" src="" alt="Carte Pokémon mystère" class="pokemon-img hidden">
                </div>
                <div class="image-meta">
                    <div id="attempts-badge" class="attempts-badge">Tentative 1 / 6</div>
                    <div id="rarity-badge" class="rarity-badge hidden"></div>
                </div>
            </div>

            <div class="attempt-dots" id="attempt-dots"></div>

            <div id="hints-area" class="hints-area hidden">
                <h3>Indices</h3>
                <ul id="hints-list"></ul>
            </div>

            <div class="input-section">
                <div class="autocomplete-wrapper">
                    <input
                        type="text"
                        id="guess-input"
                        placeholder="Nom du Pokémon..."
                        autocomplete="off"
                        spellcheck="false"
                        maxlength="50"
                    >
                    <div id="autocomplete-list" class="autocomplete-list hidden" role="listbox"></div>
                </div>
                <button id="submit-btn" class="submit-btn">Valider</button>
                <button id="skip-btn" class="skip-btn">Passer</button>
            </div>

            <div class="wrong-guesses">
                <div id="wrong-list"></div>
            </div>

            <div id="result-area" class="result-area hidden">
                <div id="result-message"></div>
                <button id="new-game-btn" class="new-game-btn">Nouvelle carte</button>
            </div>

        </div>
    </div>

    <script>
    // ── Données Gen 1 (hardcodé pour éviter un appel API supplémentaire) ──────
    const POKEMON_GEN1 = [
        {id:1,en:'bulbasaur',fr:'Bulbizarre'},{id:2,en:'ivysaur',fr:'Herbizarre'},
        {id:3,en:'venusaur',fr:'Florizarre'},{id:4,en:'charmander',fr:'Salamèche'},
        {id:5,en:'charmeleon',fr:'Reptincel'},{id:6,en:'charizard',fr:'Dracaufeu'},
        {id:7,en:'squirtle',fr:'Carapuce'},{id:8,en:'wartortle',fr:'Carabaffe'},
        {id:9,en:'blastoise',fr:'Tortank'},{id:10,en:'caterpie',fr:'Chenipan'},
        {id:11,en:'metapod',fr:'Chrysacier'},{id:12,en:'butterfree',fr:'Papilusion'},
        {id:13,en:'weedle',fr:'Aspicot'},{id:14,en:'kakuna',fr:'Coconfort'},
        {id:15,en:'beedrill',fr:'Dardargnan'},{id:16,en:'pidgey',fr:'Roucool'},
        {id:17,en:'pidgeotto',fr:'Roucoups'},{id:18,en:'pidgeot',fr:'Roucarnage'},
        {id:19,en:'rattata',fr:'Rattata'},{id:20,en:'raticate',fr:'Rattatac'},
        {id:21,en:'spearow',fr:'Piafabec'},{id:22,en:'fearow',fr:'Rapasdepic'},
        {id:23,en:'ekans',fr:'Abo'},{id:24,en:'arbok',fr:'Arbok'},
        {id:25,en:'pikachu',fr:'Pikachu'},{id:26,en:'raichu',fr:'Raichu'},
        {id:27,en:'sandshrew',fr:'Sabelette'},{id:28,en:'sandslash',fr:'Sablaireau'},
        {id:29,en:'nidoran-f',fr:'Nidoran♀',tcg:'Nidoran♀'},
        {id:30,en:'nidorina',fr:'Nidorina'},{id:31,en:'nidoqueen',fr:'Nidoqueen'},
        {id:32,en:'nidoran-m',fr:'Nidoran♂',tcg:'Nidoran♂'},
        {id:33,en:'nidorino',fr:'Nidorino'},{id:34,en:'nidoking',fr:'Nidoking'},
        {id:35,en:'clefairy',fr:'Mélofée'},{id:36,en:'clefable',fr:'Mélodelfe'},
        {id:37,en:'vulpix',fr:'Goupix'},{id:38,en:'ninetales',fr:'Ninetales'},
        {id:39,en:'jigglypuff',fr:'Rondoudou'},{id:40,en:'wigglytuff',fr:'Grodoudou'},
        {id:41,en:'zubat',fr:'Nosferapti'},{id:42,en:'golbat',fr:'Nosferalto'},
        {id:43,en:'oddish',fr:'Mystherbe'},{id:44,en:'gloom',fr:'Ortide'},
        {id:45,en:'vileplume',fr:'Rafflesia'},{id:46,en:'paras',fr:'Paras'},
        {id:47,en:'parasect',fr:'Parasect'},{id:48,en:'venonat',fr:'Mimitoss'},
        {id:49,en:'venomoth',fr:'Aéromite'},{id:50,en:'diglett',fr:'Taupiqueur'},
        {id:51,en:'dugtrio',fr:'Triopikeur'},{id:52,en:'meowth',fr:'Meowth'},
        {id:53,en:'persian',fr:'Persian'},{id:54,en:'psyduck',fr:'Psykokwak'},
        {id:55,en:'golduck',fr:'Akwakwak'},{id:56,en:'mankey',fr:'Férosinge'},
        {id:57,en:'primeape',fr:'Colossinge'},{id:58,en:'growlithe',fr:'Caninos'},
        {id:59,en:'arcanine',fr:'Arcanin'},{id:60,en:'poliwag',fr:'Ptitard'},
        {id:61,en:'poliwhirl',fr:'Têtarte'},{id:62,en:'poliwrath',fr:'Tarpaud'},
        {id:63,en:'abra',fr:'Abra'},{id:64,en:'kadabra',fr:'Kadabra'},
        {id:65,en:'alakazam',fr:'Alakazam'},{id:66,en:'machop',fr:'Machoc'},
        {id:67,en:'machoke',fr:'Machopeur'},{id:68,en:'machamp',fr:'Mackogneur'},
        {id:69,en:'bellsprout',fr:'Chétiflor'},{id:70,en:'weepinbell',fr:'Boustiflor'},
        {id:71,en:'victreebel',fr:'Empiflor'},{id:72,en:'tentacool',fr:'Tentacool'},
        {id:73,en:'tentacruel',fr:'Tentacruel'},{id:74,en:'geodude',fr:'Racaillou'},
        {id:75,en:'graveler',fr:'Gravalanch'},{id:76,en:'golem',fr:'Grolem'},
        {id:77,en:'ponyta',fr:'Ponyta'},{id:78,en:'rapidash',fr:'Galopa'},
        {id:79,en:'slowpoke',fr:'Ramoloss'},{id:80,en:'slowbro',fr:'Flagadoss'},
        {id:81,en:'magnemite',fr:'Magnéti'},{id:82,en:'magneton',fr:'Magnéton'},
        {id:83,en:'farfetchd',fr:'Canarticho',tcg:"Farfetch'd"},
        {id:84,en:'doduo',fr:'Doduo'},{id:85,en:'dodrio',fr:'Dodrio'},
        {id:86,en:'seel',fr:'Otaria'},{id:87,en:'dewgong',fr:'Lamantine'},
        {id:88,en:'grimer',fr:'Tadmorv'},{id:89,en:'muk',fr:'Grotadmorv'},
        {id:90,en:'shellder',fr:'Kokiyas'},{id:91,en:'cloyster',fr:'Crustabri'},
        {id:92,en:'gastly',fr:'Fantominus'},{id:93,en:'haunter',fr:'Spectrum'},
        {id:94,en:'gengar',fr:'Ectoplasma'},{id:95,en:'onix',fr:'Onix'},
        {id:96,en:'drowzee',fr:'Soporifik'},{id:97,en:'hypno',fr:'Hypnomade'},
        {id:98,en:'krabby',fr:'Krabby'},{id:99,en:'kingler',fr:'Krabboss'},
        {id:100,en:'voltorb',fr:'Voltorbe'},{id:101,en:'electrode',fr:'Électrode'},
        {id:102,en:'exeggcute',fr:'Noeunoeuf'},{id:103,en:'exeggutor',fr:'Noadkoko'},
        {id:104,en:'cubone',fr:'Osselait'},{id:105,en:'marowak',fr:'Ossatueur'},
        {id:106,en:'hitmonlee',fr:'Kicklee'},{id:107,en:'hitmonchan',fr:'Tygnon'},
        {id:108,en:'lickitung',fr:'Excelangue'},{id:109,en:'koffing',fr:'Smogo'},
        {id:110,en:'weezing',fr:'Smogogo'},{id:111,en:'rhyhorn',fr:'Rhinocorne'},
        {id:112,en:'rhydon',fr:'Rhinoféros'},{id:113,en:'chansey',fr:'Leveinard'},
        {id:114,en:'tangela',fr:'Saquedeneu'},{id:115,en:'kangaskhan',fr:'Kangaskhan'},
        {id:116,en:'horsea',fr:'Hypotrempe'},{id:117,en:'seadra',fr:'Hypocéan'},
        {id:118,en:'goldeen',fr:'Poissirène'},{id:119,en:'seaking',fr:'Poissoroy'},
        {id:120,en:'staryu',fr:'Stari'},{id:121,en:'starmie',fr:'Staross'},
        {id:122,en:'mr-mime',fr:'M. Mime',tcg:'Mr. Mime'},
        {id:123,en:'scyther',fr:'Insécateur'},{id:124,en:'jynx',fr:'Lippoutou'},
        {id:125,en:'electabuzz',fr:'Élektek'},{id:126,en:'magmar',fr:'Magmar'},
        {id:127,en:'pinsir',fr:'Scarabrute'},{id:128,en:'tauros',fr:'Tauros'},
        {id:129,en:'magikarp',fr:'Magicarpe'},{id:130,en:'gyarados',fr:'Léviator'},
        {id:131,en:'lapras',fr:'Lokhlass'},{id:132,en:'ditto',fr:'Métamorph'},
        {id:133,en:'eevee',fr:'Évoli'},{id:134,en:'vaporeon',fr:'Aquali'},
        {id:135,en:'jolteon',fr:'Voltali'},{id:136,en:'flareon',fr:'Pyroli'},
        {id:137,en:'porygon',fr:'Porygon'},{id:138,en:'omanyte',fr:'Amonita'},
        {id:139,en:'omastar',fr:'Amonistar'},{id:140,en:'kabuto',fr:'Kabuto'},
        {id:141,en:'kabutops',fr:'Kabutops'},{id:142,en:'aerodactyl',fr:'Aérodactyle'},
        {id:143,en:'snorlax',fr:'Ronflex'},{id:144,en:'articuno',fr:'Artikodin'},
        {id:145,en:'zapdos',fr:'Électhor'},{id:146,en:'moltres',fr:'Sulfura'},
        {id:147,en:'dratini',fr:'Minidraco'},{id:148,en:'dragonair',fr:'Draco'},
        {id:149,en:'dragonite',fr:'Dracolosse'},{id:150,en:'mewtwo',fr:'Mewtwo'},
        {id:151,en:'mew',fr:'Mew'}
    ];

    const ULTRA_RARE = new Set([
        'Rare Holo EX','Rare Ultra','Rare Holo GX',
        'Rare Holo V','Rare Holo VMAX','Rare Holo VSTAR',
        'Rare Holo LV.X','Rare Prime','LEGEND',
        'Ultra Rare','Secret Rare','Rare Secret',
        'Special Illustration Rare','Illustration Rare',
        'Double Rare','Hyper Rare','Amazing Rare','Radiant Rare',
        'Rare Shining','Rare Holo Star','Rare BREAK','Rare ACE',
        'ACE SPEC Rare','Shiny Rare','Shiny Ultra Rare',
        'Trainer Gallery Rare Holo','Classic Collection',
    ]);

    const TYPE_FR = {
        Fire:'Feu', Water:'Eau', Grass:'Plante', Lightning:'Électrik',
        Psychic:'Psy', Fighting:'Combat', Darkness:'Ténèbres', Metal:'Acier',
        Dragon:'Dragon', Fairy:'Fée', Colorless:'Incolore',
        fire:'Feu', water:'Eau', grass:'Plante', electric:'Électrik',
        psychic:'Psy', fighting:'Combat', dark:'Ténèbres', steel:'Acier',
        dragon:'Dragon', fairy:'Fée', normal:'Normal', ice:'Glace',
        poison:'Poison', ground:'Sol', flying:'Vol', bug:'Insecte',
        rock:'Roche', ghost:'Spectre',
    };

    const STAGES     = [4, 3, 2, 1.5, 1.2, 1];
    const MAX_ATTEMPTS = 6;
    const MAX_RETRY    = 6;

    // ── Cache mémoire pour éviter de refetch la même carte ────────────────────
    // Map<pokemonId, { imageUrl, types, rarity }[]>
    const cardCache = new Map();

    let difficulty = 'normal';
    let streak     = 0;
    let acIndex    = -1; // index actif dans l'autocomplétion (navigation clavier)

    const state = {
        pokemon:  null,
        types:    [],
        rarity:   '',
        imageUrl: '',
        attempts: 0,
        wrongGuesses: [],
        gameOver: false,
        cropX: 50,
        cropY: 38,
    };

    const $ = id => document.getElementById(id);
    const $img         = $('pokemon-img');
    const $input       = $('guess-input');
    const $submit      = $('submit-btn');
    const $skip        = $('skip-btn');
    const $ac          = $('autocomplete-list');
    const $hintsArea   = $('hints-area');
    const $hintsList   = $('hints-list');
    const $wrongList   = $('wrong-list');
    const $resultArea  = $('result-area');
    const $resultMsg   = $('result-message');
    const $loading     = $('loading');
    const $loadingTxt  = $('loading-text');
    const $errorState  = $('error-state');
    const $errorMsg    = $('error-msg');
    const $gameArea    = $('game-area');
    const $dots        = $('attempt-dots');
    const $badge       = $('attempts-badge');
    const $rarityBadge = $('rarity-badge');
    const $skeleton    = $('card-skeleton');
    const $streakEl    = $('streak-display');
    const $streakCount = $('streak-count');

    // ── Difficulté ─────────────────────────────────────────────────────────────
    function setDifficulty(level) {
        difficulty = level;
        $('diff-normal').classList.toggle('diff-active', level === 'normal');
        $('diff-hard').classList.toggle('diff-active', level === 'hard');
        initGame();
    }
    $('diff-normal').addEventListener('click', () => setDifficulty('normal'));
    $('diff-hard').addEventListener('click',   () => setDifficulty('hard'));

    // ── Initialisation ─────────────────────────────────────────────────────────
    async function initGame(retryCount = 0) {
        if (retryCount === 0) {
            $loading.classList.remove('hidden');
            $gameArea.classList.add('hidden');
            $errorState.classList.add('hidden');
        }
        $loadingTxt.textContent = retryCount === 0
            ? 'Chargement de la carte...'
            : `Recherche d'une carte rare... (${retryCount}/${MAX_RETRY})`;

        Object.assign(state, {
            attempts: 0,
            wrongGuesses: [],
            gameOver: false,
            cropX: 35 + Math.random() * 30,
            cropY: 28 + Math.random() * 14,
        });

        try {
            state.pokemon = POKEMON_GEN1[Math.floor(Math.random() * POKEMON_GEN1.length)];

            // ── Récupération des cartes (avec cache mémoire) ───────────────────
            let pool = await fetchCards(state.pokemon);

            if (difficulty === 'normal') {
                const ultraPool = pool.filter(c => ULTRA_RARE.has(c.rarity));
                if (ultraPool.length === 0 && retryCount < MAX_RETRY) {
                    return initGame(retryCount + 1);
                }
                pool = ultraPool.length > 0 ? ultraPool : pool;
            }

            if (pool.length === 0) {
                showError('Aucune carte trouvée pour ce Pokémon.');
                return;
            }

            const card     = pool[Math.floor(Math.random() * pool.length)];
            state.imageUrl = card.imageUrl;
            state.types    = card.types;
            state.rarity   = card.rarity;

            // ── Attendre le chargement de l'image avant de révéler le jeu ─────
            await loadImage(state.imageUrl);

            applyZoom(0);
            resetUI();

            $loading.classList.add('hidden');
            $gameArea.classList.remove('hidden');

        } catch (err) {
            console.error(err);
            showError('Erreur réseau. Vérifie ta connexion internet.');
        }
    }

    // Fetch avec cache mémoire — retourne un tableau de { imageUrl, types, rarity }
    async function fetchCards(pokemon) {
        if (cardCache.has(pokemon.id)) return cardCache.get(pokemon.id);

        const tcgName = pokemon.tcg
            || (pokemon.en.charAt(0).toUpperCase() + pokemon.en.slice(1));

        const res  = await fetch(
            `https://api.pokemontcg.io/v2/cards?q=name:"${encodeURIComponent(tcgName)}"&pageSize=100&select=id,name,images,types,rarity`
        );
        if (!res.ok) throw new Error(`TCG API ${res.status}`);

        const data  = await res.json();
        const cards = (data.data || [])
            .filter(c => c.images?.large || c.images?.small)
            .map(c => ({
                imageUrl: c.images.large || c.images.small,
                types:    c.types  || [],
                rarity:   c.rarity || '',
            }));

        // Fallback PokéAPI si aucune carte TCG
        if (cards.length === 0) {
            const pk   = await fetch(`https://pokeapi.co/api/v2/pokemon/${pokemon.id}`);
            if (!pk.ok) throw new Error(`PokéAPI ${pk.status}`);
            const pkd  = await pk.json();
            const fb = [{
                imageUrl: pkd.sprites.other['official-artwork'].front_default,
                types:    pkd.types.map(t => t.type.name),
                rarity:   '',
            }];
            cardCache.set(pokemon.id, fb);
            return fb;
        }

        cardCache.set(pokemon.id, cards);
        return cards;
    }

    // Charge une image et résout la promise quand elle est prête
    function loadImage(src) {
        return new Promise((resolve, reject) => {
            $skeleton.classList.remove('hidden');
            $img.classList.add('hidden');
            const tmp = new Image();
            tmp.onload  = () => {
                $img.src = src;
                $img.classList.remove('hidden');
                $skeleton.classList.add('hidden');
                resolve();
            };
            tmp.onerror = () => reject(new Error(`Image introuvable : ${src}`));
            tmp.src = src;
        });
    }

    function showError(msg) {
        $loading.classList.add('hidden');
        $gameArea.classList.add('hidden');
        $errorMsg.textContent = msg;
        $errorState.classList.remove('hidden');
    }

    function resetUI() {
        $hintsList.innerHTML  = '';
        $wrongList.innerHTML  = '';
        $hintsArea.classList.add('hidden');
        $resultArea.classList.add('hidden');
        $rarityBadge.classList.add('hidden');
        $input.value     = '';
        $input.disabled  = false;
        $submit.disabled = false;
        $skip.disabled   = false;
        acIndex = -1;
        renderDots();
        $badge.textContent = `Tentative 1 / ${MAX_ATTEMPTS}`;
    }

    // ── Zoom CSS ───────────────────────────────────────────────────────────────
    function applyZoom(attemptIdx) {
        const scale = STAGES[Math.min(attemptIdx, STAGES.length - 1)];
        $img.style.transformOrigin = scale > 1
            ? `${state.cropX}% ${state.cropY}%`
            : '50% 50%';
        $img.style.transform = `scale(${scale})`;
    }

    // ── Dots ───────────────────────────────────────────────────────────────────
    function renderDots() {
        $dots.innerHTML = '';
        for (let i = 0; i < MAX_ATTEMPTS; i++) {
            const d = document.createElement('span');
            d.className = 'dot' + (i < state.wrongGuesses.length ? ' wrong' : '');
            $dots.appendChild(d);
        }
    }

    // ── Indices ────────────────────────────────────────────────────────────────
    function showHint(text) {
        const li = document.createElement('li');
        li.className = 'hint-item hint-appear';
        li.textContent = text;
        $hintsList.appendChild(li);
        $hintsArea.classList.remove('hidden');
    }

    // ── Soumission ─────────────────────────────────────────────────────────────
    // Supprime les diacritiques (accents) de façon fiable (Unicode NFD)
    function normalize(str) {
        return str.trim().toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, ''); // plage unicode officielle des diacritiques
    }

    function submitGuess(guess) {
        if (state.gameOver || !guess.trim()) return;

        const input    = normalize(guess);
        const answerEn = normalize(state.pokemon.en);
        const answerFr = normalize(state.pokemon.fr);

        if (input === answerEn || input === answerFr) {
            endGame(true);
        } else {
            state.wrongGuesses.push(guess.trim());
            appendWrongGuess(guess.trim());
            state.attempts++;
            applyZoom(state.attempts);
            renderDots();

            if (state.attempts >= MAX_ATTEMPTS) {
                endGame(false);
            } else {
                $badge.textContent = `Tentative ${state.attempts + 1} / ${MAX_ATTEMPTS}`;
                const hints = [
                    `Type TCG : ${state.types.map(t => TYPE_FR[t] || t).join(' / ') || '?'}`,
                    'Génération : I (Kanto)',
                    `Première lettre : ${(state.pokemon.fr[0] || '?').toUpperCase()}`,
                ];
                const hi = state.attempts - 3;
                if (hi >= 0 && hi < hints.length) showHint(hints[hi]);
            }
        }

        $input.value = '';
        closeAC();
    }

    function endGame(win) {
        state.gameOver = true;
        applyZoom(STAGES.length); // révèle l'image complète

        if (state.rarity) {
            $rarityBadge.textContent = state.rarity;
            $rarityBadge.classList.remove('hidden');
        }

        if (win) {
            streak++;
            const dot = $dots.querySelectorAll('.dot')[state.attempts];
            if (dot) dot.classList.add('correct');
            $resultMsg.innerHTML = `<span class="result-win">Bravo ! C'était <strong>${state.pokemon.fr}</strong> !</span>`;
        } else {
            streak = 0;
            $resultMsg.innerHTML = `<span class="result-lose">Dommage ! C'était <strong>${state.pokemon.fr}</strong> (#${state.pokemon.id})</span>`;
        }

        updateStreak();
        $resultArea.classList.remove('hidden');
        [$input, $submit, $skip].forEach(el => el.disabled = true);
    }

    function updateStreak() {
        if (streak >= 2) {
            $streakCount.textContent = streak;
            $streakEl.classList.remove('hidden');
        } else {
            $streakEl.classList.add('hidden');
        }
    }

    function appendWrongGuess(label) {
        const span = document.createElement('span');
        span.className = 'wrong-guess-item';
        span.textContent = label;
        $wrongList.appendChild(span);
    }

    // ── Autocomplétion ─────────────────────────────────────────────────────────
    let acMatches = [];

    function closeAC() {
        $ac.classList.add('hidden');
        acIndex = -1;
    }

    function renderAC(matches) {
        acMatches = matches;
        acIndex = -1;
        $ac.innerHTML = '';
        matches.forEach((p, i) => {
            const div = document.createElement('div');
            div.className = 'autocomplete-item';
            div.setAttribute('role', 'option');
            div.textContent = p.fr;
            div.addEventListener('mousedown', e => {
                e.preventDefault();
                $input.value = p.fr;
                closeAC();
                submitGuess(p.fr);
            });
            $ac.appendChild(div);
        });
        $ac.classList.remove('hidden');
    }

    function highlightAC(newIdx) {
        const items = $ac.querySelectorAll('.autocomplete-item');
        items.forEach(el => el.classList.remove('ac-active'));
        acIndex = Math.max(-1, Math.min(newIdx, items.length - 1));
        if (acIndex >= 0) {
            items[acIndex].classList.add('ac-active');
            $input.value = acMatches[acIndex].fr;
        }
    }

    $input.addEventListener('input', function () {
        const val = normalize(this.value);
        if (val.length < 1) { closeAC(); return; }

        const matches = POKEMON_GEN1.filter(p =>
            normalize(p.fr).startsWith(val) || p.en.startsWith(val)
        ).slice(0, 8);

        if (!matches.length) { closeAC(); return; }
        renderAC(matches);
    });

    $input.addEventListener('keydown', e => {
        if ($ac.classList.contains('hidden')) {
            if (e.key === 'Enter') submitGuess($input.value);
            return;
        }
        if (e.key === 'ArrowDown')  { e.preventDefault(); highlightAC(acIndex + 1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); highlightAC(acIndex - 1); }
        else if (e.key === 'Enter')   { e.preventDefault(); closeAC(); submitGuess($input.value); }
        else if (e.key === 'Escape')  { closeAC(); }
    });

    $input.addEventListener('blur', () => setTimeout(closeAC, 150));

    $submit.addEventListener('click',  () => submitGuess($input.value));
    $skip.addEventListener('click',    () => { if (!state.gameOver) endGame(false); });
    $('new-game-btn').addEventListener('click', initGame);
    $('retry-btn').addEventListener('click',    initGame);

    initGame();
    </script>
</body>
</html>
