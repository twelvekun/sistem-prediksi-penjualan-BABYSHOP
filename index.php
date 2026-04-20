<?php
require 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico">
    <link rel="icon" href="icon.ico" type="image/ico">
    <title>BABYSHOP - Login</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="assets/fontawesome/css/all.min.css" rel="stylesheet" type="text/css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #FFF0DB;
            min-height: 100vh;
            overflow: hidden; 
        }

        .split-layout {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        .left-panel {
            flex: 0 0 45%;
            background-color: #FFF0DB; 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: white;
        }

        .right-panel {
            flex: 1;
            background-color: #FBF7E6;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .login-box {
            width: 100%;
            max-width: 360px;
        }

        .login-box h1 {
            text-align: center;
            font-weight: 800;
            font-size: 26px;
            margin-bottom: 90px; 
            text-transform: uppercase;
            letter-spacing: 1px;
            color: purple;
        }

        .inner-img-container {
            text-align: center;
            margin-bottom: 10px; 
            margin-inline-start: auto;
        }

        .inner-img {
            max-width: 200px; /* Silakan ubah angka ini untuk memperbesar/memperkecil gambar */
            height: auto;
            object-fit: contain;
            color: #0000FF;
        }

        .form-group label {
            text-transform: uppercase;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 15px;
            display: block;
            color: black;
        }

        .form-control {
            border-radius: 8px;
            border: none;
            padding: 14px 16px;
            margin-bottom: 20px;
            width: 100%;
            font-size: 14px;
            background-color: #f8f9fa;
        }

        .form-control::placeholder {
            color: #a0aab2;
        }

        .form-control:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }

        .btn-login-wrapper {
            text-align: center;
            margin-top: 10px;
        }

        .btn-login {
            background-color: white;
            color: purple;
            border: none;
            border-radius: 25px;
            padding: 10px 30px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-login:hover {
            background-color: #f1f2f6;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .illustration-img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
        }

        @media (max-width: 768px) {
            .split-layout {
                flex-direction: column;
            }
            .left-panel {
                flex: 1;
                width: 100%;
            }
            .right-panel {
                display: none;
            }
            body {
                overflow: auto;
            }
        }
    </style>
</head>
<body>

    <div class="split-layout">
        
        <div class="left-panel">
            <div class="login-box">
                
                
                <div class="inner-img-container">
                    <img src="assets/img/mama asix.png" alt="Logo Internal" class="inner-img">
                </div>
                <h1 class="login-box h1">MAMA ASIX BABYSHOP</h1>
                <form method="POST" action="login_process.php">
                    <div class="form-group">
                        <label for="username">USERNAME</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="username" 
                            name="username" 
                            placeholder="Masukkan Username anda" 
                            required
                            autocomplete="username"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password">PASSWORD</label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan Password anda" 
                            required
                            autocomplete="current-password"
                        >
                    </div>
                    
                    <div class="btn-login-wrapper">
                        <button class="btn-login" name="login" type="submit">
                            Masuk <i class="fas fa-jelly fa-arrow-right"></i>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <div class="right-panel">
            <img src="assets/img/bg.jpg" alt="Ilustrasi" class="illustration-img">
        </div>

    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>