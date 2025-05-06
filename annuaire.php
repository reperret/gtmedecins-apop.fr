<?php
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=reppop', 'root', 'Deflagratione89!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupération des personnes
$stmt = $pdo->query("SELECT * FROM personnes");
$personnes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// On prépare un mapping pour le mini-calendrier
// (tu peux l'adapter si besoin)
$daysMap = [
    'lundi_matin'    => ['row' => 'morning',   'col' => 'lun'],
    'lundi_aprem'    => ['row' => 'afternoon', 'col' => 'lun'],
    'mardi_matin'    => ['row' => 'morning',   'col' => 'mar'],
    'mardi_aprem'    => ['row' => 'afternoon', 'col' => 'mar'],
    'mercredi_matin' => ['row' => 'morning',   'col' => 'mer'],
    'mercredi_aprem' => ['row' => 'afternoon', 'col' => 'mer'],
    'jeudi_matin'    => ['row' => 'morning',   'col' => 'jeu'],
    'jeudi_aprem'    => ['row' => 'afternoon', 'col' => 'jeu'],
    'vendredi_matin' => ['row' => 'morning',   'col' => 'ven'],
    'vendredi_aprem' => ['row' => 'afternoon', 'col' => 'ven'],
    'samedi_matin'   => ['row' => 'morning',   'col' => 'sam'],
    'samedi_aprem'   => ['row' => 'afternoon', 'col' => 'sam'],
    'dimanche_matin' => ['row' => 'morning',   'col' => 'dim'],
    'dimanche_aprem' => ['row' => 'afternoon', 'col' => 'dim'],
];

// Helper : couper un texte à 10 caractères + “...”
function snippet($text)
{
    $text = trim($text);
    if (mb_strlen($text) <= 10) {
        return $text;
    }
    return mb_substr($text, 0, 10) . '...';
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Annuaire Reppop</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS + Responsive plugin CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css" />

    <link rel="stylesheet" href="style.css">

</head>

<body>
    <!-- Barre de navigation -->
    <div class="navbar navbar-expand-lg navbar-dark">
        <!-- Dans la navbar de annuaire.php -->
        <div class="container">
            <a class="navbar-brand" href="#"> Reppop</a>

            <!-- Bouton "Carte" -->
            <button onclick="window.location.href='index.php'" class="btn">Carte</button>

            <!-- Bouton "M'ajouter" : on renvoie vers index.php?action=add -->
            <button onclick="window.location.href='index.php?action=add'" class="btn ml-2">M'ajouter</button>
        </div>

    </div>

    <div class="container mt-4">
        <h3>Annuaire Reppop</h3>
        <hr>

        <!-- Tableau DataTables (responsive) -->
        <table id="myTable" class="table table-bordered table-striped table-sm display responsive nowrap">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Mail</th>
                    <th>Tel</th>
                    <th>Code postal</th>
                    <th>Ville</th>
                    <th>Intitulé poste</th>
                    <th>Spécialité</th>
                    <th>Temps travail</th>
                    <th>Jours</th>
                    <th>Description poste</th>
                    <th>Autres casquettes</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($personnes as $p): ?>
                    <?php
                    $joursCSV    = $p['jours_travailles'] ?? '';
                    $descPoste   = trim($p['description_poste'] ?? '');
                    $autres      = trim($p['autres_casquettes'] ?? '');
                    $desc        = trim($p['description'] ?? '');
                    ?>
                    <tr>
                        <!-- Photo ronde -->
                        <td>
                            <?php
                            $photo = !empty($p['photo']) ? htmlspecialchars($p['photo']) : 'avatar.jpg';
                            ?>
                            <img src="photos/<?php echo $photo; ?>" class="avatar-img" alt="avatar"
                                onclick="showPhotoModal('photos/<?php echo $photo; ?>')">
                        </td>

                        <td><?php echo htmlspecialchars($p['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($p['nom']); ?></td>
                        <td><?php echo htmlspecialchars($p['mail']); ?></td>
                        <td><?php echo htmlspecialchars($p['tel']); ?></td>
                        <td><?php echo htmlspecialchars($p['code_postal']); ?></td>
                        <td><?php echo htmlspecialchars($p['ville']); ?></td>
                        <td><?php echo htmlspecialchars($p['intitule_poste']); ?></td>
                        <td><?php echo htmlspecialchars($p['specialite']); ?></td>
                        <td><?php echo htmlspecialchars($p['temps_travail']); ?></td>

                        <!-- Jours travaillés : mini bouton "Planning" -->
                        <td>
                            <?php if (!empty($joursCSV)): ?>
                                <button class="btn-grey" onclick="showPlanning('<?php echo addslashes($joursCSV); ?>')">
                                    Planning
                                </button>
                            <?php endif; ?>
                        </td>

                        <!-- Description poste : snippet + "Voir +" -->
                        <td>
                            <?php
                            echo htmlspecialchars(snippet($descPoste));
                            if (mb_strlen($descPoste) > 10) {
                                echo ' <button class="btn-grey" onclick="showDetail(\'Description poste\', \'' . addslashes($descPoste) . '\')">Voir +</button>';
                            }
                            ?>
                        </td>
                        <!-- Autres casquettes : snippet + "Voir +" -->
                        <td>
                            <?php
                            echo htmlspecialchars(snippet($autres));
                            if (mb_strlen($autres) > 10) {
                                echo ' <button class="btn-grey" onclick="showDetail(\'Autres casquettes\', \'' . addslashes($autres) . '\')">Voir +</button>';
                            }
                            ?>
                        </td>
                        <!-- Description : snippet + "Voir +" -->
                        <td>
                            <?php
                            echo htmlspecialchars(snippet($desc));
                            if (mb_strlen($desc) > 10) {
                                echo ' <button class="btn-grey" onclick="showDetail(\'Description\', \'' . addslashes($desc) . '\')">Voir +</button>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal pour afficher la photo en grand -->
    <div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoModalLabel">Photo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="photoModalImg" src="" alt="photo" style="width: 100%; height: auto; border-radius: 5px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal "Detail" pour descriptions -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="detailModalContent"></div>
            </div>
        </div>
    </div>

    <!-- Modal "Planning" pour jours travaillés (mini-calendrier) -->
    <div class="modal fade" id="planningModal" tabindex="-1" role="dialog" aria-labelledby="planningModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="planningModalLabel">Jours travaillés</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="planningModalContent"></div>
            </div>
        </div>
    </div>

    <!-- jQuery + Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- DataTables JS + Responsive plugin JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <!-- DataTables en français -->
    <script src="https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"></script>

    <script>
        $(document).ready(function() {
            // Initialisation DataTables en mode responsive
            $('#myTable').DataTable({
                pageLength: 50,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Tout"]
                ],
                responsive: true,
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                }
            });
        });

        // Fonction pour afficher la photo en grand
        function showPhotoModal(imgPath) {
            $('#photoModalImg').attr('src', imgPath);
            $('#photoModal').modal('show');
        }

        // Fonction pour afficher un texte complet (desc, etc.)
        function showDetail(title, content) {
            $('#detailModalLabel').text(title);
            $('#detailModalContent').text(content);
            $('#detailModal').modal('show');
        }

        // Afficher le planning (mini calendrier)
        function showPlanning(csvString) {
            // daysMap en JS pour repérer "lundi_matin" etc.
            const daysMapJS = {
                'lundi_matin': {
                    row: 'morning',
                    col: 'lun'
                },
                'lundi_aprem': {
                    row: 'afternoon',
                    col: 'lun'
                },
                'mardi_matin': {
                    row: 'morning',
                    col: 'mar'
                },
                'mardi_aprem': {
                    row: 'afternoon',
                    col: 'mar'
                },
                'mercredi_matin': {
                    row: 'morning',
                    col: 'mer'
                },
                'mercredi_aprem': {
                    row: 'afternoon',
                    col: 'mer'
                },
                'jeudi_matin': {
                    row: 'morning',
                    col: 'jeu'
                },
                'jeudi_aprem': {
                    row: 'afternoon',
                    col: 'jeu'
                },
                'vendredi_matin': {
                    row: 'morning',
                    col: 'ven'
                },
                'vendredi_aprem': {
                    row: 'afternoon',
                    col: 'ven'
                },
                'samedi_matin': {
                    row: 'morning',
                    col: 'sam'
                },
                'samedi_aprem': {
                    row: 'afternoon',
                    col: 'sam'
                },
                'dimanche_matin': {
                    row: 'morning',
                    col: 'dim'
                },
                'dimanche_aprem': {
                    row: 'afternoon',
                    col: 'dim'
                }
            };

            let parts = csvString.split(',');
            let calendar = {
                morning: {
                    lun: false,
                    mar: false,
                    mer: false,
                    jeu: false,
                    ven: false,
                    sam: false,
                    dim: false
                },
                afternoon: {
                    lun: false,
                    mar: false,
                    mer: false,
                    jeu: false,
                    ven: false,
                    sam: false,
                    dim: false
                }
            };

            // Remplir le calendrier
            parts.forEach(p => {
                if (daysMapJS[p]) {
                    let row = daysMapJS[p].row;
                    let col = daysMapJS[p].col;
                    calendar[row][col] = true;
                }
            });

            // Génère le HTML du mini-calendrier
            let html = `
          <table class="mini-calendar">
            <thead>
              <tr>
                <th></th>
                <th>Lun</th>
                <th>Mar</th>
                <th>Mer</th>
                <th>Jeu</th>
                <th>Ven</th>
                <th>Sam</th>
                <th>Dim</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Matin</td>
                <td class="${calendar.morning.lun ? 'bg-red' : ''}"></td>
                <td class="${calendar.morning.mar ? 'bg-red' : ''}"></td>
                <td class="${calendar.morning.mer ? 'bg-red' : ''}"></td>
                <td class="${calendar.morning.jeu ? 'bg-red' : ''}"></td>
                <td class="${calendar.morning.ven ? 'bg-red' : ''}"></td>
                <td class="${calendar.morning.sam ? 'bg-red' : ''}"></td>
                <td class="${calendar.morning.dim ? 'bg-red' : ''}"></td>
              </tr>
              <tr>
                <td>Après-midi</td>
                <td class="${calendar.afternoon.lun ? 'bg-red' : ''}"></td>
                <td class="${calendar.afternoon.mar ? 'bg-red' : ''}"></td>
                <td class="${calendar.afternoon.mer ? 'bg-red' : ''}"></td>
                <td class="${calendar.afternoon.jeu ? 'bg-red' : ''}"></td>
                <td class="${calendar.afternoon.ven ? 'bg-red' : ''}"></td>
                <td class="${calendar.afternoon.sam ? 'bg-red' : ''}"></td>
                <td class="${calendar.afternoon.dim ? 'bg-red' : ''}"></td>
              </tr>
            </tbody>
          </table>
        `;

            $('#planningModalContent').html(html);
            $('#planningModal').modal('show');
        }
    </script>
</body>

</html>