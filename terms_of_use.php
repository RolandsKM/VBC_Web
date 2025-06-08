<?php
require_once 'config/con_db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lietošanas noteikumi | Vietējais brīvprātīgās centrs</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .terms-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .terms-section {
            margin-bottom: 30px;
        }
        .terms-section h2 {
            color: #4CAF50;
            margin-bottom: 15px;
        }
        .terms-section p {
            line-height: 1.6;
            margin-bottom: 10px;
        }
        .language-switch {
            text-align: right;
            margin-bottom: 20px;
        }
        .language-switch button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        .language-switch button:hover {
            background: #45a049;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <div class="terms-container">
        <div class="language-switch">
            <button onclick="switchLanguage('lv')">LV</button>
            <button onclick="switchLanguage('en')">EN</button>
        </div>

        <!-- Latvian Version -->
        <div id="lv-version">
            <div class="terms-section">
                <h2>Lietošanas noteikumi</h2>
                <p>Lūdzu, uzmanīgi izlasiet šos lietošanas noteikumus pirms Vietējā brīvprātīgā centra (turpmāk - "centrs") pakalpojumu izmantošanas.</p>
            </div>

            <div class="terms-section">
                <h2>1. Vispārīgi noteikumi</h2>
                <p>Izmantojot centra pakalpojumus, jūs piekrītat:</p>
                <ul>
                    <li>Nodrošināt precīzu un patiesu informāciju reģistrācijas laikā</li>
                    <li>Uzturēt savu konta informāciju aktuālu</li>
                    <li>Nesniegt maldinošu vai krāpniecisku informāciju</li>
                    <li>Neizmantot platformu nelikumīgiem mērķiem</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>2. Lietotāju pienākumi</h2>
                <p>Kā centra lietotājs, jums jāievēro:</p>
                <ul>
                    <li>Respektēt citu lietotāju privātumu un tiesības</li>
                    <li>Neizplatīt nevēlamu saturu vai reklāmu</li>
                    <li>Neizmantot platformu mērķiem, kas nav saistīti ar brīvprātīgo darbu</li>
                    <li>Informēt centru par jebkādiem drošības pārkāpumiem</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>3. Pasākumu organizēšana</h2>
                <p>Organizējot pasākumus, jums jāievēro:</p>
                <ul>
                    <li>Nodrošināt pasākumu drošību un atbilstību likumdošanai</li>
                    <li>Precīzi aprakstīt pasākuma mērķi un prasības</li>
                    <li>Respektēt brīvprātīgo laiku un ieguldījumu</li>
                    <li>Nodrošināt nepieciešamo apmācību un resursus</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>4. Atbildība</h2>
                <p>Centrs:</p>
                <ul>
                    <li>Nodrošina platformas tehnisko funkcionēšanu</li>
                    <li>Neuzņemas atbildību par brīvprātīgo darbību rezultātiem</li>
                    <li>Patur tiesības bloķēt lietotājus, kas pārkāpj noteikumus</li>
                    <li>Neuzņemas atbildību par trešo pušu saturu</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>5. Intelektuālā īpašuma tiesības</h2>
                <p>Visas platformas tiesības pieder centram. Lietotāji:</p>
                <ul>
                    <li>Nevar kopēt vai izplatīt platformas saturu bez atļaujas</li>
                    <li>Patur tiesības uz savu ievietoto saturu</li>
                    <li>Piekrīt centram izmantot viņu saturu platformas darbībai</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>6. Izmaiņas noteikumos</h2>
                <p>Centrs patur tiesības mainīt šos noteikumus. Par izmaiņām lietotāji tiks informēti:</p>
                <ul>
                    <li>Pa e-pastu</li>
                    <li>Caur platformas paziņojumiem</li>
                    <li>Publiskojot atjauninātos noteikumus</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>7. Saziņa</h2>
                <p>Ja jums ir jautājumi par lietošanas noteikumiem, lūdzu, sazinieties:</p>
                <p>E-pasts: terms@vietejaiscentrs.lv</p>
                <p>Telefons: +371 11 111 111</p>
            </div>
        </div>

        <!-- English Version -->
        <div id="en-version" class="hidden">
            <div class="terms-section">
                <h2>Terms of Use</h2>
                <p>Please read these terms of use carefully before using the services of the Local Volunteer Center (hereinafter - "center").</p>
            </div>

            <div class="terms-section">
                <h2>1. General Terms</h2>
                <p>By using the center's services, you agree to:</p>
                <ul>
                    <li>Provide accurate and truthful information during registration</li>
                    <li>Keep your account information up to date</li>
                    <li>Not provide misleading or fraudulent information</li>
                    <li>Not use the platform for illegal purposes</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>2. User Responsibilities</h2>
                <p>As a center user, you must:</p>
                <ul>
                    <li>Respect other users' privacy and rights</li>
                    <li>Not distribute unwanted content or advertisements</li>
                    <li>Not use the platform for purposes unrelated to volunteer work</li>
                    <li>Inform the center of any security breaches</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>3. Event Organization</h2>
                <p>When organizing events, you must:</p>
                <ul>
                    <li>Ensure event safety and compliance with legislation</li>
                    <li>Accurately describe event purpose and requirements</li>
                    <li>Respect volunteer time and contribution</li>
                    <li>Provide necessary training and resources</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>4. Liability</h2>
                <p>The center:</p>
                <ul>
                    <li>Ensures technical functionality of the platform</li>
                    <li>Is not responsible for volunteer activity outcomes</li>
                    <li>Reserves the right to block users who violate terms</li>
                    <li>Is not responsible for third-party content</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>5. Intellectual Property Rights</h2>
                <p>All platform rights belong to the center. Users:</p>
                <ul>
                    <li>Cannot copy or distribute platform content without permission</li>
                    <li>Retain rights to their uploaded content</li>
                    <li>Agree to the center using their content for platform operation</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>6. Terms Changes</h2>
                <p>The center reserves the right to modify these terms. Users will be notified of changes:</p>
                <ul>
                    <li>By email</li>
                    <li>Through platform notifications</li>
                    <li>By publishing updated terms</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>7. Contact</h2>
                <p>If you have questions about the terms of use, please contact:</p>
                <p>Email: terms@vietejaiscentrs.lv</p>
                <p>Phone: +371 11 111 111</p>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>

    <script>
        function switchLanguage(lang) {
            if (lang === 'lv') {
                document.getElementById('lv-version').classList.remove('hidden');
                document.getElementById('en-version').classList.add('hidden');
            } else {
                document.getElementById('lv-version').classList.add('hidden');
                document.getElementById('en-version').classList.remove('hidden');
            }
        }
    </script>
</body>
</html> 