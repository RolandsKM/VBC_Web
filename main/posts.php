<?php
include '../css/templates/header.php'; 
$categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$city = isset($_GET['city']) ? $_GET['city'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs</title>
  
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="../functions/script.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="../css/style-main.css">
    <style>
       #filter_category option {
  background-color: transparent !important;
  color: inherit;
}

    </style>
</head>
<body style="background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);  overflow-x: hidden;">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const categoryId = "<?= $categoryId ?>";
</script>

    <div class=" mt-4">
        <div class="row">
            <div class="content-wrapper d-flex">
     
                <button id="toggle_filters" class="d-md-none">
                    ☰ Filtri
                </button>


                <aside id="sidebar" class="collapsed">
                    <button id="close_sidebar" class="close-btn d-md-none" aria-label="Close sidebar">✕</button>

                    <h4>Filtrēšana</h4>
                    
                    <form id="filter_form">

                    <div class="mb-3">
                        <label for="filter_category" class="form-label">Kategorija</label>
                        <select class="form-select" id="filter_category">
                            <option value="">Izvēlies kategoriju...</option>
                            
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Datums no:</label>
                        <input type="date" class="form-control" id="date_from" value="<?= $dateFrom ?>">
                        <label for="form-label">Lidz:</label>
                        <input type="date" class="form-control mt-2" id="date_to" value="<?= $dateTo ?>">
                    </div>
                    
                        <div class="mb-3">
                            <label for="city" class="form-label">Pilsēta</label>
                            <select class="form-select" id="city">
                                <option value="">Izvēlies pilsētu...</option>
                                <option value="ainazi" <?= $city == 'ainazi' ? 'selected' : '' ?>>Ainaži</option>
                                <option value="aizkraukle" <?= $city == 'aizkraukle' ? 'selected' : '' ?>>Aizkraukle</option>
                                <option value="aizpute" <?= $city == 'aizpute' ? 'selected' : '' ?>>Aizpute</option>
                                <option value="akniste" <?= $city == 'akniste' ? 'selected' : '' ?>>Aknīste</option>
                                <option value="aloja" <?= $city == 'aloja' ? 'selected' : '' ?>>Aloja</option>
                                <option value="aluksne" <?= $city == 'aluksne' ? 'selected' : '' ?>>Alūksne</option>
                                <option value="ape" <?= $city == 'ape' ? 'selected' : '' ?>>Ape</option>
                                <option value="auce" <?= $city == 'auce' ? 'selected' : '' ?>>Auce</option>
                                <option value="baldone" <?= $city == 'baldone' ? 'selected' : '' ?>>Baldone</option>
                                <option value="balozi" <?= $city == 'balozi' ? 'selected' : '' ?>>Baloži</option>
                                <option value="balvi" <?= $city == 'balvi' ? 'selected' : '' ?>>Balvi</option>
                                <option value="bauska" <?= $city == 'bauska' ? 'selected' : '' ?>>Bauska</option>
                                <option value="broceni" <?= $city == 'broceni' ? 'selected' : '' ?>>Brocēni</option>
                                <option value="cesis" <?= $city == 'cesis' ? 'selected' : '' ?>>Cēsis</option>
                                <option value="cesvaine" <?= $city == 'cesvaine' ? 'selected' : '' ?>>Cesvaine</option>
                                <option value="dagda" <?= $city == 'dagda' ? 'selected' : '' ?>>Dagda</option>
                                <option value="daugavpils" <?= $city == 'daugavpils' ? 'selected' : '' ?>>Daugavpils</option>
                                <option value="dobele" <?= $city == 'dobele' ? 'selected' : '' ?>>Dobele</option>
                                <option value="durbe" <?= $city == 'durbe' ? 'selected' : '' ?>>Durbe</option>
                                <option value="grobina" <?= $city == 'grobina' ? 'selected' : '' ?>>Grobiņa</option>
                                <option value="gulbene" <?= $city == 'gulbene' ? 'selected' : '' ?>>Gulbene</option>
                                <option value="ikskile" <?= $city == 'ikskile' ? 'selected' : '' ?>>Ikšķile</option>
                                <option value="ilukste" <?= $city == 'ilukste' ? 'selected' : '' ?>>Ilūkste</option>
                                <option value="jaunjelgava" <?= $city == 'jaunjelgava' ? 'selected' : '' ?>>Jaunjelgava</option>
                                <option value="jekabpils" <?= $city == 'jekabpils' ? 'selected' : '' ?>>Jēkabpils</option>
                                <option value="jelgava" <?= $city == 'jelgava' ? 'selected' : '' ?>>Jelgava</option>
                                <option value="jurmala" <?= $city == 'jurmala' ? 'selected' : '' ?>>Jūrmala</option>
                                <option value="kandava" <?= $city == 'kandava' ? 'selected' : '' ?>>Kandava</option>
                                <option value="karsava" <?= $city == 'karsava' ? 'selected' : '' ?>>Kārsava</option>
                                <option value="kegums" <?= $city == 'kegums' ? 'selected' : '' ?>>Ķegums</option>
                                <option value="kraslava" <?= $city == 'kraslava' ? 'selected' : '' ?>>Krāslava</option>
                                <option value="kuldiga" <?= $city == 'kuldiga' ? 'selected' : '' ?>>Kuldīga</option>
                                <option value="lielvarde" <?= $city == 'lielvarde' ? 'selected' : '' ?>>Lielvārde</option>
                                <option value="liepaja" <?= $city == 'liepaja' ? 'selected' : '' ?>>Liepāja</option>
                                <option value="ligatne" <?= $city == 'ligatne' ? 'selected' : '' ?>>Līgatne</option>
                                <option value="limbazi" <?= $city == 'limbazi' ? 'selected' : '' ?>>Limbaži</option>
                                <option value="livani" <?= $city == 'livani' ? 'selected' : '' ?>>Līvāni</option>
                                <option value="lubana" <?= $city == 'lubana' ? 'selected' : '' ?>>Lubāna</option>
                                <option value="ludza" <?= $city == 'ludza' ? 'selected' : '' ?>>Ludza</option>
                                <option value="madona" <?= $city == 'madona' ? 'selected' : '' ?>>Madona</option>
                                <option value="mazsalaca" <?= $city == 'mazsalaca' ? 'selected' : '' ?>>Mazsalaca</option>
                                <option value="ogre" <?= $city == 'ogre' ? 'selected' : '' ?>>Ogre</option>
                                <option value="olaine" <?= $city == 'olaine' ? 'selected' : '' ?>>Olaine</option>
                                <option value="pavilosta" <?= $city == 'pavilosta' ? 'selected' : '' ?>>Pāvilosta</option>
                                <option value="piltene" <?= $city == 'piltene' ? 'selected' : '' ?>>Piltene</option>
                                <option value="plavinas" <?= $city == 'plavinas' ? 'selected' : '' ?>>Pļaviņas</option>
                                <option value="preili" <?= $city == 'preili' ? 'selected' : '' ?>>Preiļi</option>
                                <option value="priekule" <?= $city == 'priekule' ? 'selected' : '' ?>>Priekule</option>
                                <option value="rezekne" <?= $city == 'rezekne' ? 'selected' : '' ?>>Rēzekne</option>
                                <option value="riga" <?= $city == 'riga' ? 'selected' : '' ?>>Rīga</option>
                                <option value="rujiena" <?= $city == 'rujiena' ? 'selected' : '' ?>>Rūjiena</option>
                                <option value="sabile" <?= $city == 'sabile' ? 'selected' : '' ?>>Sabile</option>
                                <option value="salacgriva" <?= $city == 'salacgriva' ? 'selected' : '' ?>>Salacgrīva</option>
                                <option value="salaspils" <?= $city == 'salaspils' ? 'selected' : '' ?>>Salaspils</option>
                                <option value="saldus" <?= $city == 'saldus' ? 'selected' : '' ?>>Saldus</option>
                                <option value="saulkrasti" <?= $city == 'saulkrasti' ? 'selected' : '' ?>>Saulkrasti</option>
                                <option value="seda" <?= $city == 'seda' ? 'selected' : '' ?>>Seda</option>
                                <option value="sigulda" <?= $city == 'sigulda' ? 'selected' : '' ?>>Sigulda</option>
                                <option value="skrunda" <?= $city == 'skrunda' ? 'selected' : '' ?>>Skrunda</option>
                                <option value="smiltene" <?= $city == 'smiltene' ? 'selected' : '' ?>>Smiltene</option>
                                <option value="staicele" <?= $city == 'staicele' ? 'selected' : '' ?>>Staicele</option>
                                <option value="stende" <?= $city == 'stende' ? 'selected' : '' ?>>Stende</option>
                                <option value="strenci" <?= $city == 'strenci' ? 'selected' : '' ?>>Strenči</option>
                                <option value="subate" <?= $city == 'subate' ? 'selected' : '' ?>>Subate</option>
                                <option value="talsi" <?= $city == 'talsi' ? 'selected' : '' ?>>Talsi</option>
                                <option value="tukums" <?= $city == 'tukums' ? 'selected' : '' ?>>Tukums</option>
                                <option value="valdemarpils" <?= $city == 'valdemarpils' ? 'selected' : '' ?>>Valdemārpils</option>
                                <option value="valka" <?= $city == 'valka' ? 'selected' : '' ?>>Valka</option>
                                <option value="valmiera" <?= $city == 'valmiera' ? 'selected' : '' ?>>Valmiera</option>
                                <option value="vangazi" <?= $city == 'vangazi' ? 'selected' : '' ?>>Vangaži</option>
                                <option value="varaklani" <?= $city == 'varaklani' ? 'selected' : '' ?>>Varakļāni</option>
                                <option value="ventspils" <?= $city == 'ventspils' ? 'selected' : '' ?>>Ventspils</option>
                                <option value="viesite" <?= $city == 'viesite' ? 'selected' : '' ?>>Viesīte</option>
                                <option value="vilaka" <?= $city == 'vilaka' ? 'selected' : '' ?>>Viļaka</option>
                                <option value="vilani" <?= $city == 'vilani' ? 'selected' : '' ?>>Viļāni</option>
                                <option value="zilupe" <?= $city == 'zilupe' ? 'selected' : '' ?>>Zilupe</option>

                            </select>
                        </div>

                            <div class="d-flex gap-2">
                            
                                <button type="button" id="clear_filters" class="btn w-100">Notīrīt filtrus</button>
                            </div>
                    </form>
                </aside>
       
           


                <main class="col-md-9">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="create-btn">
                        <a href="../user/create.php" class="btn" id="left_button" type="button">Izveidot</a>
                    </div>
                    <div class="input-wrapper" style="flex-grow: 1; margin-left: 20px;">
                        <input type="text" class="form" id="search_input" placeholder="Meklēt pasākumus pēc nosaukuma vai apraksta...">
                    </div>
                </div>


                    <section id="events">
                        <p>Izvēlieties kategoriju, lai redzētu pasākumus.</p>
                    </section>
                    <div class="text-center mt-3" id="load_more_container" style="display: none;">
                        <button id="load_more" class="btn">Ielādēt vairāk</button>
                    </div>
                </main>
            </div>

        </div>
    </div>
<script>
let currentOffset = 0;
const eventsPerPage = 12;

document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.getElementById("sidebar");
    const toggleButton = document.getElementById("toggle_filters");
    const closeButton = document.getElementById("close_sidebar");
    const loadMoreBtn = document.getElementById("load_more");
    const loadMoreContainer = document.getElementById("load_more_container");

    function handleToggle() {
        sidebar.classList.toggle("active");
        toggleButton.style.display = sidebar.classList.contains("active") ? "none" : "block";
    }

    function handleResize() {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove("active");
            toggleButton.style.display = "block";
        } else {
            sidebar.classList.add("active");
            toggleButton.style.display = "none";
        }
    }

    function loadEvents(reset = false) {
        if (reset) {
            currentOffset = 0;
            document.getElementById('events').innerHTML = '';
        }

        const categoryId = document.getElementById('filter_category').value;
        const search = document.getElementById('search_input').value;
        const city = document.getElementById('city').value;
        const dateFrom = document.getElementById('date_from').value;
        const dateTo = document.getElementById('date_to').value;

        fetch(`../functions/get_events_by_category.php?category_id=${categoryId}&search=${search}&city=${city}&date_from=${dateFrom}&date_to=${dateTo}&limit=${eventsPerPage}&offset=${currentOffset}`)
            .then(response => response.text())
            .then(data => {
                if (data.includes('Nav atrastu pasākumu')) {
                    if (currentOffset === 0) {
                        document.getElementById('events').innerHTML = data;
                    }
                    loadMoreContainer.style.display = 'none';
                } else {
                    if (currentOffset === 0) {
                        document.getElementById('events').innerHTML = data;
                    } else {
                        document.getElementById('events').insertAdjacentHTML('beforeend', data);
                    }
                    
                    // Count the number of events in the response
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data;
                    const eventCount = tempDiv.querySelectorAll('.event').length;
                    
                    // Only show load more button if we got the full number of events
                    if (eventCount === eventsPerPage) {
                        loadMoreContainer.style.display = 'block';
                        currentOffset += eventsPerPage;
                    } else {
                        loadMoreContainer.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                loadMoreContainer.style.display = 'none';
            });
    }

    // Event listeners for filters
    document.getElementById('filter_category').addEventListener('change', () => loadEvents(true));
    document.getElementById('search_input').addEventListener('input', () => loadEvents(true));
    document.getElementById('city').addEventListener('change', () => loadEvents(true));
    document.getElementById('date_from').addEventListener('change', () => loadEvents(true));
    document.getElementById('date_to').addEventListener('change', () => loadEvents(true));
    document.getElementById('clear_filters').addEventListener('click', () => {
        document.getElementById('filter_form').reset();
        loadEvents(true);
    });

    // Load more button click handler
    loadMoreBtn.addEventListener('click', () => loadEvents());

    // Initial load
    loadEvents(true);

    toggleButton.addEventListener("click", handleToggle);
    closeButton.addEventListener("click", handleToggle);

    handleResize();
    window.addEventListener("resize", handleResize);
});
</script>
<?php include '../css/templates/footer.php'; ?>


</body>
</html>
