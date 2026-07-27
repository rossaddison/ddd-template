<?php

declare(strict_types=1);

/**
 * English translation messages for the Auth/Shell flows this template
 * ships with. Trimmed down from rossaddison/invoice's much larger
 * resources/messages/en/app.php — only the keys this repo's own code
 * actually references. Add more as you build on top of this template.
 */
return [
  'account.disabled' => 'Your account has been disabled.'
    . ' Please contact the administrator.',
  'account.have.already' => 'Already have an account? Log in',
  'account.no' => "Don't have an account? Sign up",
  'administrator' => 'Administrator',
  'continue.with.facebook' => 'Continue with Facebook',
  'continue.with.github' => 'Continue with Github',
  'continue.with.google' => 'Continue with Google',
  'continue.with.linkedin' => 'Continue with LinkedIn',
  'continue.with.microsoftonline' => 'Continue with MicrosoftOnline',
  'continue.with.vkontakte' => 'Continue with VKontakte',
  'continue.with.x' => 'Continue with X',
  'continue.with.yandex' => 'Continue with Yandex',
  'download' => 'Download',
  'email' => 'Email',
  'error.summary' => 'Error Summary',
  'forgot.your.password' => 'I forgot my password',
  'layout.go.home' => 'Go Back Home',
  'layout.login' => 'Login',
  'layout.not-found' => 'Not found',
  'layout.page.not-authorised' => 'Not Authorised:'
    . ' Authentication credentials are incorrect.',
  'layout.page.not-found' => 'The page {url}'
    . ' could not be found.',
  'layout.page.user-cancelled-oauth2' => 'User'
    . ' Cancelled Logging in / Registering'
    . ' via Identity Provider e.g Facebook',
  'layout.password' => 'Password',
  'layout.password-verify' => 'Confirm your password',
  'layout.password-verify.new' => 'Confirm your new password',
  'layout.password.new' => 'New Password',
  'layout.password.otp.6.first' => 'Enter First of Two Aegis Generated OTP'
    . ' Passwords (6 digits)',
  'layout.password.otp.6.8' => 'Enter Second Different Aegis Generated OTP'
    . ' Password (6 digits)'
    . ' / Backup Recovery Codes (8 digits)',
  'layout.remember' => 'Remember me',
  'layout.submit' => 'Submit',
  'login' => 'Login',
  'loginalert.user.not.found' => 'There is no'
    . ' account registered with this'
    . ' Email address.',
  'logout' => 'Logout',
  'menu.signup' => 'Signup',
  'oauth2.backup.recovery.codes' =>
    'Backup recovery codes. Keep in a safe place.',
  'oauth2.backup.recovery.codes.regenerate' =>
    'Regenerate Backup Recovery Codes',
  'oauth2.callback.unauthorised' =>
    'Unauthorised — the identity provider rejected the request.',
  'oauth2.missing.authentication.code.or.state.parameter' =>
    'Missing authentication code or state parameter.',
  'oauth2.missing.state.parameter.possible.csrf.attack' =>
    'State Parameter missing. Possible csrf attack',
  'onetime.password.error' =>
    'Something went wrong generating your one-time password.'
    . ' Please try logging in again.',
  'password.change' => 'Change Password',
  'password.reset' => 'Reset Password',
  'password.reset.email' =>
    'You requested a new password for your'
    . ' installation. Please click the link'
    . ' in your inbox to reset your password.',
  'password.reset.failed' => 'An error occurred'
    . ' while trying to send your password reset email.'
    . ' Please review the application logs'
    . ' or contact the system administrator.',
  'password.reset.request.token' => 'Request Password Reset Token',
  'password.reset.success' =>
    'Your password has been reset.'
    . ' You can now log in with your new password.',
  'record.successfully.created' =>
    'Record successfully created',
  'setting' => 'Setting',
  'signup.failed' => 'Signup failed. Please try again.',
  'signup.success' => 'Your account has been created.',
  'two.factor.authentication' => 'Two Factor Authentication',
  'two.factor.authentication.attempt.failure' =>
    'Two Factor Authentication Attempt Failure',
  'two.factor.authentication.attempt.failure.must.setup' =>
    'Two Factor Authentication Attempt Failure:'
    . ' you must set up a new QR code.',
  'two.factor.authentication.enabled.aegis' =>
    'Aegis Two Factor Authentication',
  'two.factor.authentication.enabled.with.disabling' =>
    'Two Factor Authentication is currently enabled.'
    . ' Compulsory scanning of Qr code.'
    . ' Press +  to enter a new secret.'
    . ' (Option 1)',
  'two.factor.authentication.enabled.without.disabling' =>
    'Two Factor Authentication is currently enabled.'
    . ' The Qr code will not be seen again for scanning.'
    . ' (Option 2)',
  'two.factor.authentication.form.verify.login' => 'Verify Login',
  'two.factor.authentication.invalid.code.format' =>
    'Invalid code format.'
    . ' Please enter the 6-digit code from your app.',
  'two.factor.authentication.invalid.backup.recovery.code' =>
    'Invalid 8 digit backup recovery code',
  'two.factor.authentication.invalid.totp.code' =>
    'Invalid 6 digit timed one-time authentication code',
  'two.factor.authentication.new.six.digit.code' =>
    'Please enter another confirmation 6-digit authentication code'
    . ' (different to the setup code) from your app.',
  'two.factor.authentication.no.secret.generated' =>
    'No secret generated. Please restart setup.',
  'two.factor.authentication.qr.code.enter.manually' =>
    'Or enter this code into the android app manually: ',
  'two.factor.authentication.rate.limit.reached' =>
    'Rate Limit reached. Please wait 10 seconds.',
  'two.factor.authentication.scan' =>
    'Scan this QR code with your Aegis app:',
  'two.factor.authentication.setup' =>
    'Setup Two Factor Authentication',
  'validator.invalid.login.password' => 'Invalid login or password',
  'validator.password.change' => 'Your Password has been changed',
  'validator.password.not.match' => 'Passwords do not match',
  'validator.password.not.match.new' => 'Your new passwords do not match',
  'validator.user.exist' => 'A User with this login already exists',
  'validator.user.exist.not' => 'A User with this login does not exist',
];
