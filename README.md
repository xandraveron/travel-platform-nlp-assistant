# Travel Platform with integrated Chatbot



Full-stack travel booking platform with an AI chatbot that helps users explore trips, prices, activities, and travel details using natural language.



## Main Features

- AI travel chatbot using NLP
- Natural language intent detection (spaCy)
- Context memory (remembers last place/season that was mentioned)
- Fuzzy matching for destinations
- Dynamic trip recommendations from database
- Full travel booking system
- Multi-role dashboards:
   - User: Can book excursions, send messages to admins and manage bookings
   - Travel Agency: Can manage its own excursions and see bookings
   - Admin: Can manage agencies
- Image galleries & trip details
- MySQL database integration



## Chatbot

The chatbot supports:
- Intent detection
- Context memory
- Destination recognition
- Field-based responses (price, activities, itinerary, hotel, transport,..etc)



## Tech Stack

* AI/NLP: Python, Flask, spaCy, fuzzy matching
* Backend: PHP, MySQL, PHPMailer, sessions
* Frontend: HTML, CSS, JavaScript, AJAX
* Environment: XAMPP (Apache, phpMyAdmin)



## Project Structure
```text
TravelIdeas/
├── web/                 # PHP website (XAMPP)
│   ├── travel.php
│   ├── booking1.php
│   ├── login.php
│   ├── logout.php
│   ├── my_bookings.php
│   ├── connect_db.php
│   ├── images/
│   ├── admin/
│   └── travel_agency/
├── chatbot_api/         # Flask chatbot API
│   ├── chatbot_api.py
│   ├── responses.py
│   ├── requirements.txt
│   └── .env.example
├── database/
│   └── travel.sql
├── .gitignore
└── README.md
```



## Local Setup



### 1) Website (XAMPP)

1. Install **XAMPP** and start **Apache** + **MySQL**.

2. Copy the website folder into XAMPP:
   - Copy everything from `web/` to: `C:\\xampp\\htdocs\\TravelIdeas\\`

3. Open the website in your browser: `http://localhost/TravelIdeas/travel.php`



### 2) Database (phpMyAdmin)

1. Open phpMyAdmin: `http://localhost/phpmyadmin`

2. Create a database named: `travel`

3. Import the SQL file: `database/travel.sql`



### 3) Chatbot API (Flask)

1. Open a terminal in the `chatbot\_api/` folder.

2. Install dependencies:
`pip install -r requirements.txt`

`python -m spacy download en\_core\_web\_sm`

4. Start the API: python chatbot\_api.py

5. The website calls the chatbot at: http://localhost:5000/chat



