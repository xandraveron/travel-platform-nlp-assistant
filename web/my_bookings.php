<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?return=my_bookings.php");
    exit();
}
require_once __DIR__ . "/connect_db.php";

$ERR = $OK = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    header('Content-Type: application/json');

    $booking_id = intval($_POST['id']);

    $stmt = $conectare->prepare("SELECT b.nr_pers, b.excursion_id, ed.start_date, b.user_id
                                 FROM bookings b
                                 JOIN excursion_details ed ON ed.excursion_id = b.excursion_id
                                 WHERE b.id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        echo json_encode(['error' => 'Booking not found.']);
        exit;
    }

    $now = new DateTime();
    $start = new DateTime($data['start_date']);
    $limit = clone $start;
    $limit->modify('-14 days');

    if ($now > $limit) {
        echo json_encode(['error' => 'Too late to cancel.']);
        exit;
    }
    if ($data['user_id'] != $_SESSION['user_id']) {
        echo json_encode(['error' => 'Unauthorized.']);
        exit;
    }

    $upd = $conectare->prepare("UPDATE excursion_details SET group_size = group_size + ? WHERE excursion_id = ?");
    $upd->bind_param("ii", $data['nr_pers'], $data['excursion_id']);
    $upd->execute();
    $upd->close();

    $del = $conectare->prepare("DELETE FROM bookings WHERE id = ?");
    $del->bind_param("i", $booking_id);
    $del->execute();
    $del->close();

    echo json_encode(['success' => true]);
    exit;
}

//if the user has already logged in, we use session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_POST['do_login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conectare->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $user_id = $user['id'];
        } else {
            $ERR = "Incorrect password.";
        }
    } else {
        $ERR = "Email not found.";
    }
    $stmt->close();
}

// Booking cancellation logic
if (isset($_GET['cancel']) && isset($_SESSION['user_id'])) {
    $booking_id = intval($_GET['cancel']);

    $stmt = $conectare->prepare("SELECT b.nr_pers, b.excursion_id, ed.start_date, b.user_id
                                  FROM bookings b
                                  JOIN excursion_details ed ON ed.excursion_id = b.excursion_id
                                  WHERE b.id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();
    $stmt->close();

    $now = new DateTime();
    $start = new DateTime($data['start_date']);
    $limit = clone $start;
    $limit->modify('-14 days');

    if ($now > $limit) {
        $ERR = "You can't cancel this booking. It's less than 2 weeks before the trip.";
    } elseif ($data['user_id'] != $_SESSION['user_id']) {
        $ERR = "Unauthorized cancelation attempt.";
    } else {
        $upd = $conectare->prepare("UPDATE excursion_details SET group_size = group_size + ? WHERE excursion_id = ?");
        $upd->bind_param("ii", $data['nr_pers'], $data['excursion_id']);
        $upd->execute();
        $upd->close();

        $del = $conectare->prepare("DELETE FROM bookings WHERE id = ?");
        $del->bind_param("i", $booking_id);
        $del->execute();
        $del->close();

        $OK = "Booking canceled successfully.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Bookings</title>
    <link rel="stylesheet" href="travel.css">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="book1">
   <section class="login-container">
      <h2>My Bookings</h2>
      <div id="bookingMessage" class="alert" style="display: none;"></div>

        

    
      <p class="welcome-text">
        Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> |
          <a href="logout.php">Logout</a>
        </p>
      <?php
      $stmt = $conectare->prepare("SELECT b.id AS booking_id, ed.title, ed.start_date, b.nr_pers, b.payment_method
                                  FROM bookings b
                                  JOIN excursions e ON b.excursion_id = e.id
                                  JOIN excursion_details ed ON ed.excursion_id = e.id
                                  WHERE b.user_id = ?");
      $stmt->bind_param("i", $_SESSION['user_id']);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($res->num_rows === 0): ?>
        <div class="no-bookings-message">
          <p>You haven’t made any bookings yet.</p>
          <p><a href="travel.php#home" class="btn btn-primary">Explore Trips</a></p>
        </div>
      <?php else: ?>

  
      <table class="table my-bookings-table">
        <thead>
        <tr>
          <th>Excursion</th>
          <th>Start Date</th>
          <th>Seats</th>
          <th>Payment</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>

      <?php while ($row = $res->fetch_assoc()):
          $start = new DateTime($row['start_date']);
          $deadline = clone $start;
          $deadline->modify('-14 days');
          $can_cancel = (new DateTime()) <= $deadline;
      ?>
        <tr>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td><?= $start->format('Y-m-d') ?></td>
          <td><?= $row['nr_pers'] ?></td>
          <td><?= $row['payment_method'] ?></td>
          <td>
            <?php if ($can_cancel): ?>
              <button class="btn btn-sm btn-danger cancel-btn" data-id="<?= $row['booking_id'] ?>">Cancel</button>
            <?php else: ?>
              <span class="text-muted">Too late to cancel</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; $stmt->close(); ?>
      </tbody>
    </table>
    <?php endif;
?>
  </section>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const messageBox = document.getElementById("bookingMessage");

  document.querySelectorAll(".cancel-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      const bookingId = btn.getAttribute("data-id");
      if (confirm("Cancel this booking?")) {
        fetch("my_bookings.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "id=" + encodeURIComponent(bookingId)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            btn.closest("tr").remove();
            messageBox.className = "alert alert-success";
            messageBox.innerText = "Booking canceled successfully.";
            messageBox.style.display = "block";
          } else {
            messageBox.className = "alert alert-danger";
            messageBox.innerText = data.error || "Failed to cancel booking.";
            messageBox.style.display = "block";
          }
          setTimeout(() => {
            messageBox.style.display = "none";
          }, 6000);
        })
        .catch(err => {
          messageBox.className = "alert alert-danger";
          messageBox.innerText = "Error while connecting to server.";
          messageBox.style.display = "block";
        });
      }
    });
  });
});
</script>

</body>
</html>