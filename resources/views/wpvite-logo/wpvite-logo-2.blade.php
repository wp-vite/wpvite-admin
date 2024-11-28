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
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      background-color: #f4f4f4; /* Dark background for a tech vibe */
    }

    .wpvite_logo {
      font-size: 3.5rem;
      font-weight: 700;
      color: #ffffff;
      display: flex;
      align-items: center;
    }

    .wpvite_logo .wp {
      color: #0073AA; /* WordPress Blue */
    }

    .wpvite_logo .brackets {
      color: #1DB954; /* Green for growth and innovation */
    }

    .wpvite_logo .vite {
      background: linear-gradient(90deg, #1DB954, #00C9A7); /* Gradient for "vite" */
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      font-style: italic;
      margin: 0 5px;
    }
  </style>
</head>
<body>
  <div class="wpvite_logo">
    <span class="wp">wp</span>
    <span class="brackets">&lt;</span>
    <span class="vite">vite</span>
    <span class="brackets">&gt;</span>
  </div>
  <div class="logo-svg">
    {{-- <!-- Example HTML --> --}}
    <img src="{{ asset('static/logo/wpvite-logo.svg') }}" alt="wpvite Logo Dark" class="logo dark-theme">
  </div>
  <div class="logo-svg">
    {{-- <!-- Example HTML --> --}}
    <img src="{{ asset('static/logo/wpvite-icon.svg') }}" alt="wpvite Logo Dark" class="logo dark-theme">
  </div>
</body>
</html>
