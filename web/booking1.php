<?php
session_start();

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . "/connect_db.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conectare->set_charset('utf8mb4');




function sendConfirm($d, $agency, $to, $name, $pers) {
    $m = new PHPMailer(true);
    try {
        $m->isSMTP();
        $m->Host       = 'smtp.gmail.com';
        $m->SMTPAuth   = true;
        $m->Username   = $agency['email'];
        $m->Password   = 'pvpx amcz xpil djti';
        $m->SMTPSecure = 'tls';
        $m->Port       = 587;
        $m->setFrom($agency['email'], $agency['name']);
        $m->addAddress($to);
        $m->isHTML(true);
        $m->Subject = 'Booking confirmation';
        $m->Body = "Hello $name,<br>"
                    . "You booked <b>$pers</b> seat(s) for <b>{$d['title']}</b><br>"
                    . "Start: {$d['start_date']} – End: {$d['end_date']}<br>"
                    . "Thank you!";
        $m->send();
    } catch (Exception $e) {
         error_log("Email error: " . $m->ErrorInfo);
    }
    if ($m->send()) {
    error_log("Confirmation email sent to $to");
}
}


$timeout = 90; // seconds

$ERR = $_SESSION['ERR'] ?? '';
$OK  = $_SESSION['OK'] ?? '';
$form_to_show = $_SESSION['form_to_show'] ?? '';
unset($_SESSION['ERR'], $_SESSION['OK'], $_SESSION['form_to_show']);


if (isset($_SESSION['LAST']) && time() - $_SESSION['LAST'] > $timeout) {
    session_unset();
    session_destroy();
}
$_SESSION['LAST'] = time();


$excursion_id = intval($_GET['id'] ?? 0);
if (!$excursion_id) {
    die("Missing excursion id");
}

$stmt = $conectare->prepare(
    "SELECT 
        ed.*,
        e.travel_agency_id,
        a.agency_name,
        a.email AS agency_email
     FROM excursion_details ed
     JOIN excursions e ON ed.excursion_id = e.id
     JOIN agencies a ON e.travel_agency_id = a.agency_id
     WHERE ed.excursion_id = ?"
);
$stmt->bind_param("i", $excursion_id);
$stmt->execute();
$excursion_details = $stmt->get_result()->fetch_assoc();
$stmt->close();

// now pull agency_name out into its own variable for clarity:
$agency_name = $excursion_details['agency_name'] ?? '—';


if (isset($_POST['make_booking']) && isset($_SESSION['user_id'])) {
    $uid   = $_SESSION['user_id'];
    $full  = trim($_POST['full_name']);
    $pay   = $_POST['payment_method'];
    $pers  = intval($_POST['seats']);

    // capacity check
    $cap = $conectare->prepare("
        SELECT group_size
        FROM excursion_details
        WHERE excursion_id=?
    ");
    $cap->bind_param('i', $excursion_id);
    $cap->execute();
    $cap->bind_result($slots);
    $cap->fetch();
    $cap->close();

    if ($pers > $slots) {
        $_SESSION['ERR'] = "Only $slots seat(s) left";
        $_SESSION['form_to_show'] = 'booking';
    } else {
        // insert booking
        $ins = $conectare->prepare("
            INSERT INTO bookings(excursion_id, user_id, full_name, nr_pers, payment_method)
            VALUES (?, ?, ?, ?, ?)
        ");
        $ins->bind_param('iisss', $excursion_id, $uid, $full, $pers, $pay);
        if ($ins->execute()) {
            $upd = $conectare->prepare("
                UPDATE excursion_details
                SET group_size = group_size - ?
                WHERE excursion_id=?
            ");
            $upd->bind_param('ii', $pers, $excursion_id);
            $upd->execute();
            $upd->close();

            $_SESSION['OK'] = "Booked $pers seat(s) successfully!";
            $_SESSION['form_to_show'] = 'booking';

            //  Send confirmation email
            sendConfirm($excursion_details, [
                'name'  => $agency_name,
                'email' => $excursion_details['agency_email']
            ], $_SESSION['email'], $full, $pers);
            error_log("Booking INSERTED for user $uid, pers: $pers, excursion $excursion_id");

        } else {
            $_SESSION['ERR'] = "Booking failed, please try again";
            $_SESSION['form_to_show'] = 'booking';
        }
        $ins->close();
    }

    //  Final redirect
    header("Location: booking1.php?id=$excursion_id#bookingSection");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Booking</title>
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">

  <link rel="stylesheet" type="text/css" href="travel.css">
    
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet"
    href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script src="travel.js"></script>

</head>
<body class="book1">
<header>
    <div class="nav-bar">
        <a href="travel.php" class="logo"><img src="flight .png" alt="Flight Logo"></a>
        
        <div class="navigation">
            <div class="nav-items">
                <i class="uil uil-times nav-close-btn"></i>
                <a href="travel.php#home"><i class="uil uil-estate"></i>Home</a>
                <a href="travel.php#AboutUs"><i class="uil uil-info-circle"></i>About</a>
                <a href="travel.php#contact"><i class="uil uil-envelope"></i>Contact</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="my_bookings.php">My bookings</a>
                <a href="logout.php" onclick="return confirm('Are you sure you want to log out?')">Log out</a>
                <?php else: ?>
                <a href="login.php">Log in</a>
                <?php endif; ?>            
            </div>
        </div>
        <div class="Media">
            <a href="https://www.facebook.com"><i class="uil uil-facebook-messenger-alt"></i></a>
            <a href="https://twitter.com"><i class="uil uil-twitter-alt"></i></a>
            <a href="https://instagram.com"><i class="uil uil-instagram"></i></a>
        </div>
        <i class="uil uil-apps nav-menu-btn"></i>
    </div>
  
</header>

<section class="descript">
      <div class="excursion-description">

    <?php if ($excursion_details): ?>
    <h2><?= htmlspecialchars($excursion_details['title']) ?></h2>
    
      <?= nl2br(htmlspecialchars($excursion_details['description'])) ?>
    </div> 

    <div class="excursion-info">
      <div class="grid-row">
        <span class="grid-label">Itinerary:</span>
        <span class="grid-value"><?= nl2br(htmlspecialchars($excursion_details['itinerary'])) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Activities:</span>
        <span class="grid-value"><?= nl2br(htmlspecialchars($excursion_details['activities'])) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Transportation:</span>
        <span class="grid-value"><?= nl2br(htmlspecialchars($excursion_details['transportation'])) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Start Date:</span>
        <span class="grid-value"><?= htmlspecialchars($excursion_details['start_date']) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">End Date:</span>
        <span class="grid-value"><?= htmlspecialchars($excursion_details['end_date']) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Price:</span>
        <span class="grid-value"><?= htmlspecialchars($excursion_details['price']) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Hotel Name:</span>
        <span class="grid-value"><?= htmlspecialchars($excursion_details['hotel_name']) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Hotel Rating:</span>
        <span class="grid-value"><?= htmlspecialchars($excursion_details['hotel_rating']) ?>/5</span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Hotel Description:</span>
        <span class="grid-value"><?= nl2br(htmlspecialchars($excursion_details['hotel_description'])) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Meals Included:</span>
        <span class="grid-value"><?= htmlspecialchars($excursion_details['meals_included']) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Seats Available:</span>
        <span class="grid-value"><?= htmlspecialchars($excursion_details['group_size']) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Hotel Link:</span>
          <a href="<?= htmlspecialchars($excursion_details['hotel_link']) ?>" target="_blank">View Hotel</a>
      </div>

      <div class="grid-row">
        <span class="grid-label">Weather Info:</span>
        <span class="grid-value"><?= nl2br(htmlspecialchars($excursion_details['weather_info'])) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Travel Tips:</span>
        <span class="grid-value"><?= nl2br(htmlspecialchars($excursion_details['travel_tips'])) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Culture Info:</span>
        <span class="grid-value"><?= nl2br(htmlspecialchars($excursion_details['culture_info'])) ?></span>
      </div>

      <div class="grid-row">
        <span class="grid-label">Hosted by:</span>
        <span class="grid-value"><?= htmlspecialchars($agency_name) ?></span>
      </div>
    </div>
  <?php endif; ?>
</section>



<section class="carousel-section">
    <div id="carouselExampleIndicators" class="carousel slide my-4" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php for ($i = 0; $i < 5; $i++): ?>
                <li data-target="#carouselExampleIndicators" data-slide-to="<?php echo $i; ?>" class="<?php echo $i == 0 ? 'active' : ''; ?>"></li>
            <?php endfor; ?>
        </ol>
        <div class="carousel-inner">
            <?php 
            $images = ['image1_url', 'image2_url', 'image3_url', 'image4_url', 'image5_url'];
            $active = 'active';
            foreach ($images as $image): 
                if ($excursion_details[$image]): ?>
                    <div class="carousel-item <?php echo $active; ?>">
                        <img class="d-block w-100" src="/TravelIdeas/images/<?php echo htmlspecialchars($excursion_details[$image]); ?>"
" alt="<?php echo ucfirst(str_replace('_', ' ', $image)); ?>">
                    </div>
                <?php 
                $active = ''; 
                endif; 
            endforeach; 
            ?>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
</section>


<section class="booking-form">
      <div class="form-section">
        <h2 id="bookingSection">Book Your Seat</h2>

        <?php if (!empty($OK)): ?>
            <div class="alert alert-success"><?= $OK ?></div>
        <?php endif; ?>

        <?php if (!empty($ERR)): ?>
            <div class="alert alert-danger"><?= $ERR ?></div>
        <?php endif; ?>

            <?php
            $return_url = "booking1.php?id=$excursion_id#bookingSection";
            if (!isset($_SESSION['existing'])):
            ?>
            <p class="text-you-need-to-login">
            Please 
            <a href="login.php?return=<?= urlencode($return_url) ?>" class="custom-link">log in</a> 
            or 
            <a href="login.php?return=<?= urlencode($return_url) ?>" class="custom-link">create an account</a> 
            to book this trip.
            </p>
        <?php else: ?>
        <form method="post" action="booking1.php?id=<?= $excursion_id ?>#bookingSection">
          <input type="hidden" name="make_booking" value="1">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" class="form-control w-100" required>
          </div>
          <div class="form-group">
            <label>Number of persons booking</label>
            <input type="number" name="seats" class="form-control w-100" min="1" required>
          </div>
          <div class="form-group">
            <label>Payment Method</label>
            <select name="payment_method" class="form-control w-100" required>
              <option value="credit_card">Credit card</option>
              <option value="paypal">PayPal</option>
              <option value="bank_transfer">Bank transfer</option>
            </select>
          </div>
          <button class="btn btn-primary">Book Now</button>
        </form>
  <?php endif; ?>
  </div>
</section>

  <footer class="footer">
    <img src="flight .png" alt="Flight Logo">
    <p>2025. Design and implementation by Alexandra-Veronica Dobra. All rights reserved.</p>
  </footer>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


</body>
</html>
