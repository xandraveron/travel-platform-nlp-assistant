
<?php
session_start();
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . "/connect_db.php";

$ERR = $_SESSION['ERR'] ?? '';
$OK  = $_SESSION['OK'] ?? '';
$form_to_show = $_SESSION['form_to_show'] ?? '';
unset($_SESSION['ERR'], $_SESSION['OK'], $_SESSION['form_to_show']);

// login
if (isset($_POST['login_check'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conectare->prepare("SELECT id, username, email, password FROM users");
    $stmt->execute();
    $result = $stmt->get_result();

    $matched_user = null;
    while ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            // Password matched, we check email
            if ($row['email'] === $email) {
                $matched_user = $row;
                break;
            } else {
                $_SESSION['ERR'] = "Email does not match password.";
                break;
            }
        }
    }

    if ($matched_user) {
        $_SESSION['user_id']    = $matched_user['id'];
        $_SESSION['username']   = $matched_user['username'];
        $_SESSION['email']      = $matched_user['email'];
        $_SESSION['existing']   = true;
        $_SESSION['user_type']  = 'user';

        $redirect = $_GET['return'] ?? 'travel.php';
        header("Location: $redirect");
        exit;
    }

    if (!isset($_SESSION['ERR'])) {
        $_SESSION['ERR'] = "Password not recognized.";
    }

    $stmt->close();
    $_SESSION['form_to_show'] = 'login';
    header("Location: login.php" . (isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : ''));
    exit;
}


// signup
if (isset($_POST['do_signup'])) {
    $u     = trim($_POST['username']);
    $pass  = trim($_POST['password']);
    $mail  = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $st = $conectare->prepare("SELECT id FROM users WHERE username=?");
    $st->bind_param('s', $u);
    $st->execute();
    $st->store_result();

    if ($st->num_rows) {
        $_SESSION['ERR'] = "Username already used";
        $_SESSION['form_to_show'] = 'signup';
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $ins = $conectare->prepare("INSERT INTO users(username,password,email,phone,user_type) VALUES(?,?,?,?, 'user')");
        $ins->bind_param('ssss', $u, $hash, $mail, $phone);

        if ($ins->execute()) {
            $_SESSION['user_id']    = $ins->insert_id;
            $_SESSION['username']   = $u;
            $_SESSION['email'] = $mail;
            $_SESSION['existing']   = true;
            $_SESSION['user_type']  = 'user';
            $_SESSION['OK'] = "Account created successfully";
            $_SESSION['form_to_show'] = 'signup';
            $_SESSION['signup_success_redirect'] = $_GET['return'] ?? 'my_bookings.php';
            header("Location: login.php" . (isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : ''));
            exit;

        } else {
            $_SESSION['ERR'] = "Signup failed, try again";
            $_SESSION['form_to_show'] = 'signup';
        }
        $ins->close();
    }
    $st->close();
    header("Location: login.php");
    exit;
}
function sendCode($to, $code) {
    $m = new PHPMailer(true);
    try {
        $m->isSMTP();
        $m->Host       = 'smtp.gmail.com';
        $m->SMTPAuth   = true;
        $m->Username   = 'dobraalexandra005@gmail.com';
        $m->Password   = 'pvpx amcz xpil djti';
        $m->SMTPSecure = 'tls';
        $m->Port       = 587;
        $m->setFrom('dobraalexandra005@gmail.com', 'Travel Site');
        $m->addAddress($to);
        $m->isHTML(true);
        $m->Subject = 'Your verification code';
        $m->Body    = 'Your code is: <b>' . $code . '</b>';
        $m->send();
    } catch (Exception $e) {
                error_log("Email error: " . $m->ErrorInfo); 

    }
    if ($m->send()) {
    error_log("Confirmation email sent to $to");
}
}

// START RECOVERY — PASSWORD or EMAIL
if (isset($_POST['start_recovery'])) {
    $u = trim($_POST['rec_username']);
    $type = $_POST['rec_type'];
    $mail = ($type === 'password') ? trim($_POST['rec_email']) : null;
    $pass = ($type === 'email') ? trim($_POST['rec_password']) : null;

    if ($type === 'password') {
        $st = $conectare->prepare("SELECT id FROM users WHERE username=? AND email=?");
        $st->bind_param('ss', $u, $mail);
        $st->execute(); $st->store_result();
        if ($st->num_rows) {
            $_SESSION['recovery'] = ['username' => $u, 'type' => 'password'];
            $_SESSION['form_to_show'] = 'set_new_password';
        } else {
            $_SESSION['ERR'] = "Username/email mismatch";
            unset($_SESSION['recovery']);
            $_SESSION['form_to_show'] = 'recovery';
        }
        $st->close();
    } elseif ($type === 'email') {
        $st = $conectare->prepare("SELECT password FROM users WHERE username=?");
        $st->bind_param('s', $u);
        $st->execute(); $st->store_result();

        if ($st->num_rows) {
            $st->bind_result($hash); $st->fetch();
            if (password_verify($pass, $hash)) {
                $_SESSION['recovery'] = ['username' => $u, 'type' => 'email'];
                $_SESSION['form_to_show'] = 'enter_new_email';
            } else {
                $_SESSION['ERR'] = "Incorrect password for $u";
                $_SESSION['form_to_show'] = 'recovery';
                unset($_SESSION['recovery']);
            }
        } else {
            $_SESSION['ERR'] = "Username not found";
            $_SESSION['form_to_show'] = 'recovery';
            unset($_SESSION['recovery']);
        }
        $st->close();
    }

    header("Location: login.php");
    exit;
}

// STEP 2: NEW EMAIL + SEND CODE
if (isset($_POST['send_email_code']) && !empty($_SESSION['recovery']) && $_SESSION['recovery']['type'] === 'email') {
    $new_email = trim($_POST['new_email']);
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['ERR'] = "Invalid email";
        $_SESSION['form_to_show'] = 'enter_new_email';
    } else {
        $code = rand(100000, 999999);
        $_SESSION['recovery']['new_email'] = $new_email;
        $_SESSION['recovery']['code'] = $code;
        $_SESSION['recovery']['expires'] = time() + 300;
        sendCode($new_email, $code);
        $_SESSION['OK'] = "Verification code sent to $new_email";
        $_SESSION['form_to_show'] = 'verify_code';
    }
    header("Location: login.php");
    exit;
}

// VERIFY CODE
if (isset($_POST['finish_recovery'])) {
    if (empty($_SESSION['recovery'])) {
        $_SESSION['ERR'] = "The session expired,please start again";
        $_SESSION['form_to_show'] = 'recovery';
    } else {
        $rec = $_SESSION['recovery'];
        $now = time();
        if ($now > $rec['expires']) {
            unset($_SESSION['recovery']);
            $_SESSION['ERR'] = "The code expired, please try again";
            $_SESSION['form_to_show'] = 'recovery';
        } elseif (intval($_POST['verif_code']) !== intval($rec['code'])) {
            $_SESSION['ERR'] = "Wrong code";
            $_SESSION['form_to_show'] = 'verify_code';
        } else {
            $new_email = $rec['new_email'];
            $upd = $conectare->prepare("UPDATE users SET email=? WHERE username=?");
            $upd->bind_param('ss', $new_email, $rec['username']);
            $upd->execute(); $upd->close();
            unset($_SESSION['recovery']);
            $_SESSION['OK'] = "Email updated to $new_email — please log in";
            $_SESSION['form_to_show'] = 'login';
        }
    }
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Account Access</title>
    <link rel="stylesheet" href="travel.css">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .form-section { display: none; max-width: 500px; margin: auto; }
  </style>
</head>
<body class="book1">

  <div class="login-container">

    <h2>Account</h2>

  <?php if (!empty($ERR)): ?><div class="alert alert-danger"><?= $ERR ?></div><?php endif; ?>
  <?php if (!empty($OK)): ?><div class="alert alert-success"><?= $OK ?></div><?php endif; ?>
  <?php if (!empty($OK) && isset($_SESSION['signup_success_redirect'])): ?>
   <script>
     setTimeout(() => {
       window.location.href = "<?= htmlspecialchars($_SESSION['signup_success_redirect']) ?>";
     }, 2000); // 2 seconds
   </script>
   <?php unset($_SESSION['signup_success_redirect']); ?>
  <?php endif; ?>

  <!--initial buttons -->
    <div id="entryBtns" class="entry-buttons">
      <button class="btn btn-primary" onclick="showLoginForm()">I have an account</button>
      <button class="btn btn-secondary" onclick="showSignupForm()">Create new account</button>
    </div>

  <!-- login form -->
<section class="booking-form">

  <form id="loginForm" class="form-section" method="post" 
      action="login.php<?= isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : '' ?>">   
    <input type="hidden" name="login_check" value="1">

    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>

    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>

    <button class="btn btn-success">Login</button>

    <div class="mt-3">
    <button type="button" class="btn btn-link" onclick="showRecovery('password')">Change Password</button>
    <button type="button" class="btn btn-link" onclick="showRecovery('email')">Change Email</button>
  </div>

  </form>
</section>
   
  <!-- signup form -->
<section class="booking-form">

  <form id="signupForm" class="form-section" method="post"
      action="login.php<?= isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : '' ?>">
    <input type="hidden" name="do_signup" value="1">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" class="form-control" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="form-group">
      <label>Phone</label>
      <input type="number" name="phone" class="form-control" required>
    </div>
    <button class="btn btn-success">Create Account</button>
  </form>
 </section>
  <!-- recovery form -->
    <section class="booking-form">

  <form id="recoveryForm" class="form-section" method="post"
        action="login.php<?= isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : '' ?>">
    <input type="hidden" name="start_recovery" value="1">
    <input type="hidden" id="rec_type" name="rec_type">

    <div class="form-group">
      <label>Username</label>
      <input name="rec_username" class="form-control" required>
    </div>

    <div class="form-group" id="rec_email_field">
      <label>Account Email</label>
      <input type="email" name="rec_email" class="form-control">
    </div>

    <div class="form-group" id="rec_password_field" style="display: none;">
      <label>Current Password</label>
      <input type="password" name="rec_password" class="form-control">
    </div>

    <button class="btn btn-warning" id="recoverySubmit">Verify</button>
  </form>
  </section>

  <section class="booking-form">

    <form id="enterNewEmailForm" class="form-section" method="post"
          action="login.php<?= isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : '' ?>">
      <input type="hidden" name="send_email_code" value="1">

      <div class="form-group">
        <label>New Email</label>
        <input type="email" name="new_email" class="form-control" required>
      </div>

      <button class="btn btn-info">Send Code</button>
    </form>
  </section>


  <!-- set new password form -->
  <section class="booking-form">

    <form id="newPasswordForm" class="form-section" method="post"
          action="login.php<?= isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : '' ?>">
      <input type="hidden" name="set_new_password" value="1">
      <div class="form-group">
        <label>New Password</label>
        <input type="password" name="new_password" class="form-control" required>
      </div>
      <button class="btn btn-success">Update Password</button>
    </form>
  </section>

<!-- verity email change form-->
  <section class="booking-form">

    <form id="verifyForm" class="form-section" method="post"
          action="login.php<?= isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : '' ?>">
      <input type="hidden" name="finish_recovery" value="1">

      <div class="form-group">
        <label>Verification Code</label>
        <input name="verif_code" class="form-control" required>
      </div>

      <button class="btn btn-success">Confirm</button>
    </form>
  </section>
</div>


<script>
  function showRecovery(type) {
  hide('loginForm');
  hide('signupForm');
  document.getElementById('rec_type').value = type;

  if (type === 'password') {
    document.getElementById('rec_email_field').style.display = 'block';
    document.getElementById('rec_password_field').style.display = 'none';
    document.getElementById('recoverySubmit').textContent = 'Verify';
  } else if (type === 'email') {
    document.getElementById('rec_email_field').style.display = 'none';
    document.getElementById('rec_password_field').style.display = 'block';
    document.getElementById('recoverySubmit').textContent = 'Send Code';
  }

  show('recoveryForm');
}

function showVerify(type) {
  hide('recoveryForm');
  hide('enterNewEmailForm');
  if (type === 'password') {
    hide('verifyForm'); show('newPasswordForm');
  } else {
    show('verifyForm');
  }
}

<?php if ($form_to_show === 'recovery'): ?>
  hide('entryBtns'); showRecovery('<?= $_SESSION['recovery']['type'] ?? 'password' ?>');
<?php elseif ($form_to_show === 'enter_new_email'): ?>
  hide('entryBtns'); show('enterNewEmailForm');
<?php elseif ($form_to_show === 'verify_code'): ?>
  hide('entryBtns'); showVerify('<?= $_SESSION['recovery']['type'] ?? 'email' ?>');
<?php elseif ($form_to_show === 'set_new_password'): ?>
  hide('entryBtns'); show('newPasswordForm');
<?php endif; ?>


function show(id) { document.getElementById(id).style.display = 'block'; }
function hide(id) { document.getElementById(id).style.display = 'none'; }
function showLoginForm() {
  hide('entryBtns'); show('loginForm');
}
function showSignupForm() {
  hide('entryBtns'); show('signupForm');
}

<?php if ($form_to_show === 'login'): ?> showLoginForm(); <?php elseif ($form_to_show === 'signup'): ?> showSignupForm(); <?php endif; ?>
</script>
</body>
</html>
