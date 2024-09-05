<!DOCTYPE HTML>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @production
    @vite('resources/css/app.css')
  @endproduction
</head>
<body class="dark bg-black text-white" id="main">
  <h1 class="text-3xl font-bold underline py-16 text-center text-white">
    Hello world!
  </h1>
  <button hx-get="/welcome" hx-target="#main" class="text-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full">
    Click Me
  </button>
  <footer class="py-16 text-center text-sm text-white/70">
    Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
  </footer>
  @production
    @vite('resources/js/app.js')
  @endproduction
</body>
</html>