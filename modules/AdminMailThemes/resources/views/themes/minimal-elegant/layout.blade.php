<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  @if(isset($subject))
  <title>{{ $subject ?? __('Email Notification') }}</title>
  @else
    <title>@yield('subject', __('Email Notification'))</title>
  @endif
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { margin:0; padding:0; background: #f4f6f8; font-family: 'Inter', Arial, sans-serif; }
    .main-container { background: #fff; max-width: 460px; margin: 32px auto 0 auto; border-radius: 10px; box-shadow:0 2px 8px #0001; }
    .content { padding:32px 24px 28px 24px; color:#232323; }
    .btn {
      display:inline-block;
      background: #22223b;
      color:#fff;
      padding: 12px 28px;
      border-radius:5px;
      text-decoration:none;
      font-size:15px;
      margin: 18px 0 10px 0;
      transition:.15s;
      border:none;
    }
    .btn:hover { background:#4a4e69; }
    h2 { margin-top:0; font-weight:500; letter-spacing:.01em;}
    @media (max-width:600px) {
      .main-container { width: 98%!important; margin:12px auto; }
      .content { padding:16px 6px; }
    }
  </style>
</head>
<body>
  <div class="main-container">
    @include('adminmailthemes::themes.minimal-elegant.header')
    <div class="content">
      {!! $content??'' !!}
    </div>
    @include('adminmailthemes::themes.minimal-elegant.footer')
  </div>
</body>
</html>