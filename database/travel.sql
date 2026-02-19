-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `travel`
--

-- --------------------------------------------------------

--
-- Table structure for table `agencies`
--

CREATE TABLE `agencies` (
  `agency_id` int(11) NOT NULL,
  `agency_name` varchar(255) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agencies`
--

INSERT INTO `agencies` (`agency_id`, `agency_name`, `username`, `password`, `email`, `phone`) VALUES
(5, 'Safe Travel', 'travel_agency1', '$2y$10$w0Ivxfg9Ni6S5OR7r1D4POFq9aCNLiCg8Ck/G.qN4/nbN0tSUzBsy', 'example@gmail.com', '0789995999'),
(6, 'Sunny Island', 'travel_agency2', '$2y$10$rCGuzQfthleEyDKhTCWN6eqOcq07DKCzTcLuVJDu1zt44ADFXtV9e', 'example@gmail.com', '0789995419'),
(7, 'Wild Adventure', 'travel_agency3', '$2y$10$iY0ens3jNVvi7EB6LIDfUOh8eUFBW/mGBxS99ogrEpAGrqm.q.aby', 'example@gmail.com', '0789995234'),
(9, 'Sunny Day', 'travel_agency4', '$2y$10$eY/upMIFKMSL9e4IgV0oKeGiUu4Jl/JI7P7xcT6.lUjII3Gy2r8jq', 'example@gmail.com', '0746067500'),
(10, 'Bora Bora', 'travel_agency5', '$2y$10$TY75g/lpXg5CtLx0xIBlTulDe3rkaSVrapslrQP/C6P.SUleIJCxW', 'example@gmail.com', '0745997678'),
(12, 'Bon Voyage', 'travel_agency6', '$2y$10$/8UxheUWYOQQt3OUh2uDm.B7XmXoMaBAbeA1RYAzb6XprlUZVztiC', 'example@gmail.com', '0725444455');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `excursion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `booking_date` timestamp NULL DEFAULT current_timestamp(),
  `nr_pers` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `excursion_id`, `user_id`, `full_name`, `payment_method`, `booking_date`, `nr_pers`) VALUES
(32, 11, 21, 'Ana Maria Magdalena', 'credit_card', '2025-04-27 10:10:00', 1),
(40, 11, 18, 'Alexandra Veronica', 'credit_card', '2025-05-15 13:34:07', 1),
(44, 2, 18, 'Alexandra Veronica', 'credit_card', '2025-05-15 15:41:14', 1),
(52, 3, 39, 'Carmelita Carmen', 'bank_transfer', '2025-06-01 13:18:06', 3),
(53, 2, 39, 'Carmelita Carmen', 'bank_transfer', '2025-06-01 13:36:03', 3),
(54, 2, 41, 'Anabella Alina', 'credit_card', '2025-06-01 14:38:36', 2),
(55, 22, 39, 'Carmen Carmelita', 'credit_card', '2025-06-14 17:35:07', 3),
(57, 12, 21, 'Ana Maria Magdalena', 'bank_transfer', '2025-07-01 09:35:04', 3),
(58, 2, 21, 'Ana Maria Magdalena', 'paypal', '2025-07-01 10:16:13', 3),
(59, 2, 44, 'Marinela Crevitz', 'bank_transfer', '2025-12-12 17:18:28', 2);

-- --------------------------------------------------------

--
-- Table structure for table `excursions`
--

CREATE TABLE `excursions` (
  `id` int(11) NOT NULL,
  `season_id` int(11) DEFAULT NULL,
  `travel_agency_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `excursions`
--

INSERT INTO `excursions` (`id`, `season_id`, `travel_agency_id`, `title`, `description`, `image`) VALUES
(2, 2, 5, 'Zermatt, Switzerland\r\n\r\n', 'A picture-perfect alpine village at the base of the Matterhorn.\r\n', 'zermatt5.jpg'),
(3, 2, 6, 'Tromsø, Norway', 'Highlights: Dog sledding, reindeer safaris, and arctic fjord cruises.\r\n\r\n', 'tromso.jpg'),
(4, 2, 7, 'Lapland, Finland', 'It’s the ultimate winter fantasy come true, for families, couples, or anyone chasing snowy adventure.', 'laponia2.jpg'),
(5, 2, 6, 'Reykjavik, Iceland', 'Volcanoes, glaciers, and geothermal spas.\r\nHighlights: Soak in the Blue Lagoon, explore ice caves, witness frozen waterfalls.', 'reykjavik.jpg'),
(11, 3, 9, 'Kyoto, Japan', 'Cherry blossom season transforms the city into a pink dream.\r\nHighlights: Philosopher’s Path, tea houses, geisha culture in Gion.', 'kyoto.jpg'),
(12, 3, 6, 'Amsterdam, Netherlands', 'Tulip season + canal charm = perfect spring energy.\r\nHighlights: Keukenhof Gardens, bike rides, art museums, canal cruises.', 'amsterdam.jpg'),
(13, 3, 5, 'Paris, France', 'Springtime in Paris is like a movie.\r\nHighlights: Blossoms in Jardin du Luxembourg, picnics under the Eiffel Tower, café hopping.', 'p.jpg'),
(14, 3, 9, 'Marrakech, Morocco', 'Warm but not scorching, spring is ideal for exploring.\r\nHighlights: Souks, gardens, hammams, desert excursions into the Atlas Mountains.', 'morocco.jpeg'),
(21, 4, 7, 'Santorini, Greece', 'Iconic sunsets and whitewashed villages over the sea.\r\nHighlights: Cliffside dining in Oia, boat tours, black sand beaches.', 'santorini.jpg'),
(22, 4, 6, 'Barcelona, Spain', 'Art, beaches, nightlife - all in one.\r\nHighlights: Sagrada Familia, tapas bars, beach walks, open-air festivals.', 'barcelona.jpg'),
(23, 4, 7, 'Amalfi Coast, Italy', 'Colorful cliff towns and stunning coastal roads.\r\nHighlights: Positano, boat rides to Capri, lemon groves and gelato.', 'amalfi.jpeg'),
(24, 4, 9, 'Dubrovnik, Croatia', 'Game of Thrones vibes meet Adriatic bliss.\r\nHighlights: Walk the city walls, sail the coast, visit hidden beaches.', 'dubrovnik.jpg'),
(31, 5, 5, 'New England, USA', ' The ultimate fall foliage destination.\r\nHighlights: Scenic drives through Vermont, apple picking, pumpkin festivals.', 'newengland.jpg'),
(32, 5, 6, 'Munich, Germany', 'Oktoberfest! And golden city parks.\r\nHighlights: Beer halls, Bavarian food, biking through English Garden.', 'munich.jpg'),
(33, 5, 6, 'Istanbul, Turkey', 'Cool weather, fewer tourists, and rich history.\r\nHighlights: Hagia Sophia, Bosphorus cruises, Grand Bazaar.', 'istanbul.jpg'),
(34, 5, 9, 'Prague, Czech Republic', 'Fairytale charm meets cozy fall vibes.\r\nHighlights: Charles Bridge at sunrise, medieval streets, hearty food in cellar pubs.', 'prague.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `excursion_details`
--

CREATE TABLE `excursion_details` (
  `id` int(11) NOT NULL,
  `excursion_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `transportation` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `itinerary` text DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `activities` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image1_url` varchar(100) DEFAULT NULL,
  `image2_url` varchar(100) DEFAULT NULL,
  `image3_url` varchar(100) DEFAULT NULL,
  `image4_url` varchar(100) DEFAULT NULL,
  `image5_url` varchar(100) DEFAULT NULL,
  `hotel_name` varchar(255) DEFAULT NULL,
  `hotel_rating` int(11) DEFAULT NULL,
  `hotel_description` text DEFAULT NULL,
  `meals_included` varchar(100) DEFAULT NULL,
  `group_size` int(11) DEFAULT NULL,
  `hotel_link` varchar(255) DEFAULT NULL,
  `weather_info` text DEFAULT NULL,
  `travel_tips` text DEFAULT NULL,
  `culture_info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `excursion_details`
--

INSERT INTO `excursion_details` (`id`, `excursion_id`, `start_date`, `end_date`, `transportation`, `description`, `itinerary`, `title`, `activities`, `price`, `image1_url`, `image2_url`, `image3_url`, `image4_url`, `image5_url`, `hotel_name`, `hotel_rating`, `hotel_description`, `meals_included`, `group_size`, `hotel_link`, `weather_info`, `travel_tips`, `culture_info`) VALUES
(2, 2, '2025-12-24', '2025-12-27', 'Round-trip train from Geneva or Zurich included.\r\n\r\nLocal electric shuttle in Zermatt provided.', 'Imagine waking up in a cozy alpine chalet with a view of the iconic Matterhorn, its snow-draped peak glowing under the winter sun. Zermatt is a skier’s paradise, offering world-class slopes and breathtaking vistas at every turn. This elegant mountain village is car-free, so you\'ll travel by charming electric taxis and horse-drawn sleighs. Your 3-day excursion begins with a scenic train ride through the Swiss Alps, followed by a warm welcome dinner featuring authentic Swiss fondue. Spend your days skiing the pristine runs of the Matterhorn Glacier Paradise and snowshoeing through silent, powder-covered forests. Whether you\'re a winter sport enthusiast or simply seeking tranquility and luxury, Zermatt offers an unforgettable blend of adventure, romance, and serene beauty.', 'Day 1: Arrival in Zermatt by train; check-in at a 4-star alpine lodge. Evening welcome dinner with Swiss fondue.\r\n\r\nDay 2: Full-day guided skiing at Matterhorn Glacier Paradise. Equipment included.\r\n\r\nDay 3: Morning snowshoe hike with mountain views; departure after lunch.', 'Zermatt, Switzerland', 'Train ride through the Swiss Alps\r\n\r\nFull-day guided skiing on glacier slopes\r\n\r\nSnowshoe hike through forest trails\r\n\r\nTraditional fondue welcome dinner\r\n\r\nRelaxing lodge stay with mountain views', 890.00, 'zermatt.jpg', 'zermatt1.jpg', 'zermatt3.jpg', 'zermatt4.jpg', 'zermatt5.jpg', 'THE OMNIA', 5, 'A modern mountain lodge offering panoramic views of the Matterhorn, with luxurious amenities and spa facilities.', 'Breakfast included', 0, 'https://www.the-omnia.com/', 'Cold with frequent snowfall. Average winter temperatures range between -8°C and 2°C.', 'Pack thermal layers, ski gear, waterproof boots, and sunglasses for glare off the snow.', 'Locals speak German; English is widely understood in tourist areas.'),
(3, 3, '2026-01-06', '2026-01-08', 'Airport transfer and guided minibus excursions', 'Far above the Arctic Circle lies Tromsø, one of the best places on Earth to witness the magical Northern Lights. As night falls, you\'ll board a heated minibus with expert guides who take you deep into the wilderness to hunt the aurora in all its green and violet glory. During the day, immerse yourself in authentic Sami culture, feeding reindeer and gliding through snowy woods in a traditional sleigh. Rejuvenate each evening in a warm fjordside sauna while snowflakes dance on the glass windows. This 2-night adventure offers a perfect blend of natural wonder, cultural depth, and raw Arctic beauty. Whether you’re a solo traveler chasing northern skies or a couple looking for cozy magic, Tromsø will leave you breathless.', 'Night 1: Arrival and Northern Lights chase by minibus.\r\n\r\nDay 2: Visit to a Sami camp, reindeer feeding & sleigh ride. Evening sauna with fjord view.\r\n\r\nDay 3: Optional dog sledding or snowmobile ride. Departure in afternoon.', 'Tromsø, Norway – Chase the Northern Lights', 'Northern Lights hunt with local guides\r\n\r\nVisit to Sami reindeer camp\r\n\r\nReindeer feeding and sleigh ride\r\n\r\nFjordside sauna under the stars\r\n\r\nOptional dog sledding or snowmobiling', 740.00, 'tromso.jpg', 'tromso1.jpeg', 'tromso2.jpg', 'tromso3.jpg', 'tromso4.jpg', 'Clarion Hotel The Edge', 4, 'Contemporary hotel located by the waterfront, offering stunning views and easy access to city attractions.', 'Breakfast included', 7, 'https://www.nordicchoicehotels.com/hotels/norway/tromso/clarion-hotel-the-edge/', 'Arctic cold; temperatures between -10°C and 0°C with polar nights in winter.', 'Bring a headlamp, heavy-duty parka, hand warmers, and snow boots.', 'Norwegian is spoken, but English is very common. People are polite but reserved.'),
(4, 4, '2026-01-28', '2026-01-31', 'Airport transfer + sleds + snowmobiles', 'If there’s one place where winter magic becomes reality, it’s Finnish Lapland. Picture yourself riding across white landscapes on a husky sled, sipping hot berry juice by a fire in a log cabin, or stepping into a real-life ice hotel. This 4-day escape combines childlike wonder with natural beauty, starting with a visit to Santa Claus Village where the magic of Christmas is alive all year. Day two takes you through snowy forests pulled by huskies, followed by a relaxing evening in a glass igloo with views of the Northern Lights. It’s the ultimate winter fantasy come true, for families, couples, or anyone chasing snowy adventure.', 'Day 1: Arrival in Rovaniemi; visit to Santa Claus Village.\r\n\r\nDay 2: Husky sledding + wilderness lunch in a cabin.\r\n\r\nDay 3: Visit to snow hotel, ice bar, and igloo sauna.\r\n\r\nDay 4: Aurora viewing in heated sleigh before departure.', ' Lapland, Finland – Santa’s Snowy Wonderland', 'Meet Santa at Santa Claus Village\r\n\r\nHusky sledding tour through wilderness\r\n\r\nLunch in traditional Lappish cabin\r\n\r\nExplore ice hotel and ice bar\r\n\r\nRelax in glass igloo & sauna\r\n\r\nSleigh ride under Northern Lights', 990.00, 'laponia.jpg', 'laponia1.jpg', 'laponia2.jpg', 'laponia3.jpg', 'laponia4.jpg', 'Arctic TreeHouse Hotel', 5, 'Unique blend of luxury comfort in the heart of Arctic nature, featuring panoramic views of the forest and skies.', 'Breakfast included', 6, 'https://arctictreehousehotel.com/', 'Extremely cold, snowy winters with temps as low as -30°C.', 'Wear thermal underwear, layers, and carry lip balm and moisturizer.', 'Finnish and Sami languages; English is understood in resorts.'),
(5, 5, '2026-02-02', '2026-02-04', 'Daily tours by minibus with hotel pickup. Entry to Blue Lagoon included.', 'Experience the wild contrasts of Iceland — a place where fire meets ice. This 3-day adventure begins in Reykjavik and takes you through the iconic Golden Circle, where you’ll see erupting geysers, volcanic craters, and the thundering Gullfoss waterfall. On day two, explore the dramatic South Coast: hike across blue glaciers, visit black sand beaches, and marvel at frozen lava fields. Your journey ends with ultimate relaxation in the Blue Lagoon — Iceland’s world-famous geothermal spa. Perfect for nature lovers, photographers, and wellness seekers, this itinerary is packed with surreal landscapes and peaceful moments. Iceland in winter is wild, raw, and deeply rewarding.', 'Day 1: Golden Circle tour — geysers, waterfalls, Thingvellir Park.\r\n\r\nDay 2: South coast glacier hike + black sand beach.\r\n\r\nDay 3: Blue Lagoon spa visit, then departure.', 'Reykjavik, Iceland – Fire and Ice Explorer', 'Guided tour of Golden Circle landmarks\r\n\r\nGlacier hike with safety gear\r\n\r\nExplore Reynisfjara black sand beach\r\n\r\nEntry and soak in Blue Lagoon spa\r\n\r\nLava field sightseeing and coastal views', 670.00, 'reykjavik.jpg', 'reykjavik1.jpg', 'reykjavik2.jpg', 'reykjavik3.jpg', 'reykjavik4.jpg', 'Reykjavik EDITION', 5, 'Luxury hotel set against scenic mountain views, offering modern amenities and close to cultural landmarks.', 'Breakfast included', 2, 'https://www.marriott.com/en-us/hotels/rekeb-the-reykjavik-edition/overview/', 'Cool and windy. Summer average: 10–15°C; winter: 0 to -5°C.', 'Waterproof clothing and layers are key. Bring a swimsuit for hot springs.', 'Icelandic is spoken; nearly everyone speaks fluent English.'),
(11, 11, '2026-03-08', '2026-03-10', ' JR Pass access + local guides included', 'Spring transforms Kyoto into a poetic canvas of pink petals and timeless traditions. Explore Zen temples and gardens under a canopy of cherry blossoms, and immerse yourself in authentic Japanese rituals. You\'ll walk historic streets in a kimono, sip matcha during a tea ceremony, and bask in the beauty of sakura season — a deeply spiritual and aesthetic journey.', 'Day 1: Arrival + afternoon temple tour + tea ceremony\r\n\r\nDay 2: Cherry blossom trail + kimono dress-up\r\n\r\nDay 3: Arashiyama Bamboo Forest + departure', 'Kyoto, Japan – Cherry Blossom Cultural Journey', 'Cherry blossom walks along Philosopher’s Path\r\n\r\nTraditional tea ceremony experience\r\n\r\nKimono rental and guided temple visits\r\n\r\nBamboo forest exploration\r\n\r\nSeasonal food tasting', 820.00, 'kyoto.jpg', 'kyoto1.jpg', 'kyoto2.jpg', 'kyoto3.jpg', 'kyoto4.jpg', 'The Celestine Kyoto Gion', 4, 'Elegant retreat in Gion district with garden views and traditional Japanese ambiance.', 'Breakfast and tea ceremony included', 7, 'https://www.celestinehotels.jp/kyoto-gion/en/', 'Spring is mild with cherry blossoms blooming. Avg. 12–20°C.', 'Bring walking shoes, a light jacket, and a camera for cherry blossoms.', 'Japanese is spoken; basic English in tourist spots. Respect etiquette rules.'),
(12, 12, '2026-03-25', '2026-03-27', 'Bike rental + Keukenhof shuttle included', 'Amsterdam in spring is a celebration of light, color, and culture. Cruise through peaceful canals, ride past endless tulip fields, and stroll the Keukenhof Gardens bursting with floral brilliance. You\'ll explore charming Dutch villages, taste local cheese, and cycle alongside windmills. This trip is like stepping into a postcard — with pedals, petals, and plenty of pastries.', 'Day 1: Canal cruise + Jordaan walking tour\r\n\r\nDay 2: Keukenhof Gardens + windmill cycling tour\r\n\r\nDay 3: Van Gogh Museum + flower market', 'Amsterdam, Netherlands – Tulip Trails & Canal Days', 'Canal boat ride through the city\r\n\r\nVisit Keukenhof Tulip Gardens\r\n\r\nBiking past tulip fields and windmills\r\n\r\nVan Gogh Museum visit\r\n\r\nDutch street market and cheese tasting', NULL, 'amsterdam.jpg', 'amsterdam1.jpg', 'amsterdam2.jpg', 'amsterdam3.jpg', 'amsterdam4.jpg', 'Hotel Estherea', 4, 'Boutique hotel along Singel Canal with rich decor, chandeliers, and canal views.', 'Breakfast included', 9, 'https://www.estherea.nl/', 'Spring is cool and breezy with tulips blooming. Avg. 8–15°C.', 'Bring a light jacket, umbrella, and comfortable shoes for cycling.', 'Dutch is spoken; English is widely understood. Locals are open and informal.'),
(13, 13, '2026-04-03', '2026-04-05', 'Paris Pass for metro + cruise ticket included', 'Live a unique experience with local guides. Spring in Paris is everything you hope for — romantic, poetic, and bathed in sunlight. Sip espresso in historic cafés, stroll under cherry blossoms near the Eiffel Tower, and cruise along the Seine. Explore world-class museums and enjoy a picnic in the park. It’s the perfect escape for lovers of beauty, art, and life’s finer pleasures. Leave with your head filled with memories of the capital\'s enchanting views and a new perspective of this magnificent city.', 'Day 1: Garden walks + Eiffel Tower at sunset\r\n\r\nDay 2: Louvre visit + Seine river cruise\r\n\r\nDay 3: Montmartre stroll + food market brunch', 'Paris, France – Spring Romance in the City of Light', 'Visit Jardin du Luxembourg and Tuileries\r\n\r\nSeine river cruise at sunset\r\n\r\nEiffel Tower viewing and picnic\r\n\r\nLouvre Museum guided entry\r\n\r\nMontmartre art walk and brunch', 730.00, 'p2.jpg', 'p.jpg', 'p1.jpg', 'p5.jpg', 'p4.jpg', 'Paris France Hotel', 3, 'Historic hotel built in 1910, centrally located with elegantly decorated rooms and easy access to attractions.', 'Breakfast included', 7, 'https://www.paris-france-hotel.com/', 'Mild spring weather, avg. 10–18°C. Occasional rain.', 'Pack a scarf, light trench coat, and an umbrella.', 'French is spoken; English in tourist areas. Politeness is important.'),
(14, 14, '2025-05-06', '2025-05-09', 'City transfers + desert shuttle + walking tours included', 'Marrakech is a vibrant burst of energy in spring — the perfect blend of sun, souks, and sensory delight. Get lost in ancient medinas, soak in a traditional hammam, and ride camels across golden sands at sunset. Stay in a peaceful riad oasis and discover flavors, stories, and traditions unlike anywhere else.', 'Day 1: Medina tour + rooftop dinner\r\n\r\nDay 2: Jardin Majorelle + spa hammam\r\n\r\nDay 3: Camel ride in Agafay desert\r\n\r\nDay 4: Local market cooking class', 'Marrakech, Morocco – Souks, Spices & Spring Sun', 'Guided tour of Marrakech Medina & souks\r\n\r\nVisit Jardin Majorelle and Yves Saint Laurent Museum\r\n\r\nCamel trekking at sunset\r\n\r\nTraditional Moroccan hammam spa\r\n\r\nCooking class with local chef', 680.00, 'morocco.jpeg', 'morocco1.jpg', 'morocco2.jpg', 'morocco3.jpeg', 'morocco4.jpg', 'Riad Kheirredine', 5, 'A tranquil luxury riad in the Medina with traditional Moroccan design and modern comforts.', 'Breakfast and welcome dinner included', 12, 'https://www.riadkheirredine.com/', 'Warm spring days, avg. 20–30°C. Very sunny.', 'Bring sunblock, modest clothing, and stay hydrated.', 'Arabic and French spoken. English in hotels. Dress modestly and respect local customs.'),
(21, 21, '2025-06-18', '2025-04-20', 'Hotel pickup + port transfers + boat excursions included', 'Santorini is summer’s crown jewel — a romantic island of blue domes, whitewashed villages, and unforgettable sunsets. Spend your days exploring volcanic beaches, sailing along the caldera, and sipping local wine with views that will leave you breathless. Your 3-day escape includes a sunset catamaran cruise with dinner on board, a guided tour through Oia and Fira, and plenty of time to relax on black sand beaches. This is more than a trip — it’s a Greek postcard brought to life.', 'Day 1: Arrival + village walk + welcome dinner\r\n\r\nDay 2: Caldera sailing + hot springs swim\r\n\r\nDay 3: Beach day + sunset winery tour', 'Santorini, Greece – Sunset Sailing & Island Escape', 'Guided walk through Oia and Fira\r\n\r\nSunset catamaran cruise with dinner\r\n\r\nSwimming at volcanic hot springs\r\n\r\nWine tasting at a cliffside vineyard\r\n\r\nBeach relaxation on Perissa and Kamari', 725.00, 'santorini.jpg', 'santorini1.jpg', 'santorini2.jpg', 'santorini3.jpg', 'santorini4.jpg', 'Canaves Oia Suites', 5, 'Luxury suites carved into the cliff, offering caldera views and private plunge pools in Oia.', 'Breakfast and 1 dinner included', 10, 'https://www.canaves.com/', 'Hot and sunny summers. Avg. 26–34°C.', 'Pack sunscreen, swimwear, and sandals. Sun protection is a must.', 'Greek is spoken; English is common. Friendly and welcoming locals.'),
(22, 22, '2025-07-22', '2025-07-24', 'Barcelona metro card + airport shuttle + guided transfers', 'Barcelona combines vibrant culture, beachside beauty, and unforgettable architecture. Discover Gaudí’s masterpieces like Sagrada Família and Park Güell, wander down La Rambla, and soak up the Mediterranean sun on Barceloneta Beach. This summer city break offers the best of both worlds: culture by day, coastal fun by afternoon, and lively tapas nights in Gothic alleyways.', 'Day 1: Gothic Quarter + tapas tour\r\n\r\nDay 2: Sagrada Família + beach afternoon\r\n\r\nDay 3: Park Güell + Montjuïc cable car\r\n\r\nDay 4: Departure after brunch', 'Barcelona, Spain – City Life, Beach Vibes', 'Guided Gothic Quarter + La Rambla walk\r\n\r\nTour of Sagrada Família + skip-the-line entry\r\n\r\nAfternoon at Barceloneta Beach\r\n\r\nPark Güell architecture tour\r\n\r\nTapas crawl with wine pairing', 680.00, 'barcelona.jpg', 'barcelona1.jpg', 'barcelona2.jpg', 'barcelona3.jpg', 'barcelona4.jpeg', 'H10 Metropolitan', 4, 'Elegant hotel near Plaça Catalunya, featuring rooftop pool and modern design.', 'Breakfast included', 2, 'https://www.h10hotels.com/en/barcelona-hotels/h10-metropolitan', 'Warm and sunny summers. Avg. 25–32°C.', 'Wear light clothing, bring a sunhat, and watch your belongings in crowded areas.', 'Spanish and Catalan spoken. English is common. Casual but respectful culture.'),
(23, 23, '2025-08-04', '2025-08-07', 'Private coastal van + Capri ferry + hotel transfers', 'Italy’s Amalfi Coast is the summer dream: lemon-scented air, turquoise waters, and pastel towns stacked on cliff sides. This journey takes you through Positano, Ravello, and Amalfi via the world-famous coastal drive. Enjoy beachside dining, hike the “Path of the Gods,” and take a boat ride to the glamorous island of Capri. It\'s a sun-kissed mix of romance, nature, and fine Italian living.', 'Day 1: Amalfi & Positano village tours\r\n\r\nDay 2: Path of the Gods hike + Ravello\r\n\r\nDay 3: Capri island boat tour\r\n\r\nDay 4: Free morning + return', 'Amalfi Coast, Italy – Cliffside Villages & Seaside Trails', 'Visit Positano, Amalfi & Ravello\r\n\r\nScenic hike on the Path of the Gods\r\n\r\nIsland cruise to Capri + Blue Grotto visit\r\n\r\nLimoncello tasting + gelato workshop\r\n\r\nBeach relaxation with local seafood lunch', 750.00, 'amalfi.jpeg', 'amalfi1.jpeg', 'amalfi2.jpg', 'amalfi3.jpg', 'amalfi4.jpg', 'Hotel Marincanto', 4, 'Positano hotel with breathtaking views over the sea, terraces, and elegant Mediterranean décor.', 'Breakfast and 1 lunch included', 8, 'https://www.hotelmarincanto.it/', 'Hot and dry summers. Avg. 27–35°C.', 'Pack light clothing, comfortable walking shoes, and a water bottle.', 'Italian is spoken. Many locals speak basic English. Friendly and passionate people.'),
(24, 24, '2025-08-26', '2025-08-28', 'City pass + kayaking gear + island ferry', 'Step into Dubrovnik’s sunlit stone walls and feel like you’re in a movie set — because you are. This medieval gem offers stunning Adriatic views, Game of Thrones tours, and island hopping in crystal-clear waters. Kayak along the city walls, explore historic monasteries, and swim in hidden beaches only locals know.', 'Day 1: City walls tour + GOT landmarks\r\n\r\nDay 2: Kayak + island snorkel\r\n\r\nDay 3: Cable car to Srd + market brunch', 'Dubrovnik, Croatia – Adriatic Adventures & Old Town Magic', 'Guided walk along Dubrovnik city walls\r\n\r\nGame of Thrones filming locations tour\r\n\r\nSea kayaking and cave snorkeling\r\n\r\nFerry to Lokrum Island nature park\r\n\r\nRide the Srd cable car at sunset', 645.00, 'dubrovnik.jpg', 'dubrovnik1.jpg', 'dubrovnik2.jpg', 'dubrovnik3.jpg', 'dubrovnik4.jpg', 'Hotel Excelsior Dubrovnik', 5, 'Seafront luxury hotel with historic charm and direct views of Dubrovnik Old Town.', 'Breakfast included', 10, 'https://www.adriaticluxuryhotels.com/en/hotel-excelsior-dubrovnik', 'Sunny and warm. Avg. summer temps: 24–30°C.', 'Bring sunglasses, beachwear, and walking shoes for cobbled streets.', 'Croatian is spoken; English is widely understood. Locals are welcoming.'),
(31, 31, '2025-09-11', '2025-09-14', 'Car rental + fall color map provided', 'Fall in New England is pure magic — a kaleidoscope of reds, oranges, and golds lining quiet country roads and rustic towns. On this 4-day road trip, you’ll drive through Vermont and New Hampshire, stopping for apple picking, cider tasting, and forest hikes. Stay in cozy lodges, visit covered bridges, and enjoy a classic New England pumpkin festival. Perfect for nature lovers and photographers, this trip immerses you in autumn’s crisp, colorful glory.', 'Day 1: Arrive in Boston, drive to Vermont\r\n\r\nDay 2: Scenic leaf drive + cider tasting\r\n\r\nDay 3: Apple orchard + pumpkin fest\r\n\r\nDay 4: Covered bridge walk + return', 'New England, USA – Fall Foliage Road Trip & Farm Fun', 'Fall foliage road trip through Vermont\r\n\r\nVisit apple orchards + cider tasting\r\n\r\nAttend a local pumpkin festival\r\n\r\nCovered bridge photo tour\r\n\r\nScenic hikes in Green Mountains', 780.00, 'newengland.jpg', 'newengland1.jpg', 'newengland2.jpg', 'newengland3.jpg', 'newengland4.jpg', 'The Equinox Golf Resort & Spa', 4, 'Rustic yet elegant resort nestled in Vermont mountains, ideal for fall foliage.', 'Breakfast and 2 farm-style meals', 12, 'https://www.equinoxresort.com/', 'Cool, crisp fall air with foliage colors. Avg. 10–18°C.', 'Bring cozy sweaters, a camera, and hiking shoes.', 'English is spoken. Friendly small-town culture with emphasis on politeness.'),
(32, 32, '2025-10-20', '2025-10-22', 'City day pass + beer garden shuttle', 'Celebrate fall the Bavarian way with beer, bratwurst, and brass bands at the world-famous Oktoberfest. Explore Munich’s historic heart, visit grand palaces, and unwind in golden-leafed parks. This festive 3-night escape blends culture and cheer, from lederhosen-lined beer halls to tranquil autumn strolls through the English Garden.', 'Day 1: City tour + beer garden welcome\r\n\r\nDay 2: Oktoberfest celebration\r\n\r\nDay 3: Nymphenburg Palace + lake picnic\r\n\r\nDay 4: Departure', 'Munich, Germany – Oktoberfest & Autumn Traditions', 'Oktoberfest beer tent reservation + local guide\r\n\r\nBavarian city walking tour\r\n\r\nVisit to Nymphenburg Palace\r\n\r\nAutumn walk in the English Garden\r\n\r\nTraditional Bavarian dinner', 695.00, 'munich.jpg', 'munich1.jpg', 'munich2.jpg', 'munich3.jpg', 'munich4.jpg', 'Platzl Hotel', 4, 'Traditional Bavarian hotel near Marienplatz with modern comfort and beer hall atmosphere.', 'Breakfast and 1 beer dinner included', 14, 'https://www.platzl.de/en/', 'Cool autumn temps. Avg. 10–17°C. Occasional rain.', 'Wear layers, and bring a raincoat if attending Oktoberfest.', 'German is spoken. English is common. Beer culture and punctuality are important.'),
(33, 33, '2025-10-13', '2025-10-15', ' IstanbulCard + ferry + guided tours included', 'In fall, Istanbul’s domes shine in golden light, ferry rides are crisp and calm, and bazaars hum with life. Visit the Hagia Sophia, take a Bosphorus cruise, and get lost in historic alleyways filled with spices, silks, and stories. Fewer crowds, cooler days, and rich culture await in this perfect East-meets-West journey.', 'Day 1: Hagia Sophia + Grand Bazaar\r\n\r\nDay 2: Bosphorus cruise + rooftop dinner\r\n\r\nDay 3: Spice market + Blue Mosque', 'Istanbul, Turkey – East Meets West in Autumn Colors', 'Guided tour of Hagia Sophia + Blue Mosque\r\n\r\nBosphorus ferry cruise\r\n\r\nShopping at the Grand Bazaar and Spice Market\r\n\r\nTurkish tea with rooftop views\r\n\r\nOptional visit to Topkapi Palace', 670.00, 'istanbul.jpg', 'istanbul1.jpg', 'istanbul2.jpg', 'istanbul3.jpg', 'istanbul4.jpg', 'Hotel Amira Istanbul', 4, 'Charming hotel in Sultanahmet near Blue Mosque, offering rooftop views and personal service.', 'Breakfast included', 10, 'https://www.hotelamira.com/', 'Mild autumn weather. Avg. 15–22°C.', 'Pack modest clothing, comfortable shoes, and a scarf for mosque visits.', 'Turkish is spoken. English common in tourist areas. Hospitality is important.'),
(34, 34, '2025-11-09', '2025-11-11', 'Prague city card + guided walk + river cruise ticket', 'Prague in autumn is simply magical — cobblestone streets lined with amber trees, fairy-tale castles peeking through morning mist, and candlelit dinners in medieval taverns. Enjoy Charles Bridge at sunrise, tour Prague Castle, and unwind in a cozy café with Czech pastries and warm drinks.', 'Day 1: Castle tour + old town walk\r\n\r\nDay 2: River cruise + café hopping\r\n\r\nDay 3: Departure', 'Prague, Czech Republic – Gothic Romance & Golden Leaves', 'Guided tour of Prague Castle\r\n\r\nEarly-morning stroll across Charles Bridge\r\n\r\nCruise on the Vltava River\r\n\r\nCafé and pastry tour\r\n\r\nExplore Astronomical Clock + town square', 660.00, 'prague.jpg', 'prague1.jpg', 'prague2.jpg', 'prague3.jpg', 'prague4.jpg', 'Hotel Golden Angel', 4, 'Historic hotel in the city center with romantic ambiance and old-world charm.', 'Breakfast and welcome dinner', 10, 'https://www.goldenangelhotel.com/', 'Cool and crisp fall with golden trees. Avg. 8–16°C.', 'Bring a coat, umbrella, and warm shoes.', 'Czech is spoken. English is common. Locals are polite but reserved.'),
(222, 26, '2024-07-04', '2024-07-10', 'By the plane', 'Imagine swimming in a sunken volcano that teems with thousands of fish, while splashing in crystal clear water. On this Molokini snorkeling tour, cruise out to the crater, before jumping in the crystalline waters and scouring the colorful reef. Make a second stop dependent upon conditions, and search for turtles at Turtle Town or a spot named Coral Gardens. This family-friendly Maui activity is one of the island\'s most popular, and an unforgettable day of exploring the underwater world.', NULL, 'Reserve Now & Pay Later Molokini and Turtle Town Snorkeling ', 'Search for sea turtles and tropical fish\nCruise the island\'s scenic coast\nReceive onboard instruction and snorkeling gear\nContinental breakfast and deli lunch included', 259.00, ' ma.jpg', 'ma1.jpg', 'ma2.jpg', 'ma3.jpg', 'ma4.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_id` int(11) DEFAULT NULL,
  `action` enum('pending','replied','deleted') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--


-- --------------------------------------------------------

--
-- Table structure for table `seasons`
--

CREATE TABLE `seasons` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seasons`
--

INSERT INTO `seasons` (`id`, `title`, `description`, `image`) VALUES
(1, 'Discover the beauty of the seasons', 'Discover unforgettable adventures handpicked for every time of year. Whether you\'re chasing the sun, seeking snow-covered escapes, or craving cultural wonders, we’ll help you find the perfect destination — tailored to your travel vibe and season.', '14.jpg'),
(2, ' Winter Wonders', 'Cozy up to breathtaking snowy landscapes, Christmas markets, and ski resorts. Perfect for mountain adventures, spa retreats, and magical Northern Lights tours.', '2.jpg'),
(3, ' Spring Escapes', 'Enjoy blooming gardens, mild weather, and fewer crowds. Ideal for city breaks, cherry blossom viewing, and exploring Europe before peak season hits.', '3.jpg'),
(4, 'Summer Adventures', 'Sun, sea, and vibrant festivals. Go island hopping, relax on golden beaches, or dive into nature. Perfect for family getaways and outdoor thrills.', '4.jpg'),
(5, ' Fall Getaways', 'Travel through golden forests and vineyard-covered valleys. Experience cultural festivals, mild weather, and lower prices. Great for foodies and peaceful road trips.\r\n\r\n', '5.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `user_type` enum('admin','user') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `phone`, `user_type`) VALUES
(5, 'cipicipi', '$2y$10$aGbRVzQdlqVJ6G1Lu9EPO.lgbNDg85plDD/A3V7VaPsI9gphpE8mG', 'example@gmail.com', '0746038090', 'user'),
(18, 'admin2', '$2y$10$oG3KRS20DPATwK7tOXOUGuLv4jkJ5EPgaXwAX0bjF2ui.srr05iA2', 'example@gmail.com', '0746038090', 'admin'),
(21, 'anaMaria', '$2y$10$W6.zBouREmJhmHB53BNNruHufho/QCq8wCyyzDM9Bv0c17szBpoQK', 'example@gmail.com', '0746038090', 'user'),
(39, 'Carmelita', '$2y$10$n6.PCdW09PvkYYGEAp3Rk.M2kK.IC/BqCr8rCYXVxtD/Q.1SWPgeW', 'example@gmail.com', '89976546789', 'user'),
(41, 'anabella', '$2y$10$jqd0YcVhqopmml9/zUS8GeOP3BIwLNM.Im1KexiFCFErPpwvq.BlC', 'example@gmail.com', '89976546789', 'user'),
(42, 'admin1', '$2y$10$0G4WV8vJs8GaJ9OLg49UfezWdyGxKvkf1kXHtXXFAcNSIqiTbTATW', 'example@gmail.com', '0745997678', 'admin'),
(43, 'Clara', '$2y$10$fkvSqvufjqU1uFp5BA8FSeZi230xrqK.7nQFgyGV8aRjadVLIVkFy', 'example@gmail.com', '0725444999', 'user'),
(44, 'margo', '$2y$10$d7Z/cJI2VJrDOId0/MRSFOuz/HT8JQuP0.UaZkLChXua8m.5Ia9RK', 'example@gmail.com', '0746039999', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agencies`
--
ALTER TABLE `agencies`
  ADD PRIMARY KEY (`agency_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `password` (`password`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_ibfk_1` (`user_id`),
  ADD KEY `bookings_ibfk_2` (`excursion_id`);

--
-- Indexes for table `excursions`
--
ALTER TABLE `excursions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `excursions_ibfk_1` (`season_id`),
  ADD KEY `excursions_ibfk_2` (`travel_agency_id`);

--
-- Indexes for table `excursion_details`
--
ALTER TABLE `excursion_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `excursion_details_ibfk_1` (`excursion_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_admin_handling_message` (`admin_id`);

--
-- Indexes for table `seasons`
--
ALTER TABLE `seasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `password` (`password`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agencies`
--
ALTER TABLE `agencies`
  MODIFY `agency_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `excursions`
--
ALTER TABLE `excursions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `excursion_details`
--
ALTER TABLE `excursion_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=228;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `seasons`
--
ALTER TABLE `seasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`excursion_id`) REFERENCES `excursions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `excursions`
--
ALTER TABLE `excursions`
  ADD CONSTRAINT `excursions_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `excursions_ibfk_2` FOREIGN KEY (`travel_agency_id`) REFERENCES `agencies` (`agency_id`) ON DELETE CASCADE;

--
-- Constraints for table `excursion_details`
--
ALTER TABLE `excursion_details`
  ADD CONSTRAINT `excursion_details_ibfk_1` FOREIGN KEY (`excursion_id`) REFERENCES `excursions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_admin_handling_message` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
