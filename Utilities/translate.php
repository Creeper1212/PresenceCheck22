<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Translate</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Load Bootstrap and a custom Google Font -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet">

    <style>
        body,
        .translator-container,
        #google_translate_element {
            font-family: 'Roboto', sans-serif;
        }
        .translator-container {
            max-width: 400px;
            margin: 50px auto;
            text-align: center;
            padding: 20px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
            background-color: #fff;
            border-radius: 8px;
        }
        /* Styling the default translate button */
        #google_translate_element .goog-te-gadget-simple {
            border: 1px solid #ccc;
            background: #f0f0f0 !important;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #google_translate_element .goog-te-gadget-simple:hover {
            background-color: #e0e0e0 !important;
        }
        /* Hide Google Translate's top banner and menu frame */
        .goog-te-banner-frame.skiptranslate,
        .goog-te-menu-frame.skiptranslate {
            display: none !important;
        }
    </style>

    <script>
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false
            }, 'google_translate_element');
        }
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</head>
<body>
    <div class="translator-container">
        <div id="google_translate_element"></div>
    </div>
</body>
</html>