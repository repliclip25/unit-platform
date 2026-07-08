<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIT — AI Workforce Platform for Construction</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --unit-yellow: #142C74;
            --accent:      #142C74;
            --accent-rgb:  241, 211, 98;
            --accent-dark: #0e2260;
            --accent-text: #142C74;
        }
        .unit-yellow { color: var(--accent); }
        .bg-unit-yellow { background-color: var(--accent); }
        .border-unit-yellow { border-color: var(--accent); }
        .hero-bg {
            background: radial-gradient(ellipse at 50% 100%, #0a2040 0%, #050c1a 50%, #020710 100%);
        }
        .glow-yellow { box-shadow: 0 0 30px rgba(245,193,0,0.3); }
        .worker-card { backdrop-filter: blur(10px); background: rgba(10,18,35,0.85); }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
        .float { animation: float 3s ease-in-out infinite; }
        .float-delay-1 { animation: float 3s ease-in-out 0.5s infinite; }
        .float-delay-2 { animation: float 3s ease-in-out 1s infinite; }
        .float-delay-3 { animation: float 3s ease-in-out 1.5s infinite; }
    </style>
</head>
<body class="hero-bg text-white font-sans antialiased min-h-screen">
    {{ $slot }}
</body>
</html>
