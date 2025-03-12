<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=reppop', 'root', 'Deflagratione89');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

// Fonction redimension / compression
function resizeAndCompressImage($source, $destination, $maxWidth, $maxHeight, $quality)
{
    try {
        $image = new Imagick($source);
        $image->autoOrient();
        $image->resizeImage($maxWidth, $maxHeight, Imagick::FILTER_LANCZOS, 1, true);
        $image->stripImage();
        $image->setImageFormat('jpeg');
        $image->setImageCompression(Imagick::COMPRESSION_JPEG);
        $image->setImageCompressionQuality($quality);
        $image->writeImage($destination);
        $image->clear();
        $image->destroy();
    } catch (Exception $e) {
        error_log("Erreur Imagick : " . $e->getMessage());
        throw $e;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération
        $prenom             = $_POST['prenom']             ?? '';
        $nom                = $_POST['nom']                ?? '';
        $mail               = $_POST['mail']               ?? '';
        $tel                = $_POST['tel']                ?? '';
        $code_postal        = $_POST['code_postal']        ?? '';
        $ville              = $_POST['ville']              ?? '';
        $latitude           = $_POST['latitude']           ?? '';
        $longitude          = $_POST['longitude']          ?? '';
        $intitule_poste     = $_POST['intitule_poste']     ?? '';
        $specialite         = $_POST['specialite']         ?? '';
        $temps_travail      = $_POST['temps_travail']      ?? '';
        $jours_travailles   = $_POST['jours_travailles']   ?? [];
        $description_poste  = $_POST['description_poste']  ?? '';
        $autres_casquettes  = $_POST['autres_casquettes']  ?? '';
        $description        = $_POST['description']        ?? '';

        // Transforme l'array de jours en CSV
        $jours_csv = implode(',', $jours_travailles);

        // Légère variation lat/lon pour éviter la superposition exacte
        if (!empty($latitude) && !empty($longitude)) {
            // Variation de ±0.0001 (quelques mètres)
            $latitude  += mt_rand(-10, 10) / 100000;
            $longitude += mt_rand(-10, 10) / 100000;
        }

        // Gestion de la photo
        $photoName = null;

        // 1) Si on a du base64 recadré
        if (!empty($_POST['croppedImage'])) {
            $base64 = $_POST['croppedImage'];
            $parts = explode(',', $base64);
            if (count($parts) === 2) {
                $decoded = base64_decode($parts[1]);
                $photoName = time() . '_cropped.jpg';
                $photoPath = 'photos/' . $photoName;
                file_put_contents($photoPath, $decoded);

                // Compression
                $resizedPath = 'photos/resized_' . $photoName;
                resizeAndCompressImage($photoPath, $resizedPath, 800, 800, 75);
                unlink($photoPath);
                $photoName = 'resized_' . $photoName;
            }
        }
        // 2) Sinon, check upload classique
        else if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photo = $_FILES['photo'];
            $fileType = mime_content_type($photo['tmp_name']);
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/heic', 'image/heif'];
            if (!in_array($fileType, $allowed)) {
                throw new Exception("Fichier image invalide");
            }

            $photoName = time() . '_' . pathinfo($photo['name'], PATHINFO_FILENAME) . '.jpg';
            $photoPath = 'photos/' . $photoName;

            if (move_uploaded_file($photo['tmp_name'], $photoPath)) {
                $resizedPath = 'photos/resized_' . $photoName;
                resizeAndCompressImage($photoPath, $resizedPath, 800, 800, 75);
                unlink($photoPath);
                $photoName = 'resized_' . $photoName;
            }
        }

        // Insertion en BDD
        $stmt = $pdo->prepare("
            INSERT INTO personnes (
                prenom, nom, mail, tel, code_postal, ville,
                latitude, longitude, intitule_poste, specialite, temps_travail,
                jours_travailles, description_poste, autres_casquettes, description, photo
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $prenom,
            $nom,
            $mail,
            $tel,
            $code_postal,
            $ville,
            $latitude,
            $longitude,
            $intitule_poste,
            $specialite,
            $temps_travail,
            $jours_csv,
            $description_poste,
            $autres_casquettes,
            $description,
            $photoName
        ]);

        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Erreur : " . $e->getMessage());
    echo "Une erreur est survenue : " . $e->getMessage();
    http_response_code(500);
}