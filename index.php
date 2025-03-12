<?php
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=reppop', 'root', 'Deflagratione89');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupération des informations des personnes
$copains = $pdo->query("SELECT * FROM personnes")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reppop</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" />

    <!-- Leaflet MarkerCluster CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />

    <!-- Cropper.js CSS -->
    <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.css" />

    <!-- Ton fichier CSS perso -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Barre de navigation -->
    <div class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Reppop</a>
            <!-- Bouton "Annuaire" -->
            <button onclick="window.location.href='annuaire.php'" class="btn">Annuaire</button>
            <!-- Bouton "M'ajouter" -->
            <button id="addButton" class="btn ml-2">M'ajouter</button>
        </div>
    </div>

    <!-- Carte Leaflet -->
    <div id="map"></div>

    <!-- Overlay -->
    <div id="overlay"></div>

    <!-- Popup pour ajouter une personne -->
    <div id="addModal">
        <form id="addPersonForm" action="save_person.php" method="post" enctype="multipart/form-data">
            <!-- Prénom / Nom -->
            <div class="form-group">
                <label for="prenom">Prénom:</label>
                <input type="text" class="form-control" id="prenom" name="prenom" required>
            </div>
            <div class="form-group">
                <label for="nom">Nom:</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>

            <!-- Coordonnées de contact -->
            <div class="form-group">
                <label for="mail">Mail:</label>
                <input type="email" class="form-control" id="mail" name="mail">
            </div>
            <div class="form-group">
                <label for="tel">Téléphone:</label>
                <input type="text" class="form-control" id="tel" name="tel">
            </div>

            <!-- Code Postal + liste des villes + lat/lon cachés -->
            <div class="form-group">
                <label for="codePostal">Code Postal:</label>
                <input type="text" class="form-control" id="codePostal" name="code_postal">
            </div>
            <div class="form-group">
                <label for="citySelect">Ville:</label>
                <select class="form-control" id="citySelect" name="ville">
                    <!-- Options chargées en AJAX -->
                </select>
            </div>
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <!-- Poste / Spécialité -->
            <div class="form-group">
                <label for="intitule_poste">Intitulé du poste:</label>
                <input type="text" class="form-control" id="intitule_poste" name="intitule_poste">
            </div>
            <div class="form-group">
                <label for="specialite">Spécialité:</label>
                <input type="text" class="form-control" id="specialite" name="specialite">
            </div>

            <!-- Temps de travail -->
            <div class="form-group">
                <label for="temps_travail">Temps de travail (ex: "Plein temps"):</label>
                <input type="text" class="form-control" id="temps_travail" name="temps_travail">
            </div>

            <!-- Jours travaillés -->
            <div class="form-group">
                <label>Jours travaillés (par demi-journée) :</label><br>
                <!-- Lundi -->
                <div><label><input type="checkbox" name="jours_travailles[]" value="lundi_matin"> Lundi matin</label>
                </div>
                <div><label><input type="checkbox" name="jours_travailles[]" value="lundi_aprem"> Lundi
                        après-midi</label></div>
                <!-- Mardi -->
                <div><label><input type="checkbox" name="jours_travailles[]" value="mardi_matin"> Mardi matin</label>
                </div>
                <div><label><input type="checkbox" name="jours_travailles[]" value="mardi_aprem"> Mardi
                        après-midi</label></div>
                <!-- Mercredi -->
                <div><label><input type="checkbox" name="jours_travailles[]" value="mercredi_matin"> Mercredi
                        matin</label></div>
                <div><label><input type="checkbox" name="jours_travailles[]" value="mercredi_aprem"> Mercredi
                        après-midi</label></div>
                <!-- Jeudi -->
                <div><label><input type="checkbox" name="jours_travailles[]" value="jeudi_matin"> Jeudi matin</label>
                </div>
                <div><label><input type="checkbox" name="jours_travailles[]" value="jeudi_aprem"> Jeudi
                        après-midi</label></div>
                <!-- Vendredi -->
                <div><label><input type="checkbox" name="jours_travailles[]" value="vendredi_matin"> Vendredi
                        matin</label></div>
                <div><label><input type="checkbox" name="jours_travailles[]" value="vendredi_aprem"> Vendredi
                        après-midi</label></div>
                <!-- Samedi -->
                <div><label><input type="checkbox" name="jours_travailles[]" value="samedi_matin"> Samedi matin</label>
                </div>
                <div><label><input type="checkbox" name="jours_travailles[]" value="samedi_aprem"> Samedi
                        après-midi</label></div>
                <!-- Dimanche -->
                <div><label><input type="checkbox" name="jours_travailles[]" value="dimanche_matin"> Dimanche
                        matin</label></div>
                <div><label><input type="checkbox" name="jours_travailles[]" value="dimanche_aprem"> Dimanche
                        après-midi</label></div>
            </div>

            <!-- Description du poste -->
            <div class="form-group">
                <label for="description_poste">Description du poste:</label>
                <textarea class="form-control" id="description_poste" name="description_poste"></textarea>
            </div>

            <!-- Autres casquettes -->
            <div class="form-group">
                <label for="autres_casquettes">Autres casquettes métiers:</label>
                <textarea class="form-control" id="autres_casquettes" name="autres_casquettes"></textarea>
            </div>

            <!-- Ancien champ "description" -->
            <div class="form-group">
                <label for="description">Autre description (si besoin) :</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>

            <!-- Photo : input + Crop + boutons -->
            <div class="form-group">
                <label for="photo">Photo (recadrage 600x600):</label>
                <!-- Bouton stylé -->
                <label id="photoLabel" for="photo">
                    <span class="glyphicon glyphicon-camera" aria-hidden="true"></span> Sélectionner une photo
                </label>
                <input type="file" id="photo" name="photo" accept="image/*">

                <!-- Image pour le crop -->
                <img id="cropImage" />

                <!-- Boutons d'action -->
                <button type="button" id="cropButton" class="btn btn-secondary">Recadrer</button>
                <button type="button" id="deleteCroppedButton" class="btn btn-danger">Supprimer</button>

                <!-- Champ caché pour stocker l'image recadrée en base64 -->
                <input type="hidden" id="croppedImage" name="croppedImage">
            </div>

            <button type="submit" class="btn btn-primary btn-block"
                style="background-color: #066b6b; border-color: #066b6b;">VALIDER</button>
        </form>
    </div>

    <!-- Popup de détail de la personne -->
    <div id="personDetailModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="personName"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="personPhoto" src="" alt="Photo de la personne">
                    <p id="personMail"></p>
                    <p id="personTel"></p>
                    <p id="personCPVille"></p>
                    <p id="personIntitulePoste"></p>
                    <p id="personSpecialite"></p>
                    <p id="personTempsTravail"></p>
                    <p id="personJoursTravailles"></p>
                    <p id="personDescriptionPoste"></p>
                    <p id="personAutresCasquettes"></p>
                    <p id="personDescription"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charger Leaflet JS en premier -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>

    <!-- MarkerCluster JS (APRÈS Leaflet) -->
    <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>

    <!-- jQuery + Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Cropper.js -->
    <script src="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.js"></script>

    <script>
    // Initialisation de la carte
    var map = L.map('map').setView([46.603354, 1.888334], 6); // Centre de la France
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // MarkerCluster Group
    var markersCluster = L.markerClusterGroup();

    // Mapping pour afficher les jours
    const daysMap = {
        'lundi_matin': 'Lundi Matin',
        'lundi_aprem': 'Lundi Après-midi',
        'mardi_matin': 'Mardi Matin',
        'mardi_aprem': 'Mardi Après-midi',
        'mercredi_matin': 'Mercredi Matin',
        'mercredi_aprem': 'Mercredi Après-midi',
        'jeudi_matin': 'Jeudi Matin',
        'jeudi_aprem': 'Jeudi Après-midi',
        'vendredi_matin': 'Vendredi Matin',
        'vendredi_aprem': 'Vendredi Après-midi',
        'samedi_matin': 'Samedi Matin',
        'samedi_aprem': 'Samedi Après-midi',
        'dimanche_matin': 'Dimanche Matin',
        'dimanche_aprem': 'Dimanche Après-midi'
    };

    // Données PHP => JS
    var copains = <?php echo json_encode($copains); ?>;

    copains.forEach(function(copain) {
        // Icon perso (photo ronde)
        var avatarIcon = L.divIcon({
            html: `<div style="background-image: url('photos/${copain.photo}'); 
                              background-size: cover; 
                              width: 40px; 
                              height: 40px; 
                              border-radius: 50%; 
                              border: 2px solid #066b6b;"></div>`,
            className: '',
            iconSize: [40, 40],
            popupAnchor: [0, -20],
        });

        // Création d'un marqueur
        var marker = L.marker([copain.latitude, copain.longitude], {
            icon: avatarIcon
        });

        // Au clic => modal détail
        marker.on('click', function() {
            $('#personName').text(copain.prenom + ' ' + copain.nom);
            $('#personPhoto').attr('src', 'photos/' + copain.photo);

            $('#personMail').html(copain.mail ?
                `<span class="info-label">Mail :</span> ${copain.mail}` : '');
            $('#personTel').html(copain.tel ? `<span class="info-label">Tel :</span> ${copain.tel}` :
                '');

            let cpVille = '';
            if (copain.code_postal) cpVille += copain.code_postal + ' ';
            if (copain.ville) cpVille += copain.ville;
            $('#personCPVille').html(cpVille ? `<span class="info-label">Ville :</span> ${cpVille}` :
                '');

            $('#personIntitulePoste').html(copain.intitule_poste ?
                `<span class="info-label">Poste :</span> ${copain.intitule_poste}` : '');
            $('#personSpecialite').html(copain.specialite ?
                `<span class="info-label">Spécialité :</span> ${copain.specialite}` : '');
            $('#personTempsTravail').html(copain.temps_travail ?
                `<span class="info-label">Temps de travail :</span> ${copain.temps_travail}` : '');

            let jt = copain.jours_travailles ? copain.jours_travailles.split(',') : [];
            let mapped = jt.map(j => daysMap[j] || j);
            let jtStr = mapped.join(' | ');
            $('#personJoursTravailles').html(jtStr ?
                `<span class="info-label">Jours travaillés :</span> ${jtStr}` : '');

            $('#personDescriptionPoste').html(copain.description_poste ?
                `<span class="info-label">Description poste :</span> ${copain.description_poste}` :
                '');
            $('#personAutresCasquettes').html(copain.autres_casquettes ?
                `<span class="info-label">Autres casquettes :</span> ${copain.autres_casquettes}` :
                '');
            $('#personDescription').html(copain.description ?
                `<span class="info-label">Description :</span> ${copain.description}` : '');

            $('#personDetailModal').modal('show');
        });

        // Ajout au cluster
        markersCluster.addLayer(marker);
    });

    // Ajout du cluster sur la carte
    map.addLayer(markersCluster);

    // Gérer le bouton "M'ajouter" => afficher la popup
    $('#addButton').on('click', function() {
        $('#overlay').show();
        $('#addModal').show();
    });
    // Clique sur overlay => on ferme le popup
    $('#overlay').on('click', function() {
        $('#addModal').hide();
        $('#overlay').hide();
    });

    // Sur codePostal => on charge la liste des villes
    $('#codePostal').on('blur', function() {
        let cp = $(this).val().trim();
        if (!cp) return;

        $.getJSON(
            `https://geo.api.gouv.fr/communes?codePostal=${cp}&fields=nom,centre&format=json`,
            function(data) {
                $('#citySelect').empty();
                if (!data || data.length === 0) {
                    $('#citySelect').append(`<option value="">Aucune commune trouvée</option>`);
                    $('#latitude').val('');
                    $('#longitude').val('');
                } else {
                    data.forEach(function(commune) {
                        let lon = commune.centre.coordinates[0];
                        let lat = commune.centre.coordinates[1];
                        $('#citySelect').append(`
                            <option value="${commune.nom}" data-lat="${lat}" data-lon="${lon}">
                                ${commune.nom}
                            </option>
                        `);
                    });
                    // Sélection auto de la première
                    $('#citySelect').trigger('change');
                }
            }
        );
    });

    // Sur changement de ville => maj lat/long
    $('#citySelect').on('change', function() {
        let optionSelected = $(this).find('option:selected');
        if (!optionSelected.val()) {
            $('#latitude').val('');
            $('#longitude').val('');
            return;
        }
        $('#latitude').val(optionSelected.data('lat'));
        $('#longitude').val(optionSelected.data('lon'));
    });

    // Gestion du crop (Cropper.js)
    let cropper;
    let photoInput = document.getElementById('photo');
    let cropImage = document.getElementById('cropImage');
    let cropButton = document.getElementById('cropButton');
    let deleteButton = document.getElementById('deleteCroppedButton');
    let croppedImageInput = document.getElementById('croppedImage');

    function resetPhotoState() {
        photoInput.value = '';
        cropImage.src = '';
        cropImage.style.display = 'none';
        cropButton.style.display = 'none';
        deleteButton.style.display = 'none';
        croppedImageInput.value = '';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }
    resetPhotoState();

    // Au choix de fichier => init cropper
    photoInput.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const reader = new FileReader();
            reader.onload = function(evt) {
                cropImage.src = evt.target.result;
                cropImage.style.display = 'block';
                cropButton.style.display = 'inline-block';

                if (cropper) cropper.destroy();
                cropper = new Cropper(cropImage, {
                    aspectRatio: 1,
                    viewMode: 1
                });
            };
            reader.readAsDataURL(files[0]);
        }
    });

    // Bouton "Recadrer"
    cropButton.addEventListener('click', function() {
        if (cropper) {
            let canvas = cropper.getCroppedCanvas({
                width: 600,
                height: 600
            });
            let base64data = canvas.toDataURL('image/jpeg', 0.9);

            croppedImageInput.value = base64data;
            cropImage.src = base64data;
            cropper.destroy();
            cropper = null;

            cropButton.style.display = 'none';
            deleteButton.style.display = 'inline-block';
        }
    });

    // Bouton "Supprimer"
    deleteButton.addEventListener('click', function() {
        resetPhotoState();
    });

    // Ouvrir la popup si ?action=add
    <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('overlay').style.display = 'block';
        document.getElementById('addModal').style.display = 'block';
    });
    <?php endif; ?>
    </script>
</body>

</html>