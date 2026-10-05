<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Allocore Manager — Sign in</title>
<style>
  body{margin:0;font-family:ui-sans-serif,system-ui;background:#0f172a;color:#0f172a;display:grid;place-items:center;min-height:100vh}
  .card{background:#fff;border-radius:16px;padding:32px;width:340px;box-shadow:0 20px 50px #0006}
  h1{font-size:18px;margin:0 0 4px}.sub{color:#64748b;font-size:13px;margin-bottom:20px}
  input{width:100%;padding:10px 12px;border:1px solid #d6dee9;border-radius:8px;margin-bottom:12px;font-size:14px;box-sizing:border-box}
  button{width:100%;padding:10px;background:#ca8a04;color:#fff;border:0;border-radius:8px;font-weight:600;cursor:pointer}
  .err{color:#dc2626;font-size:13px;margin-bottom:10px}
</style>
</head>
<body>
<form class="card" method="post" action="/login">
  <h1>Allocore Manager</h1>
  <div class="sub">Decision intelligence — sign in</div>
  @csrf
  @error('email')<div class="err">{{ $message }}</div>@enderror
  <input type="email" name="email" placeholder="E-mail" value="{{ old('email') }}" required autofocus>
  <input type="password" name="password" placeholder="Password" required>
  <button type="submit">Sign in</button>
</form>
</body>
</html>
