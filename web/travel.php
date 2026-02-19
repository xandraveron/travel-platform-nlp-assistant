<?php require_once __DIR__ . "/connect_db.php";

session_start();
$is_logged_in = isset($_SESSION['existing']) && $_SESSION['existing'] === true;
$username = $_SESSION['username'] ?? '';

function renderExcursionCard($row) {
     $filename = trim($row['image']);             
    $image = "/TravelIdeas/images/" . rawurlencode($filename);
    $description = $row['description'];
    $title = $row['title'];
    $detailsUrl = "booking1.php?id=" . $row['id'];
    return ' 
        <div class="responsive">
            <div class="gallery">
                <a href="' . $detailsUrl . '">
                    <img src="' . $image . '" alt="' . $title . '">
                </a>
                <div class="desc">' . $description . '</div>
            </div>
        </div>';
}

if (isset($_GET['get_all_excursions'])) {
    $sql = "SELECT * FROM excursions";
    $result = mysqli_query($conectare, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo '<div class="gallery-container">';
        while ($row = mysqli_fetch_assoc($result)) {
            echo renderExcursionCard($row);
        }
        echo '</div>';
    } else {
        echo "No excursions found.";
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['season_id'])) {
    $season_id = intval($_POST['season_id']);

    $sql = "SELECT * FROM excursions WHERE season_id = ?";
    $stmt = $conectare->prepare($sql);
    $stmt->bind_param("i", $season_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<div class="gallery-container">';
        while ($row = $result->fetch_assoc()) {
            echo renderExcursionCard($row);
        }
        echo '</div>';
    } else {
        echo "No excursions found for this season.";
    }
    $stmt->close();
    exit;
}
?>
<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $message = trim($_POST["message"]);

    if (!empty($name) && !empty($email) && !empty($message)) {
        $stmt = $conectare->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
        $stmt->close();
    } else {
        echo "missing";
    }
    exit;
}
?>


<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Travel</title>
  <link rel="icon" type="image/x-icon" href="travel.png">
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">

  <link rel="stylesheet" type="text/css" href="travel.css">
    
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="travel.js"></script>


</head>
<body>
<header>
  <div class="nav-bar">
    <a href="" class="logo"><img src="flight .png"></a>
    <div class="navigation">
      <div class="nav-items">
        
        <i class="uil uil-times nav-close-btn"></i>
        <a href="#home"><i class="uil uil-estate"></i>Home</a>

        <a href="#AboutUs"><i class="uil uil-info-circle"></i>About Us</a>
        <a href="#contact"><i class="uil uil-envelope"></i>Contact</a>
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

<section id="home">
    <div class="media-icons">
        <a href="https://www.facebook.com"><i class="uil uil-facebook-messenger-alt"></i></a>
        <a href="https://twitter.com"><i class="uil uil-twitter-alt"></i></a>
        <a href="https://instagram.com"><i class="uil uil-instagram"></i></a>
    </div>

    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            <?php
            $sql = "SELECT * FROM seasons";
            $result = mysqli_query($conectare, $sql);

            if (mysqli_num_rows($result) > 0) {

                $first = true;

                while ($row = mysqli_fetch_assoc($result)) {
                    

                    $seasonId = $row['id'];

                    $title = $row['title'];
                    $description = $row['description'];
                    $filename = trim($row['image']);              
                    $image = "/TravelIdeas/images/" . rawurlencode($filename);                    
                    echo '<div class="swiper-slide"';
                    if ($first) {
                        echo ' data-season-id="all"'; 
                        $first = false;
                    } else {
                        echo ' data-season-id="' . $seasonId . '"';
                    }
                    echo '>';

                    echo '<img src="' . $image . '" >';
                    echo '<div class="text-content">';
                    echo '<h2 class="title">' . $title . '</h2>';
                    echo '<p>' . $description . '</p>';
                    echo '<button class="read-btn" data-season-id="' . $seasonId . '">Read More<i class="uil uil-arrow-right"></i></button>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "No seasons found.";
            }
            ?>
        </div>

        <a class="prev" onclick="plusSlides(-1)">❮</a>
        <a class="next" onclick="plusSlides(1)">❯</a>

        <div class="dots">
            <?php
            $sql = "SELECT * FROM seasons";
            $result = mysqli_query($conectare, $sql);

            if (mysqli_num_rows($result) > 0) {
                $count = 0; 
                while ($count <= mysqli_num_rows($result)) {
                    echo '<span class="dot" onclick="currentSlide(' . $count . ')"></span>';
                    $count++;
                }
            }
            ?>
        </div>
    </div>
</section>
<div class="content-wrapper">

<section class="descript">
  <h2>
    Explore the world
  </h2>
  <p>
    A lot of people think about investing in their career or home, but the life experience you get through traveling is one of the greatest investments. The insight, perspective, and resilience you gain can help you in all facets of life.</p>
    <p>A journey of a thousand miles begins with a single step. – Lao Tzu</p>
  
</section>
 
<section class="more" id="more-details">

</section>


<div class="clearfix"></div>

<section id="AboutUs">
  <h2>About Us</h2>
  <p> We have been moving excellent encounters for a considerable length of time through our cutting-edge planned occasion bundles and other fundamental travel administrations. We rouse our clients to carry on with a rich life, brimming with extraordinary travel encounters.</p>
   <p>Travel is the main thing you purchase that makes you more extravagant”. We, at ‘Organization Name’, swear by this and put stock in satisfying travel dreams that make you perpetually rich constantly.
   We are enthusiastic about giving corporate explorers hello there.</p>
</section>

 <section id="contact" class="booking-form">
    <h2>Contact</h2>

    <article>
      <div class="info mb-4">
        <p><i class="uil uil-map-marker"></i> Strada Lulelei, Cluj-Napoca, jud. Cluj</p>
        <p><i class="uil uil-phone-volume"></i> 0711980915</p>
        <p><i class="uil uil-fast-mail"></i> travelIdea@gmail.com</p>
      </div>


    <form class="form-section contact-form" method="post" action="">
        <h3>Write a message here:</h3>

        <div id="contactMessage" class="form-message"></div>

        <div class="form-group">
          <label for="name">Name:</label>
          <input type="text" id="name" name="name" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="message">Message:</label>
          <textarea id="message" name="message" rows="5" class="form-control" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Send</button>
      </form>
    </article>
  </section>


    <footer class="footer">
      <img src="flight .png">
      <p>©2025.Design si implementare:Dobra Alexandra-Veronica.All rights reserved</p>
      
    </footer>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
      let slideIndex = 0;
showSlides();

function plusSlides(n) {
    showSlides(slideIndex += n);
}

function currentSlide(n) {
    showSlides(slideIndex = n);
}

function showSlides() {
    let i;
    let slides = document.getElementsByClassName("swiper-slide");
    let dots = document.getElementsByClassName("dot");
    if (slideIndex >= slides.length) {
        slideIndex = 0;
    }
    if (slideIndex < 0) {
        slideIndex = slides.length - 1;
    }
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";  
    }
    for (i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active");
    }
    slides[slideIndex].style.display = "block";  
    dots[slideIndex].classList.add("active");
  }


function displayAllExcursions(callback) {
  $.ajax({
    url: "travel.php",
    type: "GET",
    data: { get_all_excursions: true },
    success: function(data) {
      $("#more-details").fadeOut(100, function () {
        $(this).html(data).fadeIn(200, function () {
          if (typeof callback === "function") callback();
        });
      });
    }
  });
}

$(document).ready(function() {
    displayAllExcursions();
});


function fetchExcursions(seasonId, callback) {
  if (seasonId === "all") {
    displayAllExcursions(callback);
    return;
  }

  $.ajax({
    url: "travel.php",
    type: "POST",
    data: { season_id: seasonId },
    success: function(data) {
      $("#more-details").fadeOut(100, function () {
        $(this).html(data).fadeIn(200, function () {
          if (typeof callback === "function") callback();
        });
      });
    }
  });
}


$(document).ready(function() {
    $(".read-btn").click(function() {
    var seasonId = getActiveSlideSeasonId();
        fetchExcursions(seasonId, function () {
              const target = document.getElementById("more-details");
              if (target) {
                target.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    });    
  });
});





function getActiveSlideSeasonId() {
  const activeSlide = document.querySelector(".swiper-slide[style*='block']");
  return activeSlide?.dataset.seasonId;
}

function autoLoadExcursionsForSlide(callback) {
  const seasonId = getActiveSlideSeasonId();
  if (seasonId) {
    fetchExcursions(seasonId, callback);
  }
}

function plusSlides(n) {
  showSlides(slideIndex += n);
  setTimeout(autoLoadExcursionsForSlide, 200); 
}

function currentSlide(n) {
  showSlides(slideIndex = n);
  setTimeout(autoLoadExcursionsForSlide, 200);
}

function loadChatHistory() {
  const saved = localStorage.getItem("tripy-history");
  if (saved) document.getElementById("chat-history").innerHTML = saved;
}

function saveChatHistory() {
  const history = document.getElementById("chat-history").innerHTML;
  localStorage.setItem("tripy-history", history);
}

$(document).ready(function() {
    $(".contact-form").on("submit", function(e) {
        e.preventDefault(); // oprește trimiterea clasică

        $.ajax({
            type: "POST",
            url: "travel.php", 
            data: $(this).serialize(),
            success: function(response) {
                let msg = "";
                let msgClass = "";

                if (response.trim() === "success") {
                    msg = "Your message was sent successfully.";
                    msgClass = "text-success";
                    $(".contact-form")[0].reset(); 
                } else if (response.trim() === "error") {
                    msg = "Error saving your message.";
                    msgClass = "text-danger";
                } else if (response.trim() === "missing") {
                    msg = "All fields are required.";
                    msgClass = "text-danger";
                } else {
                    msg = "Unexpected error.";
                    msgClass = "text-danger";
                }

                // afisam mesajul în #contactMessage (deasupra formularului)
                $("#contactMessage")
                    .removeClass("text-success text-danger")
                    .addClass(msgClass)
                    .html(msg)
                    .fadeIn();
            },
            error: function() {
                $("#contactMessage")
                    .removeClass("text-success text-danger")
                    .addClass("text-danger")
                    .html("Failed to connect to server.")
                    .fadeIn();
            }
        });
    });
});



  </script>
 
<div id="tripy-avatar" onclick="toggleChat()">
  <img src="tripy-avatar.png" alt="Tripy" />
  <span>I’m Tripy, I can help you!</span>
</div>

<div id="chat-container">
  <div class="chat-box">
    <div class="chat-header">
      <h4><img src="tripy-avatar.png" alt="Tripy" /> Tripy - Travel Assistant</h4>
      <button onclick="toggleChat()">❌</button>
    </div>
    <div id="chat-history">
      <div class="msg-bot">Hi, I'm Tripy, your travel assistant. I'm here to guide you on your adventure. Ask me anything about our trips!</div>
    </div>
    <div class="chat-input">
      <input id="user-input" type="text" placeholder="Ask me about travel..." onkeydown="if(event.key==='Enter') sendMessage()" />
      <button onclick="sendMessage()">Send</button>
    </div>
  </div>
</div>

<script>
  let chatOpen = false;

  function toggleChat() {
  const chat = document.getElementById("chat-container");
  const avatar = document.getElementById("tripy-avatar");

  if (chat.style.display === "block") {
    chat.style.display = "none";
    avatar.style.display = "flex";

    sessionStorage.setItem("chat_persist", "true");

  } else {
    chat.style.display = "block";
    avatar.style.display = "none";

    const chatPersist = sessionStorage.getItem("chat_persist");
    if (chatPersist !== "true") {
      sessionStorage.clear();
    }
  }
}




function sendMessage() {
  const input = document.getElementById("user-input");
  const message = input.value.trim();
  if (!message) return;

  const history = document.getElementById("chat-history");
  history.innerHTML += `<div class="msg-user">${message}</div>`;
  input.value = "";


  fetch(
      "http://localhost:5000/chat?" + new URLSearchParams({ message: message }),
      {
        method: "GET",
        credentials: "include"  
      }
    )
  .then(response => response.json())
  .then(data => {
    
    
    history.innerHTML += `<div class="msg-bot">${data.response}</div>`;
    history.scrollTop = history.scrollHeight;
  })
  .catch(() => {
    history.innerHTML += `<div class="msg-bot">Error connecting to chatbot.</div>`;
  });
}

</script>
</body>
</html> 
