<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#101b18">
    <title>Change temporary password | Sun Son Solar</title>
    <link rel="stylesheet" href="/assets/site.css"><link rel="stylesheet" href="/assets/portal.css">
</head>
<body class="portal-page"><main class="reset-shell"><a class="brand" href="/"><img class="brand-mark" src="/assets/sunson-logo.svg" alt=""><span class="brand-name">SUN SON <b>SOLAR</b></span></a><section class="portal-card"><span class="eyebrow eyebrow-dark">SECURE YOUR ACCOUNT</span><h1>Choose a new password</h1><p>Your administrator account is using a temporary password. Set a new password to continue.</p><?php if ($status): ?><div class="portal-alert is-error" role="status"><?= esc($status) ?></div><?php endif ?><?php if ($errors): ?><div class="portal-alert is-error" role="alert"><?= esc(implode(' ', $errors)) ?></div><?php endif ?><form class="stack-form" method="post" action="<?= site_url('portal/password') ?>"><?= csrf_field() ?><label>Temporary password<input name="current_password" type="password" required autocomplete="current-password"></label><label>New password <small>At least 10 characters</small><input name="new_password" type="password" minlength="10" required autocomplete="new-password"></label><label>Confirm new password<input name="confirm_password" type="password" minlength="10" required autocomplete="new-password"></label><button class="action-button" type="submit">Update password</button></form><form action="<?= site_url('logout') ?>" method="post" class="reset-logout"><?= csrf_field() ?><button class="text-button" type="submit">Sign out</button></form></section></main></body>
</html>
