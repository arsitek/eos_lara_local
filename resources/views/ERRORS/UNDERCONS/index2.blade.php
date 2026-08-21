<!DOCTYPE html>
<html>
<head>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
    <title>Oops...</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            font-family: 'Arial', sans-serif;
            overflow: hidden;
            perspective: 1000px;
        }

        .container {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            position: relative;
            animation: containerFloat 6s ease-in-out infinite;
            transform-style: preserve-3d;
        }

        @keyframes containerFloat {
            0%, 100% { transform: translateY(0) rotateX(0) rotateY(0); }
            25% { transform: translateY(-10px) rotateX(2deg) rotateY(2deg); }
            75% { transform: translateY(10px) rotateX(-2deg) rotateY(-2deg); }
        }

        h1 {
            font-size: 3rem;
            color: white;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 1rem;
            position: relative;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            line-height: 1.6;
            animation: textGlow 2s ease-in-out infinite alternate;
        }

        @keyframes textGlow {
            from { text-shadow: 0 0 5px rgba(255, 255, 255, 0.5); }
            to { text-shadow: 0 0 20px rgba(255, 255, 255, 0.8); }
        }

        .construction-icon {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 2rem;
        }

        .circle {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: rotate 2s linear infinite;
        }

        .inner-circle {
            position: absolute;
            width: 70%;
            height: 70%;
            top: 15%;
            left: 15%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: rotate 3s linear infinite reverse;
        }

        .middle-circle {
            position: absolute;
            width: 85%;
            height: 85%;
            top: 7.5%;
            left: 7.5%;
            border: 4px solid rgba(255, 255, 255, 0.2);
            border-right: 4px solid white;
            border-radius: 50%;
            animation: rotate 4s linear infinite;
        }

        .dots {
            position: absolute;
            width: 100%;
            height: 100%;
            animation: rotate 10s linear infinite;
        }

        .dot {
            position: absolute;
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

        .dot:nth-child(1) { top: 0; left: 50%; transform: translateX(-50%); animation-delay: 0s; }
        .dot:nth-child(2) { top: 50%; right: 0; transform: translateY(-50%); animation-delay: 0.5s; }
        .dot:nth-child(3) { bottom: 0; left: 50%; transform: translateX(-50%); animation-delay: 1s; }
        .dot:nth-child(4) { top: 50%; left: 0; transform: translateY(-50%); animation-delay: 1.5s; }
        .dot:nth-child(5) { top: 15%; right: 15%; animation-delay: 0.8s; }
        .dot:nth-child(6) { bottom: 15%; right: 15%; animation-delay: 1.3s; }
        .dot:nth-child(7) { bottom: 15%; left: 15%; animation-delay: 1.8s; }
        .dot:nth-child(8) { top: 15%; left: 15%; animation-delay: 2.3s; }

        .progress-container {
            width: 80%;
            margin: 2rem auto;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            position: absolute;
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.9), transparent);
            animation: loading 2s ease-in-out infinite;
        }

        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            pointer-events: none;
        }

        .sparkle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: white;
            border-radius: 50%;
            animation: sparkle 3s linear infinite;
        }

        @keyframes sparkle {
            0% { transform: scale(0) rotate(0deg); opacity: 0; }
            50% { transform: scale(1) rotate(180deg); opacity: 1; }
            100% { transform: scale(0) rotate(360deg); opacity: 0; }
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes rotate {
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.5); opacity: 0.5; }
        }

        @keyframes loading {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(200%); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .shooting-star {
            position: absolute;
            width: 2px;
            height: 2px;
            background: white;
            pointer-events: none;
        }

        @keyframes shoot {
            0% { transform: translateX(0) translateY(0) rotate(45deg) scale(0); opacity: 0; }
            50% { transform: translateX(-50vw) translateY(50vh) rotate(45deg) scale(1); opacity: 1; }
            100% { transform: translateX(-100vw) translateY(100vh) rotate(45deg) scale(0); opacity: 0; }
        }

        h1 span {
            display: inline-block;
            animation: float 3s ease-in-out infinite;
            animation-delay: calc(0.1s * var(--i));
        }

        .glow-effect {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: radial-gradient(circle at var(--x) var(--y), rgba(255,255,255,0.2) 0%, transparent 50%);
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="construction-icon">
            <div class="circle"></div>
            <div class="middle-circle"></div>
            <div class="inner-circle"></div>
            <div class="dots">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>
        <h1>
            <span style="--i:1">U</span>
            <span style="--i:2">n</span>
            <span style="--i:3">d</span>
            <span style="--i:4">e</span>
            <span style="--i:5">r</span>
            <span style="--i:7">C</span>
            <span style="--i:8">o</span>
            <span style="--i:9">n</span>
            <span style="--i:10">s</span>
            <span style="--i:11">t</span>
            <span style="--i:12">r</span>
            <span style="--i:13">u</span>
            <span style="--i:14">c</span>
            <span style="--i:15">t</span>
            <span style="--i:16">i</span>
            <span style="--i:17">o</span>
            <span style="--i:18">n</span>
        </h1>
        <p>
            Mohon maaf atas ketidaknyamanan yang terjadi.<br>
            Proses impor data RKT Tahun Anggaran Indikatif 2025 ke Definitif 2025 sedang berlangsung.<br>
            Halaman akan segera kembali seperti semula.
        </p>
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
        <div class="glow-effect"></div>
    </div>
    <div class="particles"></div>

    <script>
        // Create more varied floating particles
        const particlesContainer = document.querySelector('.particles');
        for (let i = 0; i < 100; i++) { // Increased from 50 to 100 particles
            const particle = document.createElement('div');
            particle.className = 'particle';
            
            // Randomize particle sizes
            const size = Math.random() * 6 + 2;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            
            // Initial position
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.top = `${Math.random() * 100}%`;
            
            // Randomized animation
            const duration = 3 + Math.random() * 4;
            const delay = Math.random() * 2;
            particle.style.animation = `float ${duration}s ease-in-out ${delay}s infinite`;
            
            // Randomize opacity
            particle.style.opacity = Math.random() * 0.5 + 0.2;
            
            particlesContainer.appendChild(particle);
        }

        // Add sparkles
        function createSparkle() {
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle';
            sparkle.style.left = `${Math.random() * 100}%`;
            sparkle.style.top = `${Math.random() * 100}%`;
            particlesContainer.appendChild(sparkle);
            
            setTimeout(() => {
                sparkle.remove();
            }, 3000);
        }

        setInterval(createSparkle, 300);

        // Add shooting stars
        function createShootingStar() {
            const star = document.createElement('div');
            star.className = 'shooting-star';
            star.style.top = `${Math.random() * 50}%`;
            star.style.left = '100%';
            star.style.animation = `shoot ${1 + Math.random()}s linear forwards`;
            document.body.appendChild(star);
            
            setTimeout(() => {
                star.remove();
            }, 2000);
        }

        setInterval(createShootingStar, 2000);

        // Interactive glow effect
        const container = document.querySelector('.container');
        const glowEffect = document.querySelector('.glow-effect');

        container.addEventListener('mousemove', (e) => {
            const rect = container.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            glowEffect.style.setProperty('--x', `${x}px`);
            glowEffect.style.setProperty('--y', `${y}px`);
        });
    </script>
</body>
</html>