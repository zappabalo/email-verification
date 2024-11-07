<?php

// Fonction pour valider un email via la syntaxe
function validateEmailSyntax($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Vérification des enregistrements MX via DNS
function checkMXRecords($domain) {
    return checkdnsrr($domain, 'MX');
}

// Fonction pour récupérer les enregistrements MX
function getMXRecords($domain) {
    $mxhosts = [];
    if (getmxrr($domain, $mxhosts)) {
        return $mxhosts[0]; // Retourne le premier serveur MX
    }
    return false;
}

// Fonction pour vérifier si l'email est actif via un ping SMTP
function validateEmailActive($email) {
    // Extraire le domaine de l'email
    $domain = substr(strrchr($email, "@"), 1);
    
    // Vérifier les enregistrements MX
    if (!checkMXRecords($domain)) {
        return 'Le domaine n\'a pas d\'enregistrements MX.';
    }

    $smtpServer = getMXRecords($domain);

    // Connexion au serveur SMTP
    $smtpConnection = fsockopen($smtpServer, 25, $errno, $errstr, 10);

    if (!$smtpConnection) {
        return 'Inaccessible';
    } else {
        // Tenter un dialogue SMTP
        $response = fgets($smtpConnection, 1024);  // Lire la réponse du serveur
        if (substr($response, 0, 3) != '220') {
            fclose($smtpConnection);
            return 'Le serveur ne répond pas correctement.';
        }

        // Simuler l'initiation de la connexion SMTP
        fwrite($smtpConnection, "HELO example.com\r\n");
        $response = fgets($smtpConnection, 1024);
        if (substr($response, 0, 3) != '250') {
            fclose($smtpConnection);
            return 'Échec de la négociation SMTP.';
        }

        // Simuler une tentative de "MAIL FROM" et "RCPT TO"
        fwrite($smtpConnection, "MAIL FROM:<example@example.com>\r\n");
        $response = fgets($smtpConnection, 1024);
        fwrite($smtpConnection, "RCPT TO:<$email>\r\n");
        $response = fgets($smtpConnection, 1024);

        // Vérification de la réponse pour confirmer si l'email est valide
        if (substr($response, 0, 3) == '250') {
            fclose($smtpConnection);
            return 'Email actif et valide.';
        } else {
            fclose($smtpConnection);
            return 'Email non actif ou refusé.';
        }
    }
}

// Traitement du fichier texte avec les emails
if ($_FILES['email-file']) {
    $emails = file($_FILES['email-file']['tmp_name'], FILE_IGNORE_NEW_LINES);
    $results = [];

    foreach ($emails as $email) {
        if (validateEmailSyntax($email)) {
            $mxResult = checkMXRecords(substr(strrchr($email, "@"), 1)) ? 'MX valide' : 'MX invalide';
            $activeResult = validateEmailActive($email);
            $results[] = ['email' => $email, 'syntax' => 'Valide', 'mx' => $mxResult, 'active' => $activeResult];
        } else {
            $results[] = ['email' => $email, 'syntax' => 'Invalide', 'mx' => 'N/A', 'active' => 'N/A'];
        }
    }

    // Créer un fichier de sortie avec les résultats
    $outputFile = 'results_' . date('Y-m-d_H-i-s') . '.txt';
    file_put_contents($outputFile, json_encode($results, JSON_PRETTY_PRINT));

    // Retourner les résultats sous forme de JSON pour l'interface
    echo json_encode(['status' => 'success', 'result' => $results, 'downloadLink' => $outputFile]);
} else {
    echo json_encode(['status' => 'error', 'error' => 'Aucun fichier sélectionné']);
}

?>
