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

$agency_id = $_SESSION['agency_id'] ?? null;
if (!$agency_id) {
    header("Location: login.php");
    exit();
}

function hasDetails($conectare, $excursion_id) {
    $stmt = $conectare->prepare("SELECT COUNT(*) FROM excursion_details WHERE excursion_id = ?");
    $stmt->bind_param("i", $excursion_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

if (isset($_GET['fetch_options'])) {
    try {
        ob_start();
        $stmt = $conectare->prepare("SELECT id, title FROM excursions WHERE travel_agency_id = ?");
        $stmt->bind_param("i", $agency_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $excursions = [];
        while ($row = $result->fetch_assoc()) {
            $excursions[] = $row;
        }
        $stmt->close();

        $stmt_destinations = $conectare->prepare("SELECT id, title FROM seasons");
        $stmt_destinations->execute();
        $result_destinations = $stmt_destinations->get_result();
        $seasons = [];
        while ($row = $result_destinations->fetch_assoc()) {
            $seasons[] = $row;
        }
        $stmt_destinations->close();

        ob_end_clean();
        echo json_encode([
            "status" => "success",
            "excursions" => $excursions,
            "seasons" => $seasons
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


if (isset($_GET['excursion_id']) && isset($_GET['details_type'])) {
    ob_start();
    $excursion_id = intval($_GET['excursion_id']);
    $details_type = $_GET['details_type'];

    if ($details_type === 'excursion') {
        $stmt = $conectare->prepare("SELECT * FROM excursions WHERE id = ?");
        $stmt->bind_param("i", $excursion_id);
    } else if ($details_type === 'excursion_details') {
        $stmt = $conectare->prepare("SELECT * FROM excursion_details WHERE excursion_id = ?");
        $stmt->bind_param("i", $excursion_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        ob_end_clean();
        echo json_encode(["status" => "success", "details" => $row]);
        exit;
    } else {
        ob_end_clean();
        echo json_encode(["status" => "error", "message" => ucfirst(str_replace('_', ' ', $details_type)) . " not found."]);
        exit;
    }
    $stmt->close();
    
}

$stmt_agency = $conectare->prepare("SELECT agency_name FROM agencies WHERE agency_id = ?");
$stmt_agency->bind_param("i", $agency_id);
$stmt_agency->execute();
$stmt_agency->bind_result($agency_name);
$stmt_agency->fetch();
$stmt_agency->close();

$seasons = [];
$stmt_destinations = $conectare->prepare("SELECT id, title FROM seasons");
$stmt_destinations->execute();
$result_destinations = $stmt_destinations->get_result();
while ($row = $result_destinations->fetch_assoc()) {
    $seasons[] = $row;
}
$stmt_destinations->close();

$excursions = [];
$stmt_excursions = $conectare->prepare("SELECT id, title, description FROM excursions WHERE travel_agency_id = ?");
$stmt_excursions->bind_param("i", $agency_id);
$stmt_excursions->execute();
$result_excursions = $stmt_excursions->get_result();
while ($row = $result_excursions->fetch_assoc()) {
    $excursions[] = $row;
}
$stmt_excursions->close();

function addExcursion($conectare, $agency_id) {
    $season_id = $_POST['season_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image = $_FILES['image'];

    $target_dir = "../../TravelIdeas/";
    $target_file = $target_dir . basename($image['name']);
    $image_name_db = basename($image['name']);

    $check = getimagesize($image['tmp_name']);
    if ($check !== false && move_uploaded_file($image['tmp_name'], $target_file)) {
        $stmt_add = $conectare->prepare("INSERT INTO excursions (season_id, travel_agency_id, title, description, image) VALUES (?, ?, ?, ?, ?)");
        $stmt_add->bind_param("iisss", $season_id, $agency_id, $title, $description, $image_name_db);
        if ($stmt_add->execute()) {
            echo json_encode(["status" => "success", "message" => "Excursion added successfully!"]);
            exit;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to insert into database."]);
            exit;
        }
        $stmt_add->close();
    } else {
        echo json_encode(["status" => "error", "message" => "There was an error uploading the image or file is not an image."]);
        exit;
    }
}

function editExcursion($conectare, $agency_id) {
    if (!isset($_POST['excursion_id'])) {
        echo json_encode(["status" => "error", "message" => "Excursion ID is missing."]);
        exit;
    }

    $id = $_POST['excursion_id'];
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $image = $_FILES['image'] ?? null;

    if ($image && $image['error'] == UPLOAD_ERR_OK) {
        $imageName = basename($image['name']);
        $imagePath = "../../TravelIdeas/" . $imageName;
        $imageFileType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        $check = getimagesize($image['tmp_name']);
        if ($check !== false && in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            if (move_uploaded_file($image['tmp_name'], $imagePath)) {
                $stmt_update = $conectare->prepare("UPDATE excursions SET title = ?, description = ?, image = ? WHERE id = ? AND travel_agency_id = ?");
                $stmt_update->bind_param("sssii", $title, $description, $imageName, $id, $agency_id);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
                exit;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid image file."]);
            exit;
        }
    } else {
        $stmt_update = $conectare->prepare("UPDATE excursions SET title = ?, description = ? WHERE id = ? AND travel_agency_id = ?");
        $stmt_update->bind_param("ssii", $title, $description, $id, $agency_id);
    }

    if ($stmt_update->execute()) {
        echo json_encode(["status" => "success", "message" => "Excursion updated successfully."]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt_update->error]);
        exit;
    }
    $stmt_update->close();
}

function deleteExcursion($conectare, $agency_id) {
    $id = $_POST['excursion_id'];

    $stmt_delete = $conectare->prepare("DELETE FROM excursions WHERE id = ? AND travel_agency_id = ?");
    $stmt_delete->bind_param("ii", $id, $agency_id);
    if ($stmt_delete->execute()) {
        echo json_encode(["status" => "success", "message" => "Excursion deleted successfully."]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt_delete->error]);
        exit;
    }
    $stmt_delete->close();
}

function addExcursionDetails($conectare) {
    $excursion_id = $_POST['excursion_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $transportation = $_POST['transportation'];
    $description = $_POST['description'];
    $title = $_POST['title'];
    $activities = $_POST['activities'];
    $price = $_POST['price'];

    $itinerary = $_POST['itinerary'];
    $hotel_name = $_POST['hotel_name'];
    $hotel_rating = $_POST['hotel_rating'];
    $hotel_description = $_POST['hotel_description'];
    $hotel_link = $_POST['hotel_link'];
    $meals_included = $_POST['meals_included'];
    $group_size = $_POST['group_size'];
    $weather_info = $_POST['weather_info'];
    $travel_tips = $_POST['travel_tips'];
    $culture_info = $_POST['culture_info'];

    $images = [$_FILES['image1'], $_FILES['image2'], $_FILES['image3'], $_FILES['image4'], $_FILES['image5']];
    $image_paths = [];

    foreach ($images as $image) {
        if ($image['error'] == UPLOAD_ERR_OK) {
            $imageName = basename($image['name']);
            $imagePath = "../../TravelIdeas/" . $imageName;
            $imageFileType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
            $check = getimagesize($image['tmp_name']);

            if ($check !== false && in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                if (move_uploaded_file($image['tmp_name'], $imagePath)) {
                    $image_paths[] = $imageName;
                } else {
                    echo json_encode(["status" => "error", "message" => "Failed to upload image: $imageName"]);
                    exit;
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid image format: $imageName"]);
                exit;
            }
        } else {
            $image_paths[] = null;
        }
    }

    $stmt = $conectare->prepare("
        INSERT INTO excursion_details (
            excursion_id, start_date, end_date, transportation, description, title, activities, price,
            image1_url, image2_url, image3_url, image4_url, image5_url,
            itinerary, hotel_name, hotel_rating, hotel_description, hotel_link,
            meals_included, group_size, weather_info, travel_tips, culture_info
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conectare->error]);
        exit;
    }

    $stmt->bind_param(
        "isssssssssssssssssisiss",
        $excursion_id, $start_date, $end_date, $transportation, $description, $title, $activities, $price,
        $image_paths[0], $image_paths[1], $image_paths[2], $image_paths[3], $image_paths[4],
        $itinerary, $hotel_name, $hotel_rating, $hotel_description, $hotel_link,
        $meals_included, $group_size, $weather_info, $travel_tips, $culture_info
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Excursion details added successfully."]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Execute failed: " . $stmt->error]);
        exit;
    }

    $stmt->close();
}

function editExcursionDetails($conectare) {
    if (!isset($_POST['details_id']) || !isset($_POST['excursion_id'])) {
        echo json_encode(["status" => "error", "message" => "Details ID or Excursion ID is missing."]);
        exit;
    }

    $details_id = $_POST['details_id'];
    $excursion_id = $_POST['excursion_id'];
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $transportation = $_POST['transportation'] ?? '';
    $description = $_POST['description'] ?? '';
    $title = $_POST['title'] ?? '';
    $activities = $_POST['activities'] ?? '';
    $price = $_POST['price'] ?? '';
    $itinerary = $_POST['itinerary'] ?? '';
    $hotel_name = $_POST['hotel_name'] ?? '';
    $hotel_rating = $_POST['hotel_rating'] ?? '';
    $hotel_description = $_POST['hotel_description'] ?? '';
    $hotel_link = $_POST['hotel_link'] ?? '';
    $meals_included = $_POST['meals_included'] ?? '';
    $group_size = $_POST['group_size'] ?? '';
    $weather_info = $_POST['weather_info'] ?? '';
    $travel_tips = $_POST['travel_tips'] ?? '';
    $culture_info = $_POST['culture_info'] ?? '';

    $image_paths = [];
    foreach (['image1', 'image2', 'image3', 'image4', 'image5'] as $i => $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == UPLOAD_ERR_OK) {
            $imageName = basename($_FILES[$field]['name']);
            $imagePath = "../../TravelIdeas/" . $imageName;
            $imageType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
            $check = getimagesize($_FILES[$field]['tmp_name']);

            if ($check !== false && in_array($imageType, ['jpg', 'jpeg', 'png', 'gif'])) {
                if (move_uploaded_file($_FILES[$field]['tmp_name'], $imagePath)) {
                    $image_paths[$i] = $imageName;
                } else {
                    $image_paths[$i] = $_POST['existing_image' . ($i + 1)] ?? null;

                }
            } else {
                $image_paths[$i] = $_POST['existing_image' . ($i + 1)] ?? null;

            }
        } else {
            $image_paths[$i] = $_POST['existing_image' . ($i + 1)] ?? null;

        }
    }


    $stmt_update_details = $conectare->prepare("UPDATE excursion_details SET 
    start_date = ?, end_date = ?, transportation = ?, description = ?, title = ?, activities = ?, price = ?,
    image1_url = ?, image2_url = ?, image3_url = ?, image4_url = ?, image5_url = ?,
    itinerary = ?, hotel_name = ?, hotel_rating = ?, hotel_description = ?, hotel_link = ?,
    meals_included = ?, group_size = ?, weather_info = ?, travel_tips = ?, culture_info = ?
    WHERE id = ? AND excursion_id = ?");


    $stmt_update_details->bind_param("sssssssssssssssssisissii",
    $start_date, $end_date, $transportation, $description, $title, $activities, $price,
    $image_paths[0], $image_paths[1], $image_paths[2], $image_paths[3], $image_paths[4],
    $itinerary, $hotel_name, $hotel_rating, $hotel_description, $hotel_link,
    $meals_included, $group_size, $weather_info, $travel_tips, $culture_info,
    $details_id, $excursion_id  
    );





    if ($stmt_update_details->execute()) {
        echo json_encode(["status" => "success", "message" => "Excursion details updated successfully."]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt_update_details->error]);
        exit;
    }
    $stmt_update_details->close();
}

function deleteExcursionDetails($conectare) {
    $excursion_id = $_POST['excursion_id'];

    $stmt_delete_details = $conectare->prepare("DELETE FROM excursion_details WHERE excursion_id = ?");
    $stmt_delete_details->bind_param("i", $excursion_id);
    if ($stmt_delete_details->execute()) {
        echo json_encode(["status" => "success", "message" => "Excursion details deleted successfully."]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt_delete_details->error]);
        exit;
    }
    $stmt_delete_details->close();
}

function fetchBookings($conectare, $agency_id) {
    $stmt = $conectare->prepare("
        SELECT 
            b.full_name, 
            u.email, 
            u.username, 
            u.phone, 
            b.payment_method, 
            b.nr_pers,
            e.title AS excursion_name
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN excursions e ON b.excursion_id = e.id
        WHERE e.travel_agency_id = ?
    ");
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $stmt->close();
    echo json_encode(["status" => "success", "bookings" => $bookings]);
    exit;
}

if (isset($_GET['fetch_bookings'])) {
    fetchBookings($conectare, $agency_id);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_excursion'])) {
        addExcursion($conectare, $agency_id);
    } elseif (isset($_POST['update_excursion'])) {
        editExcursion($conectare, $agency_id);
    } elseif (isset($_POST['delete_excursion'])) {
        deleteExcursion($conectare, $agency_id);
    } elseif (isset($_POST['add_excursion_details'])) {
        addExcursionDetails($conectare);
    } elseif (isset($_POST['update_excursion_details'])) {
        editExcursionDetails($conectare);
    } elseif (isset($_POST['delete_excursion_details'])) {
        deleteExcursionDetails($conectare);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="index_agency.css">
    <script>
        function showSection(sectionId) {
            var sections = document.getElementsByClassName("section");
            for (var i = 0; i < sections.length; i++) {
                sections[i].style.display = "none";
            }
            document.getElementById(sectionId).style.display = "block";
        }

        function handleFormSubmit(event, action) {
        event.preventDefault();
        var formData = new FormData(event.target);
        
        const hiddenExcId = document.getElementById("edit_excursion_id");
        if (hiddenExcId && hiddenExcId.value) {
            formData.set("excursion_id", hiddenExcId.value);
        }

        formData.append(action, true);

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "index_agency.php", true);

        xhr.onload = function() {
            const messageBox = document.getElementById("agencyMessage");
            if (!messageBox) return;

            try {
                const response = JSON.parse(xhr.responseText);
                messageBox.innerText = response.message || "No message returned";
                messageBox.className = "alert " + (response.status === "success" ? "alert-success" : "alert-danger");
                messageBox.style.display = "block";
                setTimeout(() => { messageBox.style.display = "none"; }, 8000);

                if (response.status === "success") {
                    event.target.reset();
                    updateFormOptions();
                }
            } catch (e) {
                messageBox.className = "alert alert-danger";
                messageBox.innerText = "Error parsing response from server.";
                messageBox.style.display = "block";
                console.error("Parsing error:", e);
            }
        };

        xhr.send(formData);
    }


        function updateFormOptions() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "index_agency.php?fetch_options=true", true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.status === "success") {
                            var excursions = data.excursions;
                            var seasons = data.seasons;

                            var editSelect = document.getElementById('edit_excursion_id');
                            var deleteSelect = document.getElementById('delete_excursion_id');
                            var detailsSelect = document.getElementById('details_excursion_id');
                            var editDetailsSelect = document.getElementById('edit_details_excursion_id');
                            var deleteDetailsSelect = document.getElementById('delete_details_excursion_id');
                            var destinationSelect = document.getElementById('season_id');

                            editSelect.innerHTML = '';
                            deleteSelect.innerHTML = '';
                            detailsSelect.innerHTML = '';
                            editDetailsSelect.innerHTML = '';
                            deleteDetailsSelect.innerHTML = '';
                            destinationSelect.innerHTML = '';

                            excursions.forEach(function(excursion) {
                                var option = new Option(excursion.title, excursion.id);
                                editSelect.add(option.cloneNode(true));
                                deleteSelect.add(option.cloneNode(true));
                                detailsSelect.add(option.cloneNode(true));
                                editDetailsSelect.add(option.cloneNode(true));
                                deleteDetailsSelect.add(option.cloneNode(true));
                            });

                            seasons.forEach(function(destination) {
                                var option = new Option(destination.title, destination.id);
                                destinationSelect.add(option.cloneNode(true));
                            });
                        } else {
                            alert("Failed to fetch options: " + data.message);
                        }
                    } catch (e) {
                        console.error("Parsing error:", e);
                        console.error("Response text:", xhr.responseText);
                       alert("An error occurred while fetching options. Please try again.");
                    }
                } else {
                    alert("Error: " + xhr.statusText);
                }
            };
            xhr.send();
        }

        document.addEventListener("DOMContentLoaded", updateFormOptions);

        function showEditExcursionForm() {
            var selectedExcursionId = document.getElementById('edit_excursion_id').value;

            var xhr = new XMLHttpRequest();
            xhr.open("GET", "index_agency.php?excursion_id=" + selectedExcursionId + "&details_type=excursion", true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === "success") {
                        var details = response.details;

                        document.getElementById('edit_excursion_title').value = details.title;
                        document.getElementById('edit_excursion_description').value = details.description;
                        document.getElementById('edit_excursion_id_hidden').value = selectedExcursionId;

                        if (details.image) {
                            document.getElementById('edit_excursion_image_preview').src = "../../TravelIdeas/" + details.image;
                        } else {
                            document.getElementById('edit_excursion_image_preview').style.display = 'none';
                        }

                        document.getElementById('editExcursionForm').style.display = 'block';
                    } else {
                        alert(response.message);
                    }
                } else {
                    alert("Error: " + xhr.statusText);
                }
            };
            xhr.send();
        }

        function showEditExcursionDetailsForm() {
            const selectedExcursionId = document.getElementById('edit_details_excursion_id').value;

            const xhr = new XMLHttpRequest();
            xhr.open("GET", "index_agency.php?excursion_id=" + selectedExcursionId + "&details_type=excursion_details", true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === "success") {
                        const details = response.details;
                        document.getElementById('edit_excursion_id').value = selectedExcursionId;
                    console.log(" Set hidden input edit_excursion_id =", selectedExcursionId);

                        // Fill in all fields from database
                        document.getElementById('edit_details_id').value = details.id;
                        document.getElementById('edit_start_date').value = details.start_date;
                        document.getElementById('edit_end_date').value = details.end_date;
                        document.getElementById('edit_transportation').value = details.transportation;
                        document.getElementById('edit_details_description').value = details.description;
                        document.getElementById('edit_details_title').value = details.title;
                        document.getElementById('edit_activities').value = details.activities;
                        document.getElementById('edit_price').value = details.price;
                        document.getElementById('edit_itinerary').value = details.itinerary;
                        document.getElementById('edit_hotel_name').value = details.hotel_name;
                        document.getElementById('edit_hotel_rating').value = details.hotel_rating;
                        document.getElementById('edit_hotel_description').value = details.hotel_description;
                        document.getElementById('edit_hotel_link').value = details.hotel_link;
                        document.getElementById('edit_meals_included').value = details.meals_included;
                        document.getElementById('edit_group_size').value = details.group_size;
                        document.getElementById('edit_weather_info').value = details.weather_info;
                        document.getElementById('edit_travel_tips').value = details.travel_tips;
                        document.getElementById('edit_culture_info').value = details.culture_info;

                        ['image1_url', 'image2_url', 'image3_url', 'image4_url', 'image5_url'].forEach((img, index) => {
                            const imgPreview = document.getElementById(`edit_details_image${index + 1}_preview`);
                            const hiddenInput = document.getElementById(`existing_image${index + 1}`);
                            
                            if (details[img]) {
                                imgPreview.src = "../../TravelIdeas/" + details[img];
                                imgPreview.style.display = 'block';
                                hiddenInput.value = details[img];  
                            } else {
                                imgPreview.style.display = 'none';
                                hiddenInput.value = ""; 
                            }
                        });


                        document.getElementById('edit_excursion_id').value = selectedExcursionId;
                        console.log(" Set hidden input edit_excursion_id =", selectedExcursionId);

                        document.getElementById('editExcursionDetailsForm').style.display = 'block';
                    } else {
                        console.error(" Server error:", response.message);
                    }
                } else {
                    console.error(" HTTP error:", xhr.statusText);
                }
            };
            xhr.send();
        }

        function showBookings() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "index_agency.php?fetch_bookings=true", true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.status === "success") {
                            var bookings = response.bookings;
                            var bookingsTableBody = document.getElementById('bookingsTableBody');
                            bookingsTableBody.innerHTML = '';

                            bookings.forEach(function(booking) {
                                var row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${booking.full_name}</td>
                                    <td>${booking.email}</td>
                                    <td>${booking.username}</td>
                                    <td>${booking.phone}</td>
                                    <td>${booking.nr_pers}</td>
                                    <td>${booking.payment_method}</td>
                                    <td>${booking.excursion_name}</td>
                                `;
                                bookingsTableBody.appendChild(row);
                            });

                            showSection('bookingsSection');
                        } else {
                            alert("Failed to fetch bookings: " + response.message);
                        }
                    } catch (e) {
                        console.error("Parsing error:", e);
                        console.error("Response text:", xhr.responseText);
                        alert("An error occurred while fetching bookings. Please try again.");
                    }
                } else {
                    alert("Error: " + xhr.statusText);
                }
            };
            xhr.send();
        }

        document.addEventListener("DOMContentLoaded", updateFormOptions);
    </script>
</head>
<body>
    <div class="container mt-4">
        <h2>Welcome, <?php echo htmlspecialchars($agency_name); ?>!</h2>
        <div id="agencyMessage" class="alert" style="display:none;"></div>

        <div class="buttons">
            <button class="btn btn-primary" onclick="showSection('addSection')">Add Excursion</button>
            <button class="btn btn-primary" onclick="showSection('editSection')">Edit Excursion</button>
            <button class="btn btn-primary" onclick="showSection('deleteSection')">Delete Excursion</button>
            <button class="btn btn-primary" onclick="showSection('addDetailsSection')">Add Excursion Details</button>
            <button class="btn btn-primary" onclick="showSection('editDetailsSection')">Edit Excursion Details</button>
            <button class="btn btn-primary" onclick="showSection('deleteDetailsSection')">Delete Excursion Details</button>
            <button class="btn btn-primary" onclick="showBookings()">View Bookings</button>
            <button id="logoutBtn" class="btn btn-secondary">Logout</button>

        </div>

        <div id="addSection" class="section" style="display:none;">
            <h3>Add New Excursion</h3>
            <form method="POST" enctype="multipart/form-data" onsubmit="handleFormSubmit(event, 'add_excursion')">
                <div class="form-group">
                    <label for="season_id">Destination</label>
                    <select class="form-control" id="season_id" name="season_id" required>
                        <?php foreach ($seasons as $destination): ?>
                            <option value="<?php echo $destination['id']; ?>"><?php echo htmlspecialchars($destination['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
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
                <button type="submit" class="btn btn-success">Add Excursion</button>
            </form>
        </div>

        <div id="editSection" class="section" style="display:none;">
            <h3>Edit Excursion</h3>



            <form id="selectExcursionForm" onsubmit="showEditExcursionForm(); return false;">
                <div class="form-group">
                    <label for="edit_excursion_id">Excursion</label>
                    <select class="form-control" id="edit_excursion_id" name="excursion_id" required>
                    </select>
                </div>
                <button type="submit" class="btn btn-info">Edit Excursion</button>
            </form>

            <form id="editExcursionForm" method="POST" enctype="multipart/form-data" style="display:none;" onsubmit="handleFormSubmit(event, 'update_excursion')">

                <input type="hidden" id="edit_excursion_id_hidden" name="edit_excursion_id_hidden">

                <div class="form-group">
                    <label for="edit_excursion_title">New Title</label>
                    <input type="text" class="form-control" id="edit_excursion_title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="edit_excursion_description">New Description</label>
                    <textarea class="form-control" id="edit_excursion_description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_image">New Image</label>
                    <input type="file" class="form-control" id="edit_image" name="image">
                    <img id="edit_excursion_image_preview" src="" alt="Excursion Image" style="max-width: 100px; max-height: 100px;">
                </div>
                <button type="submit" class="btn btn-warning">Update Excursion</button>
            </form>

        </div>

        <div id="deleteSection" class="section" style="display:none;">
            <h3>Delete Excursion</h3>
            <form method="POST" onsubmit="handleFormSubmit(event, 'delete_excursion')">
                <div class="form-group">
                    <label for="delete_excursion_id">Excursion</label>
                    <select class="form-control" id="delete_excursion_id" name="excursion_id" required>
                    </select>
                </div>
                <button type="submit" class="btn btn-danger">Delete Excursion</button>
            </form>
        </div>

        <div id="addDetailsSection" class="section" style="display:none;">
            <h3>Add New Excursion Details</h3>
            <form method="POST" enctype="multipart/form-data" onsubmit="handleFormSubmit(event, 'add_excursion_details')">
                <div class="form-group">
                    <label for="details_excursion_id">Excursion</label>
                    <select class="form-control" id="details_excursion_id" name="excursion_id" required>
                    </select>
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                </div>
                <div class="form-group">
                    <label for="transportation">Transportation</label>
                    <input type="text" class="form-control" id="transportation" name="transportation" required>
                </div>
                <div class="form-group">
                    <label for="details_description">Description</label>
                    <textarea class="form-control" id="details_description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="details_title">Title</label>
                    <input type="text" class="form-control" id="details_title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="activities">Activities</label>
                    <textarea class="form-control" id="activities" name="activities" required></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                </div>
                <div class="form-group">
                    <label for="itinerary">Itinerary</label>
                    <textarea class="form-control" id="itinerary" name="itinerary" required></textarea>
                </div>
                <div class="form-group">
                    <label for="hotel_name">Hotel Name</label>
                    <input type="text" class="form-control" id="hotel_name" name="hotel_name">
                </div>
                <div class="form-group">
                    <label for="hotel_rating">Hotel Rating</label>
                    <input type="number" step="0.1" max="5" min="0" class="form-control" id="hotel_rating" name="hotel_rating">
                </div>
                <div class="form-group">
                    <label for="hotel_description">Hotel Description</label>
                    <textarea class="form-control" id="hotel_description" name="hotel_description"></textarea>
                </div>
                <div class="form-group">
                    <label for="hotel_link">Hotel Link</label>
                    <input type="url" class="form-control" id="hotel_link" name="hotel_link">
                </div>
                <div class="form-group">
                    <label for="meals_included">Meals Included</label>
                    <input type="text" class="form-control" id="meals_included" name="meals_included">
                </div>
                <div class="form-group">
                    <label for="group_size">Group Size</label>
                    <input type="number" class="form-control" id="group_size" name="group_size">
                </div>
                <div class="form-group">
                    <label for="weather_info">Weather Info</label>
                    <textarea class="form-control" id="weather_info" name="weather_info"></textarea>
                </div>
                <div class="form-group">
                    <label for="travel_tips">Travel Tips</label>
                    <textarea class="form-control" id="travel_tips" name="travel_tips"></textarea>
                </div>
                <div class="form-group">
                    <label for="culture_info">Culture Info</label>
                    <textarea class="form-control" id="culture_info" name="culture_info"></textarea>
                </div>

                
                <div class="form-group">
                    <label for="details_image1">Image 1</label>
                    <input type="file" class="form-control" id="details_image1" name="image1" required>
                </div>
                <div class="form-group">
                    <label for="details_image2">Image 2</label>
                    <input type="file" class="form-control" id="details_image2" name="image2">
                </div>
                <div class="form-group">
                    <label for="details_image3">Image 3</label>
                    <input type="file" class="form-control" id="details_image3" name="image3">
                </div>
                <div class="form-group">
                    <label for="details_image4">Image 4</label>
                    <input type="file" class="form-control" id="details_image4" name="image4">
                </div>
                <div class="form-group">
                    <label for="details_image5">Image 5</label>
                    <input type="file" class="form-control" id="details_image5" name="image5">
                </div>
                <button type="submit" class="btn btn-success">Add Excursion Details</button>
            </form>
        </div>

        <div id="editDetailsSection" class="section" style="display:none;">
            <h3>Edit Excursion Details</h3>
            <form id="selectDetailsForm" onsubmit="showEditExcursionDetailsForm(); return false;">
                <div class="form-group">
                    <label for="edit_details_excursion_id">Excursion</label>
                    <select class="form-control" id="edit_details_excursion_id" name="excursion_selector" required>

                    </select>
                </div>
                <button type="submit" class="btn btn-info">Edit Details</button>
            </form>

            <form id="editExcursionDetailsForm" method="POST" enctype="multipart/form-data" style="display:none;" onsubmit="handleFormSubmit(event, 'update_excursion_details')">
                <input type="hidden" id="edit_excursion_id" name="excursion_id">

                <input type="hidden" id="edit_details_id" name="details_id">
                <div class="form-group">
                    <label for="edit_start_date">New Start Date</label>
                    <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="edit_end_date">New End Date</label>
                    <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                </div>
                <div class="form-group">
                    <label for="edit_transportation">New Transportation</label>
                    <input type="text" class="form-control" id="edit_transportation" name="transportation" required>
                </div>
                <div class="form-group">
                    <label for="edit_details_description">New Description</label>
                    <textarea class="form-control" id="edit_details_description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_details_title">New Title</label>
                    <input type="text" class="form-control" id="edit_details_title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="edit_activities">New Activities</label>
                    <textarea class="form-control" id="edit_activities" name="activities" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_price">New Price</label>
                    <input type="number" step="0.01" class="form-control" id="edit_price" name="price" required>
                </div>
                <div class="form-group">
                    <label for="edit_itinerary">New Itinerary</label>
                    <textarea class="form-control" id="edit_itinerary" name="itinerary"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_hotel_name">Hotel Name</label>
                    <input type="text" class="form-control" id="edit_hotel_name" name="hotel_name">
                </div>
                <div class="form-group">
                    <label for="edit_hotel_rating">Hotel Rating</label>
                    <input type="number" step="0.1" max="5" min="0" class="form-control" id="edit_hotel_rating" name="hotel_rating">
                </div>
                <div class="form-group">
                    <label for="edit_hotel_description">Hotel Description</label>
                    <textarea class="form-control" id="edit_hotel_description" name="hotel_description"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_hotel_link">Hotel Link</label>
                    <input type="url" class="form-control" id="edit_hotel_link" name="hotel_link">
                </div>
                <div class="form-group">
                    <label for="edit_meals_included">Meals Included</label>
                    <input type="text" class="form-control" id="edit_meals_included" name="meals_included">
                </div>
                <div class="form-group">
                    <label for="edit_group_size">Group Size</label>
                    <input type="number" class="form-control" id="edit_group_size" name="group_size">
                </div>
                <div class="form-group">
                    <label for="edit_weather_info">Weather Info</label>
                    <textarea class="form-control" id="edit_weather_info" name="weather_info"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_travel_tips">Travel Tips</label>
                        <textarea class="form-control" id="edit_travel_tips" name="travel_tips"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_culture_info">Culture Info</label>
                    <textarea class="form-control" id="edit_culture_info" name="culture_info"></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_details_image1">New Image 1</label>

                    <input type="hidden" id="existing_image1" name="existing_image1">

                    <input type="file" class="form-control" id="edit_details_image1" name="image1">
                    <img id="edit_details_image1_preview" src="" alt="Image 1" style="max-width: 100px; max-height: 100px;">
                </div>
                <div class="form-group">
                    <label for="edit_details_image2">New Image 2</label>
                    <input type="hidden" id="existing_image2" name="existing_image2">
                    
                    <input type="file" class="form-control" id="edit_details_image2" name="image2">
                    <img id="edit_details_image2_preview" src="" alt="Image 2" style="max-width: 100px; max-height: 100px;">
                </div>
                <div class="form-group">
                <input type="hidden" id="existing_image3" name="existing_image3">
                    
                    <label for="edit_details_image3">New Image 3</label>
                    <input type="file" class="form-control" id="edit_details_image3" name="image3">
                    <img id="edit_details_image3_preview" src="" alt="Image 3" style="max-width: 100px; max-height: 100px;">
                </div>
                <div class="form-group">
                    <label for="edit_details_image4">New Image 4</label>
                    <input type="hidden" id="existing_image4" name="existing_image4">
                    <input type="file" class="form-control" id="edit_details_image4" name="image4">
                    <img id="edit_details_image4_preview" src="" alt="Image 4" style="max-width: 100px; max-height: 100px;">
                </div>
                <div class="form-group">
                    <label for="edit_details_image5">New Image 5</label>
                    <input type="hidden" id="existing_image5" name="existing_image5">

                    <input type="file" class="form-control" id="edit_details_image5" name="image5">
                    <img id="edit_details_image5_preview" src="" alt="Image 5" style="max-width: 100px; max-height: 100px;">
                </div>
                <button type="submit" class="btn btn-warning">Update Excursion Details</button>
            </form>
        </div>

        <div id="deleteDetailsSection" class="section" style="display:none;">
            <h3>Delete Excursion Details</h3>
            <form method="POST" onsubmit="handleFormSubmit(event, 'delete_excursion_details')">
                <div class="form-group">
                    <label for="delete_details_excursion_id">Excursion</label>
                    <select class="form-control" id="delete_details_excursion_id" name="excursion_id" required>
                    </select>
                </div>
                <button type="submit" class="btn btn-danger">Delete Excursion Details</button>
            </form>
        </div>
        <div id="bookingsSection" class="section" style="display:none;">
            <h3>User Bookings</h3>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Phone</th>
                            <th>Nr of people</th>
                            <th>Payment Method</th>
                            <th>Excursion Name</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsTableBody">
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <script>
    document.getElementById('logoutBtn').addEventListener('click', function () {
    fetch('index_agency.php', {
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
