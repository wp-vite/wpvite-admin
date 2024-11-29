<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>wpvite Logo - Tech-Focused</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      background-color: #f4f4f4; /* Dark background for a tech vibe */
    }

    .logo-container {
        font-size: 3.2rem;
    }
  </style>

@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/common.css'])
</head>
<body>

    <div class="logo-svg">
        {{-- <!-- Example HTML --> --}}
        <img src="{{ asset('static/logo/wpvite-icon.svg') }}" alt="wpvite Logo Dark" class="logo dark-theme">
    </div>

    <div class="logo-container">
        <div class="wpvite_logo">
            <span class="wp">WP</span>
            <span class="brackets">&lt;</span>
            <span class="vite">Vite</span>
            <span class="brackets">&gt;</span>
        </div>
    </div>

    <div class="logo-svg">
        {{-- <!-- Example HTML --> --}}
        <img src="{{ asset('static/logo/wpvite-logo.svg') }}" alt="wpvite Logo Dark" class="logo dark-theme">
    </div>
</body>
</html>
