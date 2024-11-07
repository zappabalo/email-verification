<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation des Emails</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-size: 1.1em;
        }
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        #result-container {
            display: none;
            margin-top: 20px;
        }
        #result-output {
            white-space: pre-wrap;
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 1em;
        }
        #download-btn {
            display: none;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #download-btn:hover {
            background-color: #0b7dda;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Validation des Emails</h1>
        <form id="email-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="email-file">Sélectionner un fichier d'emails (txt)</label>
                <input type="file" id="email-file" name="email-file" accept=".txt" required>
            </div>
            <div class="form-group">
                <label for="method">Choisissez la méthode de validation</label>
                <select id="method" name="method">
                    <option value="smtp">Validation via SMTP</option>
                    <option value="mx">Validation via MX</option>
                </select>
            </div>
            <button type="submit">Valider les Emails</button>
        </form>

        <div id="result-container">
            <h2>Résultats</h2>
            <pre id="result-output"></pre>
            <button id="download-btn">Télécharger les Résultats</button>
        </div>
    </div>

    <script>
        document.getElementById('email-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const emailFile = document.getElementById('email-file').files[0];
            const method = document.getElementById('method').value;

            if (!emailFile) {
                alert("Veuillez sélectionner un fichier d'emails.");
                return;
            }

            const formData = new FormData();
            formData.append("email-file", emailFile);
            formData.append("method", method);

            document.getElementById('result-output').textContent = 'En cours de traitement...';
            document.getElementById('result-container').style.display = 'block';

            fetch('votre_script_php.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('result-output').textContent = JSON.stringify(data.result, null, 2);
                    document.getElementById('download-btn').style.display = 'inline-block';
                    document.getElementById('download-btn').onclick = function() {
                        window.location.href = data.downloadLink;
                    };
                } else {
                    document.getElementById('result-output').textContent = 'Une erreur est survenue : ' + data.error;
                }
            })
            .catch(error => {
                document.getElementById('result-output').textContent = 'Erreur lors de la requête : ' + error;
            });
        });
    </script>
</body>
</html>
