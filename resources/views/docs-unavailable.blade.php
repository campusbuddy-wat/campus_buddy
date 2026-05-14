<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation Not Available | Campus Buddy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0a0e1a;
            color: white;
            background-image:
                radial-gradient(at 20% 50%, rgba(99, 102, 241, 0.08) 0px, transparent 50%),
                radial-gradient(at 80% 20%, rgba(6, 182, 212, 0.06) 0px, transparent 50%);
        }
        .container {
            text-align: center;
            max-width: 500px;
            padding: 3rem;
        }
        .lock-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(6, 182, 212, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 2rem;
            border: 1px solid rgba(255,255,255,0.1);
        }
        h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #818cf8, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        p {
            color: rgba(255,255,255,0.6);
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #6366f1, #06b6d4);
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="lock-icon">🔒</div>
        <h1>Documentation Not Available</h1>
        <p>This documentation page is currently not accessible. It may be scheduled for a specific viewing window or temporarily disabled by the administrators.</p>
        <a href="{{ url('/') }}" class="btn">← Back to Home</a>
    </div>
</body>
</html>
