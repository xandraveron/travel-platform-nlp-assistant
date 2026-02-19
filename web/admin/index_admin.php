<?php
session_start();
require 'D:/xampp/htdocs/TravelIdeas/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . "/../connect_db.php";

$timeout_duration = 600; 






if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}


if (!isset($_SESSION['loggedin']) || $_SESSION['userType'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['fetch_agency_details'])) {
    $agency_id = intval($_GET['fetch_agency_details']);

    $stmt = $conectare->prepare("SELECT agency_name, username, email, phone FROM agencies WHERE agency_id = ?");
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($agency = $result->fetch_assoc()) {
        echo json_encode(["status" => "success", "agency" => $agency]);
        exit;  
    } else {
        echo json_encode(["status" => "error", "message" => "Agency not found."]);
        exit;  
    }
    $stmt->close();
    exit();
}
if (isset($_GET['fetch_season_details'])) {
    $season_id = intval($_GET['fetch_season_details']);

    $stmt = $conectare->prepare("SELECT title, description, image FROM seasons WHERE id = ?");
    $stmt->bind_param("i", $season_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($season = $result->fetch_assoc()) {
        echo json_encode([
            "status" => "success",
            "season" => $season
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Season not found."
        ]);
    }

    $stmt->close();
    exit();
}


if (isset($_GET['fetch_options'])) {
    try {
        $stmt_agencies = $conectare->prepare("SELECT agency_id, agency_name FROM agencies");
        $stmt_agencies->execute();
        $result_agencies = $stmt_agencies->get_result();
        $agencies = [];
        while ($row = $result_agencies->fetch_assoc()) {
            $agencies[] = $row;
        }
        $stmt_agencies->close();

        $stmt_seasons = $conectare->prepare("SELECT id, title FROM seasons");
        $stmt_seasons->execute();
        $result_seasons = $stmt_seasons->get_result();
        $seasons = [];
        while ($row = $result_seasons->fetch_assoc()) {
            $seasons[] = $row;
        }
        $stmt_seasons->close();

        $stmt_excursions = $conectare->prepare("SELECT id, title FROM excursions");
        $stmt_excursions->execute();
        $result_excursions = $stmt_excursions->get_result();
        $excursions = [];
        while ($row = $result_excursions->fetch_assoc()) {
            $excursions[] = $row;
        }
        $stmt_excursions->close();

        echo json_encode([
            "status" => "success",
            "agencies" => $agencies,
            "seasons" => $seasons,
            "excursions" => $excursions
        ]);
        exit;  
    } catch (Exception $e) {
        error_log("Error fetching data: " . $e->getMessage());
        echo json_encode([
            "status" => "error",
            "message" => "Error fetching data: " . $e->getMessage()
        ]);
        exit;  
    }
    exit();
}








function addAgency($conectare) {
    $agency_name = $_POST['agency_name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conectare->prepare("INSERT INTO agencies (agency_name, username, password, email, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $agency_name, $username, $password, $email, $phone);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Agency added successfully!"]);
        exit;  
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add agency."]);
        exit;  
    }
    $stmt->close();
}

function editAgency($conectare) {
    $agency_id = $_POST['agency_id'];

    $stmt = $conectare->prepare("SELECT agency_name, username, email, phone, password FROM agencies WHERE agency_id = ?");
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $agency = $result->fetch_assoc();
    $stmt->close();

    if (!$agency) {
        echo json_encode(["status" => "error", "message" => "Agency not found."]);
        exit;  
        return;
    }

    $agency_name = $_POST['agency_name'] ?: $agency['agency_name'];
    $username = $_POST['username'] ?: $agency['username'];
    $email = $_POST['email'] ?: $agency['email'];
    $phone = $_POST['phone'] ?: $agency['phone'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    } else {
        $password = $agency['password'];
    }

    $stmt_update = $conectare->prepare("UPDATE agencies SET agency_name = ?, username = ?, password = ?, email = ?, phone = ? WHERE agency_id = ?");
    $stmt_update->bind_param("sssssi", $agency_name, $username, $password, $email, $phone, $agency_id);

    if ($stmt_update->execute()) {
        echo json_encode(["status" => "success", "message" => "Agency updated successfully."]);
        exit;  
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update agency."]);
        exit;  
    }

    $stmt_update->close();
}


function deleteAgency($conectare) {
    $agency_id = $_POST['agency_id'];

    $stmt = $conectare->prepare("DELETE FROM agencies WHERE agency_id = ?");
    $stmt->bind_param("i", $agency_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Agency deleted successfully!"]);
        exit;  
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete agency."]);
        exit;  
    }
    $stmt->close();
}

function addSeason($conectare) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image = $_FILES['image'];

    $target_dir = "../../TravelIdeas/";
    $target_file = $target_dir . basename($image['name']);
    $image_name_db = basename($image['name']);

    $check = getimagesize($image['tmp_name']);
    if ($check !== false && move_uploaded_file($image['tmp_name'], $target_file)) {
        $stmt = $conectare->prepare("INSERT INTO seasons (title, description, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $image_name_db);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Season added successfully!"]);
            exit;  
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add season."]);
            exit;  
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
        exit;  
    }
}

function editSeason($conectare) {
    $season_id = $_POST['season_id'];
    $title = $_POST['title'] ?: null;
    $description = $_POST['description'] ?: null;

    $stmt = $conectare->prepare("SELECT title, description FROM seasons WHERE id = ?");
    $stmt->bind_param("i", $season_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $existing = $res->fetch_assoc();
    $stmt->close();

    $title = $title ?? $existing['title'];
    $description = $description ?? $existing['description'];

    $image = $_FILES['image'];

    if ($image['error'] == UPLOAD_ERR_OK) {
        $imageName = basename($image['name']);
        $imagePath = "../../TravelIdeas/" . $imageName;
        move_uploaded_file($image['tmp_name'], $imagePath);

        $stmt = $conectare->prepare("UPDATE seasons SET title = ?, description = ?, image = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $description, $imageName, $season_id);
    } else {
        $stmt = $conectare->prepare("UPDATE seasons SET title = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $description, $season_id);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Season updated successfully!"]);
        exit;  
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update season."]);
        exit;  
    }
    $stmt->close();
}

function deleteSeason($conectare) {
    $season_id = $_POST['season_id'];

    $stmt = $conectare->prepare("DELETE FROM seasons WHERE id = ?");
    $stmt->bind_param("i", $season_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Season deleted successfully!"]);
        exit;  
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete season."]);
        exit;  
    }
    $stmt->close();
}

function sendBookingConfirmationEmail($user_email, $full_name, $excursion_details, $agency_details) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $agency_details['email']; 
         $mail->Password = 'pvpx amcz xpil djti'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($agency_details['email'], $agency_details['name']);
        $mail->addAddress($user_email);

        $mail->isHTML(true);
        $mail->Subject = 'Booking Confirmation';
        $mail->Body = 'Hello ' . htmlspecialchars($full_name) . ',<br><br>';
        $mail->Body .= 'Thank you for booking with us! Here are your excursion details:<br>';
        $mail->Body .= '<strong>Excursion:</strong> ' . htmlspecialchars($excursion_details['title']) . '<br>';
        $mail->Body .= '<strong>Activities:</strong> ' . htmlspecialchars($excursion_details['activities']) . '<br>';
        $mail->Body .= '<strong>Transportation:</strong> ' . htmlspecialchars($excursion_details['transportation']) . '<br>';
        $mail->Body .= '<strong>Start Date:</strong> ' . htmlspecialchars($excursion_details['start_date']) . '<br>';
        $mail->Body .= '<strong>End Date:</strong> ' . htmlspecialchars($excursion_details['end_date']) . '<br>';
        $mail->Body .= '<br>Best regards,<br>' . htmlspecialchars($agency_details['name']);
        $mail->send();
    } catch (Exception $e) {
        return false;
    }
    return true;
}



function getLoggedInAdminEmail($conectare) {
    if (!isset($_SESSION['username']) || $_SESSION['userType'] !== 'admin') {
        return null;
    }

    $stmt = $conectare->prepare("SELECT email, username FROM users WHERE username = ? AND user_type = 'admin'");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    return $admin ?: null;
}



function sendReplyToUser($admin_email, $admin_username, $user_email, $user_name, $reply_text) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $admin_email; 
        $mail->Password = 'pvpx amcz xpil djti'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($admin_email, $admin_username); 
        $mail->addAddress($user_email, $user_name);

        $mail->isHTML(true);
        $mail->Subject = 'Reply to your message at TravelIdeas';
        $mail->Body  = 'Hi ' . htmlspecialchars($user_name) . ',<br><br>';
        $mail->Body .= nl2br(htmlspecialchars($reply_text));
        $mail->Body .= '<br><br>Best regards,<br>TravelIdeas Administration Team';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Email error: ' . $mail->ErrorInfo);
        echo "<pre>PHPMailer error: " . $mail->ErrorInfo . "</pre>";
        return false;
    }
}


function replyMessage($conectare) {
    $user_email = $_POST['reply_email'];
    $user_name = $_POST['reply_name'];
    $reply_text = trim($_POST['reply_text']);
    $message_id = intval($_POST['reply_message_id']);

    $admin = getLoggedInAdminEmail($conectare);

    if ($admin && !empty($user_email) && !empty($reply_text)) {
        $sent = sendReplyToUser(
            $admin['email'],
            $admin['username'],
            $user_email,
            $user_name,
            $reply_text
        );

        if ($sent) {
            // Update message status and admin_id
            $stmt = $conectare->prepare("UPDATE messages SET action = 'replied', admin_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $_SESSION['user_id'], $message_id);
            $stmt->execute();
            $stmt->close();

            echo json_encode(["status" => "success", "message" => "Reply sent and message status updated."]);
            exit;
        } else {
            echo json_encode(["status" => "error", "message" => "Email not sent."]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Missing info."]);
        exit;
    }
}


function deleteMessage($conectare) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conectare->prepare("UPDATE messages SET action = 'deleted', admin_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $delete_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Message marked as deleted."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update message status."]);
    }
    $stmt->close();
    exit;
}


if (!isset($_SESSION['user_id'])) {
    $stmt = $conectare->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $stmt->bind_result($admin_id);
    if ($stmt->fetch()) {
        $_SESSION['user_id'] = $admin_id;
    }
    $stmt->close();
}




function makeBooking($conectare) {
    $excursion_id   = $_POST['excursion_id'];
    $full_name      = $_POST['full_name'];
    $payment_method = $_POST['payment_method'];
    $user_email     = $_POST['user_email'];
    $username       = $_POST['username'];
    $password_plain = $_POST['password'];
    $phone          = $_POST['phone'];
    $pers           = intval($_POST['nr_pers']);

    $check = $conectare->prepare("SELECT id, password, email, user_type FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->bind_result($user_id, $hashed_password, $existing_email, $user_type);
        $check->fetch();

        if (!password_verify($password_plain, $hashed_password)) {
            echo json_encode(["status" => "error", "message" => "Incorrect password."]);
            exit;
        }

        $user_email = $existing_email;

    } else {
        $hashed = password_hash($password_plain, PASSWORD_DEFAULT);
        $user_type = 'user';

        $ins = $conectare->prepare("INSERT INTO users (username, password, email, phone, user_type) VALUES (?, ?, ?, ?, ?)");
        $ins->bind_param("sssss", $username, $hashed, $user_email, $phone, $user_type);
        if (!$ins->execute()) {
            echo json_encode(["status" => "error", "message" => "Failed to create new user."]);
            exit;
        }
        $user_id = $ins->insert_id;
        $ins->close();
    }
    $check->close();

    $stmt_excursion = $conectare->prepare("
        SELECT ed.title, ed.description, ed.activities, ed.transportation, ed.start_date, ed.end_date, ed.price,ed.group_size,
               a.agency_name, a.email AS agency_email
        FROM excursion_details ed
        JOIN excursions e ON ed.excursion_id = e.id
        JOIN agencies a ON e.travel_agency_id = a.agency_id
        WHERE ed.excursion_id = ?
    ");
    $stmt_excursion->bind_param("i", $excursion_id);
    $stmt_excursion->execute();
    $excursion_details = $stmt_excursion->get_result()->fetch_assoc();
    $stmt_excursion->close();

    $stmt_capacity = $conectare->prepare("
        SELECT IFNULL(SUM(no_persons), 0) as booked
        FROM bookings
        WHERE excursion_id = ?
    ");
    $stmt_capacity->bind_param("i", $excursion_id);
    $stmt_capacity->execute();
    $stmt_capacity->bind_result($booked);
    $stmt_capacity->fetch();
    $stmt_capacity->close();

    $available = $excursion_details['group_size'] - $booked;

    if ($pers > $available) {
        echo json_encode(["status" => "error", "message" => "Only $available seat(s) left."]);
        exit;
    }

    $stmt_booking = $conectare->prepare("
        INSERT INTO bookings (excursion_id, user_id, full_name, payment_method, no_persons)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt_booking->bind_param("iissi", $excursion_id, $user_id, $full_name, $payment_method, $pers);

    if ($stmt_booking->execute()) {
        $agency_details = [
            'name'  => $excursion_details['agency_name'],
            'email' => $excursion_details['agency_email']
        ];
        sendBookingConfirmationEmail($user_email, $full_name, $excursion_details, $agency_details);
        echo json_encode(["status" => "success", "message" => "Booking successful for $pers people."]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Booking failed."]);
        exit;
    }
    $stmt_booking->close();
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_agency'])) {
        addAgency($conectare);
    } elseif (isset($_POST['update_agency'])) {
        editAgency($conectare);
    } elseif (isset($_POST['delete_agency'])) {
        deleteAgency($conectare);
    } elseif (isset($_POST['add_season'])) {
        addSeason($conectare);
    } elseif (isset($_POST['update_season'])) {
        editSeason($conectare);
    } elseif (isset($_POST['delete_season'])) {
        deleteSeason($conectare);
    } elseif (isset($_POST['make_booking'])) {
        makeBooking($conectare);
    } elseif (isset($_POST['reply_message'])) {
        replyMessage($conectare);
    } elseif (isset($_POST['delete_message'])) {
        deleteMessage($conectare);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="index_admin.css">
    <script>
        function showSection(sectionId) {
            var sections = document.getElementsByClassName("section");
            for (var i = 0; i < sections.length; i++) {
                sections[i].style.display = "none";
            }
            document.getElementById(sectionId).style.display = "block";
        }

        function loadAgencyDetails() {
            const agencyId = document.getElementById('edit_agency_id').value;

            var xhr = new XMLHttpRequest();
            xhr.open("GET", "index_admin.php?fetch_agency_details=" + agencyId, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === "success") {
                        const agency = response.agency;
                        document.getElementById('edit_agency_name').value = agency.agency_name;
                        document.getElementById('edit_username').value = agency.username;
                        document.getElementById('edit_email').value = agency.email;
                        document.getElementById('edit_phone').value = agency.phone;
                        document.getElementById('edit_password').value = ""; // Empty password field for security
                    } else {
                        console.error(response.message);
                    }
                } else {
                    console.error("Error fetching agency details.");
                }
            };
            xhr.send();
        }

        function handleFormSubmit(event, action) {
        event.preventDefault();
        const formData = new FormData(event.target);
        formData.append(action, true);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "index_admin.php", true);

        xhr.onload = function () {
        let response;
        try {
            response = JSON.parse(xhr.responseText);
        } catch (e) {
            console.error("Parse error:", e);
            console.error("Response text:", xhr.responseText);
            return;
        }

        const messageBox = document.getElementById("adminMessage");
        if (messageBox) {
            messageBox.innerText = response.message || "No message returned";
            messageBox.className = "alert " + (response.status === "success" ? "alert-success" : "alert-danger");
            messageBox.style.display = "block";
            setTimeout(() => { messageBox.style.display = "none"; }, 8000);
        }

        if (action === 'delete_message' && response.status === 'success') {
            sessionStorage.setItem('adminMessageText', response.message);
            sessionStorage.setItem('adminMessageStatus', response.status);
            location.reload();
            return;
        }

        if (action === 'reply_message' && response.status === 'success') {
            location.reload();
            return;
        }

        if (response.status === "success") {
            event.target.reset();
            updateFormOptions();
        }
    };

    xhr.send(formData);
}




       function updateFormOptions() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "index_admin.php?fetch_options=true", true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.status === "success") {
                            var agencies = data.agencies;
                            var seasons = data.seasons;
                            var excursions = data.excursions;

                            var editAgencySelect = document.getElementById('edit_agency_id');
                            var deleteAgencySelect = document.getElementById('delete_agency_id');
                            var editSeasonSelect = document.getElementById('edit_season_id');
                            var deleteSeasonSelect = document.getElementById('delete_season_id');
                            var bookingExcursionSelect = document.getElementById('booking_excursion_id');

                            editAgencySelect.innerHTML = '';
                            deleteAgencySelect.innerHTML = '';
                            editSeasonSelect.innerHTML = '';
                            deleteSeasonSelect.innerHTML = '';
                            bookingExcursionSelect.innerHTML = '';

                            agencies.forEach(function(agency) {
                                var option = new Option(agency.agency_name, agency.agency_id);
                                editAgencySelect.add(option.cloneNode(true));
                                deleteAgencySelect.add(option.cloneNode(true));
                            });

                            seasons.forEach(function(season) {
                                var option = new Option(season.title, season.id);
                                editSeasonSelect.add(option.cloneNode(true));
                                deleteSeasonSelect.add(option.cloneNode(true));
                            });

                            excursions.forEach(function(excursion) {
                                var option = new Option(excursion.title, excursion.id);
                                bookingExcursionSelect.add(option.cloneNode(true));
                            });
                        } else {
                            //alert("Failed to fetch options: " + data.message);
                        }
                    } catch (e) {
                        console.error("Parsing error:", e);
                        console.error("Response text:", xhr.responseText);
                        //alert("An error occurred while fetching options. Please try again.");
                    }
                } else {
                    //alert("Error: " + xhr.statusText);
                }
            };
            xhr.send();
        }

   document.addEventListener("DOMContentLoaded", function () {
    updateFormOptions();
    // Restore adminMessage if we just deleted a message:
    const msgText = sessionStorage.getItem('adminMessageText');
    const msgStatus = sessionStorage.getItem('adminMessageStatus');
    if (msgText && msgStatus) {
        const messageBox = document.getElementById("adminMessage");
        messageBox.innerText = msgText;
        messageBox.className = "alert " + (msgStatus === "success" ? "alert-success" : "alert-danger");
        messageBox.style.display = "block";
        setTimeout(() => { messageBox.style.display = "none"; }, 8000);

        sessionStorage.removeItem('adminMessageText');
        sessionStorage.removeItem('adminMessageStatus');
    }
    document.getElementById("edit_season_id").addEventListener("change", function () {
        const destId = this.value;
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "index_admin.php?fetch_season_details=" + destId, true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp.status === "success") {
                        const d = resp.season;
                        document.getElementById("edit_title").value = d.title;
                        document.getElementById("edit_description").value = d.description;

                        const img = document.getElementById("edit_image_preview");
                        if (d.image) {
                            img.src = "../../TravelIdeas/" + encodeURIComponent(d.image);
                            img.style.display = "block";
                        } else {
                            img.style.display = "none";
                        }
                    } else {
                        console.error(resp.message);
                    }
                } catch (e) {
                    console.error("Parse error:", e);
                }
            }
        };
        xhr.send();
    });
});



    </script>
</head>
<body>
    <div class="container mt-4">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

        <div id="adminMessage" class="alert" style="display:none;"></div>

        <div class="buttons">
            <button class="btn btn-primary" onclick="showSection('addAgencySection')">Add Agency</button>
            <button class="btn btn-primary" onclick="showSection('editAgencySection')">Edit Agency</button>
            <button class="btn btn-primary" onclick="showSection('deleteAgencySection')">Delete Agency</button>
            <button class="btn btn-primary" onclick="showSection('addSeasonSection')">Add Season</button>
            <button class="btn btn-primary" onclick="showSection('editSeasonSection')">Edit Season</button>
            <button class="btn btn-primary" onclick="showSection('deleteSeasonSection')">Delete Season</button>
            <button class="btn btn-primary" onclick="showSection('makeBookingSection')">Make Booking</button>
            <button class="btn btn-primary" onclick="showSection('viewMessagesSection')">View Messages</button>
            <button id="logoutBtn" class="btn btn-secondary">Logout</button>

            
        </div>

        <div id="addAgencySection" class="section" style="display:none;">
            <h3>Add New Agency</h3>
            <form method="POST" onsubmit="handleFormSubmit(event, 'add_agency')">
                <div class="form-group">
                    <label for="agency_name">Agency Name</label>
                    <input type="text" class="form-control" id="agency_name" name="agency_name" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>
                <button type="submit" class="btn btn-success">Add Agency</button>
            </form>
        </div>

        <div id="editAgencySection" class="section" style="display:none;">
            <h3>Edit Agency</h3>
            <form method="POST" onsubmit="handleFormSubmit(event, 'update_agency')">
                <div class="form-group">
                    <label for="edit_agency_id">Select Agency</label>
                    <select class="form-control" id="edit_agency_id" name="agency_id" required onchange="loadAgencyDetails()">
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_agency_name">New Agency Name</label>
                    <input type="text" class="form-control" id="edit_agency_name" name="agency_name">
                </div>
                <div class="form-group">
                    <label for="edit_username">New Username</label>
                    <input type="text" class="form-control" id="edit_username" name="username">
                </div>
                <div class="form-group">
                    <label for="edit_password">New Password (leave blank to keep current)</label>
                    <input type="password" class="form-control" id="edit_password" name="password">
                </div>
                <div class="form-group">
                    <label for="edit_email">New Email</label>
                    <input type="email" class="form-control" id="edit_email" name="email">
                </div>
                <div class="form-group">
                    <label for="edit_phone">New Phone</label>
                    <input type="text" class="form-control" id="edit_phone" name="phone">
                </div>
                <button type="submit" class="btn btn-warning">Update Agency</button>
            </form>
        </div>


        <div id="deleteAgencySection" class="section" style="display:none;">
            <h3>Delete Agency</h3>
            <form method="POST" onsubmit="handleFormSubmit(event, 'delete_agency')">
                <div class="form-group">
                    <label for="delete_agency_id">Select Agency</label>
                    <select class="form-control" id="delete_agency_id" name="agency_id" required>
                    </select>
                </div>
                <button type="submit" class="btn btn-danger">Delete Agency</button>
            </form>
        </div>

        <div id="addSeasonSection" class="section" style="display:none;">
            <h3>Add New Season</h3>
            <form method="POST" enctype="multipart/form-data" onsubmit="handleFormSubmit(event, 'add_season')">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" class="form-control" id="image" name="image" required>
                </div>
                <button type="submit" class="btn btn-success">Add Season</button>
            </form>
        </div>

        <div id="editSeasonSection" class="section" style="display:none;">
            <h3>Edit Season</h3>
            <form method="POST" enctype="multipart/form-data" onsubmit="handleFormSubmit(event, 'update_season')">
                <div class="form-group">
                    <label for="edit_season_id">Select Season</label>
                    <select class="form-control" id="edit_season_id" name="season_id" required>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_title">New Title</label>
                    <input type="text" class="form-control" id="edit_title" name="title">
                </div>
                <div class="form-group">
                    <label for="edit_description">New Description</label>
                    <textarea class="form-control" id="edit_description" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_image">New Image</label>
                    <img id="edit_image_preview" src="" alt="Current Image" style="max-height: 150px; display: none; border: 1px solid #ccc; padding: 5px;">
                    <input type="file" class="form-control" id="edit_image" name="image">

                </div>
                <button type="submit" class="btn btn-warning">Update Season</button>
            </form>
        </div>

        <div id="deleteSeasonSection" class="section" style="display:none;">
            <h3>Delete Season</h3>
            <form method="POST" onsubmit="handleFormSubmit(event, 'delete_season')">
                <div class="form-group">
                    <label for="delete_season_id">Select Season</label>
                    <select class="form-control" id="delete_season_id" name="season_id" required>
                    </select>
                </div>
                <button type="submit" class="btn btn-danger">Delete Season</button>
            </form>
        </div>

        <div id="makeBookingSection" class="section" style="display:none;">
            <h3>Make a Booking</h3>
            <div id="bookingMessage" class="alert" style="display: none;"></div>

            <form method="POST" onsubmit="handleFormSubmit(event, 'make_booking')">
            <div class="form-group">
                <label for="booking_excursion_id">Excursion</label>
                <select class="form-control" id="booking_excursion_id" name="excursion_id" required>
                </select>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="booking_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="booking_password" name="password" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" class="form-control" id="booking_phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" class="form-control" id="booking_full_name" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="nr_pers">Number of People</label>
                <input type="number" class="form-control" id="nr_pers" name="nr_pers" min="1" required>
            </div>
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select class="form-control" id="payment_method" name="payment_method" required>
                    <option value="credit_card">Credit Card</option>
                    <option value="paypal">PayPal</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>
            <div class="form-group">
                <label for="user_email">User Email</label>
                <input type="email" class="form-control" id="user_email" name="user_email" required>
            </div>
            <button type="submit" class="btn btn-success">Submit Booking</button>
            </form>
        </div>
        <div id="viewMessagesSection" class="section" style="display:none;">
            <h3>User Messages</h3>
            <table class="messages-table table table-bordered">
                <thead>
                    <tr>
                        <th>#</th><th>Name</th><th>Email</th><th>Message</th><th>Admin</th><th>Date</th><th>Status</th><th>Reply</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    <?php
    $result = $conectare->query("
        SELECT m.*, u.username AS admin_username 
        FROM messages m
        LEFT JOIN users u ON m.admin_id = u.id
        WHERE m.action IN ('pending', 'replied')
        ORDER BY m.submitted_at DESC
    ");

    if (!$result) {
        die("Query error: " . $conectare->error);
    }

    if ($result->num_rows === 0): ?>
        <tr>
            <td colspan="8" class="text-center text-muted">No messages to display.</td>
        </tr>
    <?php else:
        while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                <td><?= htmlspecialchars($row['admin_username'] ?? '—') ?></td>
                <td><?= $row['submitted_at'] ?></td>
                <td><?= ucfirst($row['action']) ?></td>
                <td>
                    <?php if ($row['action'] === 'pending'): ?>
                        <form method="post" onsubmit="handleFormSubmit(event, 'reply_message')" style="margin-bottom:10px;">
                        <input type="hidden" name="reply_message" value="1">
                            <input type="hidden" name="reply_message_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="reply_email" value="<?= htmlspecialchars($row['email']) ?>">
                            <input type="hidden" name="reply_name" value="<?= htmlspecialchars($row['name']) ?>">
                            <textarea name="reply_text" class="form-control mb-1" placeholder="Reply..." rows="2" required></textarea>
                        <?php elseif ($row['action'] === 'replied'): ?>
                            <span class="text-muted">Already replied</span>
                        <?php else: ?>
                            <em>No reply action</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['action'] === 'pending'): ?>
                                <button type="submit" class="btn btn-success btn-sm mt-1">Send Reply</button>
                            </form>
                            <form method="post" onsubmit="handleFormSubmit(event, 'delete_message')" style="display:inline-block; margin-top:5px;">
                            <input type="hidden" name="delete_message" value="1">
                                <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        <?php elseif ($row['action'] === 'replied'): ?>
                            <form method="post" onsubmit="handleFormSubmit(event, 'delete_message')" style="display:inline-block; margin-top:5px;">
                                <input type="hidden" name="delete_message" value="1">
                                <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>

                        <?php else: ?>
                            <em>No actions</em>
                        <?php endif; ?>
                    </td>
                </tr>
        <?php endwhile;
    endif;
    ?>
</tbody>

            </table>
        </div>


    </div>
    <script>
document.getElementById('logoutBtn').addEventListener('click', function () {
    fetch('index_admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'logout=1'
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            location.reload();  // fallback
        }
    });
});
</script>

</body>
</html>
 
