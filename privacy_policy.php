<?php
require_once 'config/con_db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privātuma politika | Vietējais brīvprātīgās centrs</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .privacy-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .privacy-section {
            margin-bottom: 30px;
        }
        .privacy-section h2 {
            color: #4CAF50;
            margin-bottom: 15px;
        }
        .privacy-section p {
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

    <div class="privacy-container">
        <div class="language-switch">
            <button onclick="switchLanguage('lv')">LV</button>
            <button onclick="switchLanguage('en')">EN</button>
        </div>

        <!-- Latvian Version -->
        <div id="lv-version">
            <div class="privacy-section">
                <h2>Privātuma politika</h2>
                <p>Vietējais brīvprātīgās centrs (turpmāk - "mēs", "mūsu" vai "centrs") ir apņēmies aizsargāt jūsu privātumu. Šī privātuma politika izskaidro, kā mēs apkopojam, izmantojam un aizsargājam jūsu personisko informāciju.</p>
            </div>

            <div class="privacy-section">
                <h2>Informācija, ko mēs apkopojam</h2>
                <p>Mēs apkopojam šādu informāciju:</p>
                <ul>
                    <li>Personīgā informācija (vārds, uzvārds, e-pasta adrese)</li>
                    <li>Kontaktinformācija (telefona numurs, adrese)</li>
                    <li>Profila informācija (lietotājvārds, profila bilde)</li>
                    <li>Brīvprātīgās darbības informācija (dalība pasākumos, stundas)</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>Kā mēs izmantojam jūsu informāciju</h2>
                <p>Mēs izmantojam jūsu informāciju, lai:</p>
                <ul>
                    <li>Pārvaldītu jūsu kontu un sniegtu jums piekļuvi mūsu pakalpojumiem</li>
                    <li>Komunicētu ar jums par brīvprātīgajām iespējām</li>
                    <li>Uzlabotu mūsu pakalpojumus</li>
                    <li>Nodrošinātu platformas drošību</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>Datu aizsardzība</h2>
                <p>Mēs ievērojam stingrus drošības pasākumus, lai aizsargātu jūsu personisko informāciju:</p>
                <ul>
                    <li>Visas datu pārsūtīšanas ir šifrētas</li>
                    <li>Regulāri veicam drošības auditus</li>
                    <li>Ierobežojam piekļuvi personīgajai informācijai</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>Jūsu tiesības</h2>
                <p>Jums ir tiesības:</p>
                <ul>
                    <li>Pieprasīt piekļuvi savai personīgajai informācijai</li>
                    <li>Pieprasīt informācijas labošanu</li>
                    <li>Pieprasīt informācijas dzēšanu</li>
                    <li>Atteikties no mārketinga komunikācijas</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>Saziņa ar mums</h2>
                <p>Ja jums ir jautājumi par šo privātuma politiku, lūdzu, sazinieties ar mums:</p>
                <p>E-pasts: privacy@vietejaiscentrs.lv</p>
                <p>Telefons: +371 11 111 111</p>
            </div>
        </div>

        <!-- English Version -->
        <div id="en-version" class="hidden">
            <div class="privacy-section">
                <h2>Privacy Policy</h2>
                <p>Local Volunteer Center (hereinafter - "we", "our" or "center") is committed to protecting your privacy. This privacy policy explains how we collect, use, and protect your personal information.</p>
            </div>

            <div class="privacy-section">
                <h2>Information We Collect</h2>
                <p>We collect the following information:</p>
                <ul>
                    <li>Personal information (name, surname, email address)</li>
                    <li>Contact information (phone number, address)</li>
                    <li>Profile information (username, profile picture)</li>
                    <li>Volunteer activity information (event participation, hours)</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>How We Use Your Information</h2>
                <p>We use your information to:</p>
                <ul>
                    <li>Manage your account and provide access to our services</li>
                    <li>Communicate with you about volunteer opportunities</li>
                    <li>Improve our services</li>
                    <li>Ensure platform security</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>Data Protection</h2>
                <p>We implement strict security measures to protect your personal information:</p>
                <ul>
                    <li>All data transfers are encrypted</li>
                    <li>Regular security audits</li>
                    <li>Limited access to personal information</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>Your Rights</h2>
                <p>You have the right to:</p>
                <ul>
                    <li>Request access to your personal information</li>
                    <li>Request correction of information</li>
                    <li>Request deletion of information</li>
                    <li>Opt-out of marketing communications</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>Contact Us</h2>
                <p>If you have questions about this privacy policy, please contact us:</p>
                <p>Email: privacy@vietejaiscentrs.lv</p>
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