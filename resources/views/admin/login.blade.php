<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Kojo Hotel</title>

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Poppins', sans-serif;
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;

            background:
                linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.55)),
                url('https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1600&q=80');

            background-size:cover;
            background-position:center;
        }

        .login-card{
            width:400px;
            background:rgba(255,255,255,0.08);
            backdrop-filter:blur(15px);
            padding:40px;
            border-radius:20px;
            border:1px solid rgba(255,255,255,0.15);
            box-shadow:0 15px 40px rgba(0,0,0,.4);
        }

        .hotel-name{
            font-family:'Cormorant Garamond', serif;
            color:#fff;
            text-align:center;
            font-size:42px;
            margin-bottom:5px;
        }

        .subtitle{
            text-align:center;
            color:rgba(255,255,255,.8);
            margin-bottom:35px;
            font-size:14px;
            letter-spacing:2px;
            text-transform:uppercase;
        }

        .input-group{
            margin-bottom:18px;
        }

        input{
            width:100%;
            padding:14px 16px;
            border:none;
            border-radius:10px;
            background:rgba(255,255,255,.12);
            color:white;
            font-size:15px;
            outline:none;
        }

        input::placeholder{
            color:rgba(255,255,255,.7);
        }

        .login-btn{
            width:100%;
            padding:14px;
            border:none;
            border-radius:10px;
            background:linear-gradient(135deg,#c8a96a,#e6c37a);
            color:#222;
            font-weight:600;
            cursor:pointer;
            transition:.3s;
            margin-top:10px;
        }

        .login-btn:hover{
            transform:translateY(-2px);
            box-shadow:0 10px 20px rgba(230,195,122,.3);
        }

        .error{
            background:rgba(255,0,0,.15);
            color:#ffb3b3;
            padding:12px;
            border-radius:8px;
            margin-bottom:15px;
            text-align:center;
        }
    </style>
</head>
<body>

<div class="login-card">

    <h1 class="hotel-name">Sea Eagle Beach Resort</h1>
    <p class="subtitle">Admin Management System</p>

    @if(session('error'))
        <div class="error">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="/admin/login">
        @csrf

        <div class="input-group">
            <input type="email" name="email" placeholder="Email Address" required>
        </div>

        <div class="input-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit" class="login-btn">
            Login
        </button>

    </form>

</div>

</body>
</html>