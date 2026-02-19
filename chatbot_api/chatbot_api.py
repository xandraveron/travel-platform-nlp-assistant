
from flask import Flask, request, jsonify, session
from flask_cors import CORS
import random
import spacy
import mysql.connector
import logging
from responses import responses, keywords
from difflib import get_close_matches
from datetime import timedelta
import re


app = Flask(__name__)
CORS(app, supports_credentials=True)  
app.secret_key = 'dev_only_change_it'
logging.basicConfig(level=logging.DEBUG)
nlp = spacy.load("en_core_web_sm")
app.permanent_session_lifetime = timedelta(minutes=10)

DB_CONFIG = {
    "host": "localhost",
    "user": "change_it",
    "password": "",
    "database": "travel"
}

def detect_intent(message):
    message = message.lower()
    if any(p in message for p in ["when does", "trip start", "when is it", "start date", "when will it begin"]):
        return "dates"
    if any(p in message for p in ["itinerary", "schedule", "duration", "timeline", "what’s the plan"]):
        return "itinerary"
    if any(p in message for p in ["do they speak english", "language","speak","speaking","locals", "etiquette", "friendly", "safe", "culture", "customs","spoken"]):
        return "culture"
    if any(p in message for p in ["how much", "cost", "price", "expensive", "what’s the price", "rate"]):
        return "price"
    if any(p in message for p in ["what to do", "things to do", "activities", "can i do", "what can i do", "do there", "see", "visit", "special"]):
        return "activities"
    if any(p in message for p in ["best trip", "where to go", "trip in", "travel in", "go this", "recommend", "suggest", "spend my time", "spend this", "spend my", "go", "best way"]):
        return "season_trip"
    if any(p in message for p in ["where do i stay", "what hotel", "accommodation", "which hotel", "stay at","hotel"]):
        return "hotel"
    if any(p in message for p in ["meals", "food", "included meals", "do we eat", "breakfast", "lunch", "dinner"]):
        return "meals"
    if any(p in message for p in ["how many seats", "group size", "maximum number of travelers", "how many seats are available"]):
        return "group_size"
    if any(p in message for p in ["weather", "climate", "temperature", "cold", "hot", "raining"]):
        return "weather"
    if any(p in message for p in ["what to pack", "bring", "prepare", "sunscreen", "jacket", "take with me"]):
        return "travel_tips"
    if any(p in message for p in [
        "get me the full info", "all the details", "tell me all about that trip","i want the full trip info",
        "give me all the details", "give me all the info", "i want to know more about",
        "tell me everything about", "i want everything about", "tell me more",
        "full trip info", "full info", "entire description", "trip details","get me the full info",
        "all the details",
        "tell me all about that trip",
        "give me all the details",
        "give me all the info",
        "i want to know more about",
        "tell me everything about",
        "i want everything about",
        "tell me more",
        "full trip info",
        "full info",
        "the entire description"
    ]):
        return "details"
    if any(word in message for word in keywords["greeting"]):
        return "greeting"
    
    if any(p in message for p in ["goodbye", "bye", "see you", "no thanks", "no, thank you", "nope", "that's all"]):
        return "goodbye"

    if any(p in message.lower() for p in ["thank you", "thanks", "thx", "thank u"]):
        return "thankyou"

    if any(p in message.lower() for p in ["no", "nope", "not really", "no thank you"]):
        return "no_followup"


    return "unknown"


def fetch_all_excursion_titles():
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        cursor.execute("SELECT title FROM excursions")
        titles = [row[0] for row in cursor.fetchall()]
        cursor.close()
        conn.close()
        return titles
    except:
        return []

excursion_titles = fetch_all_excursion_titles()

def extract_place(message):
    doc = nlp(message)
    words = [token.text.lower() for token in doc if not token.is_stop]
    for title in excursion_titles:
        parts = [part.strip().lower() for part in title.split(",")]
        if any(part in words for part in parts):
            return title
    for ent in doc.ents:
        if ent.label_ == "GPE":
            return ent.text
    return None

def match_excursion_name(query):
    if not query:
        return None
    query = query.lower()
    matches = get_close_matches(query, [t.lower() for t in excursion_titles], n=1, cutoff=0.6)  
    if matches:
        for title in excursion_titles:
            if title.lower() == matches[0]:
                return title
    return None


def get_trip_full_details(title):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT e.title, ed.price,ed.transportation, ed.start_date, ed.end_date, ed.activities, ed.description,
                   ed.itinerary, ed.hotel_name, ed.hotel_rating, ed.hotel_description, ed.hotel_link,
                   ed.meals_included, ed.group_size
            FROM excursion_details ed
            JOIN excursions e ON ed.excursion_id = e.id
        """)
        all_rows = cursor.fetchall()
        cursor.close()
        conn.close()

        for r in all_rows:
            if title.lower() in r["title"].lower():
                full = (
                    f" <b>Trip:</b> {r['title']} — <b>{r['price']} EUR</b><br>"
                    f" <b>Dates:</b> {r['start_date']} to {r['end_date']}<br>"
                    f" <b>Description:</b> {r['description']}<br>"
                    f" <b>Activities:</b> {r['activities']}<br>"
                    f" <b>Transportation:</b> {r['transportation']}<br>"
                    f" <b>Itinerary:</b> {r['itinerary']}"
                )

                #Optional: Hotel info
                if r.get("hotel_name"):
                    full += f"<br><br> <b>Hotel:</b> {r['hotel_name']} ({r['hotel_rating']}⭐)<br>{r['hotel_description']}"
                    if r.get("hotel_link"):
                        full += f"<br><a href='{r['hotel_link']}' target='_blank'> View Hotel</a>"
                    else:
                        full += "<br> No hotel website"

                # Optional:meals
                if r.get("meals_included"):
                    full += f"<br> <b>Meals Included:</b> {r['meals_included']}"

                # Optional:seats left for booking
                if r.get("group_size"):
                    full += f"<br> <b>Seats left:</b> {r['group_size']}"

                return full

        return "Sorry, I couldn’t find that trip."
    except Exception as e:
        return f"Database error: {e}"


def get_price_only(title):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT e.title, ed.price FROM excursions e JOIN excursion_details ed ON ed.excursion_id = e.id")
        rows = cursor.fetchall()
        cursor.close()
        conn.close()
        for row in rows:
            if title.lower() in row["title"].lower():
                return f" <b>{row['title']}</b>: {row['price']} EUR<br>Would you like the full trip details?"
        return "Price info not found."
    except Exception as e:
        return f"Database error: {e}"

def get_activities_only(title):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT e.title, ed.activities FROM excursions e JOIN excursion_details ed ON ed.excursion_id = e.id")
        rows = cursor.fetchall()
        cursor.close()
        conn.close()
        for row in rows:
            if title.lower() in row["title"].lower():
                return f" <b>Activities in {row['title']}:</b><br>{row['activities']}<br><br>Would you like the full trip details?"
        return "Activities not found."
    except Exception as e:
        return f"Database error: {e}"
    
def get_transportation(title):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT e.title, ed.transportation
            FROM excursions e
            JOIN excursion_details ed ON ed.excursion_id = e.id
        """)
        results = cursor.fetchall()
        cursor.close()
        conn.close()

        for r in results:
            if title.lower() in r['title'].lower():
                return f" <b>Transportation for {r['title']}:</b> {r['transportation']}"
        return "Transportation details not found."
    except Exception as e:
        return f"Database error: {e}"



def get_dates(title):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT e.title, ed.start_date, ed.end_date
            FROM excursions e
            JOIN excursion_details ed ON ed.excursion_id = e.id
        """)
        data = cursor.fetchall()
        cursor.close()
        conn.close()
        for r in data:
            if title.lower() in r["title"].lower():
                return f" <b>Trip Dates for {r['title']}:</b> {r['start_date']} to {r['end_date']}<br>Would you like the full trip info too?"
        return "Dates not found."
    except Exception as e:
        return f"Database error: {e}"
    

def get_itinerary(title):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT e.title, ed.itinerary FROM excursions e JOIN excursion_details ed ON ed.excursion_id = e.id")
        rows = cursor.fetchall()
        cursor.close()
        conn.close()
        for row in rows:
            if title.lower() in row["title"].lower():
                return f" <b>Itinerary for {row['title']}:</b><br>{row['itinerary']}<br>Would you like the full description too?"
        return "Itinerary not found."
    except Exception as e:
        return f"Database error: {e}"
    


def get_weather_info(title):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT e.title, ed.weather_info FROM excursions e JOIN excursion_details ed ON ed.excursion_id = e.id")
        data = cursor.fetchall()
        cursor.close()
        conn.close()
        for r in data:
            if title.lower() in r["title"].lower():
                return f" <b>Weather in {r['title']}:</b> {r['weather_info']}"
        return "Sorry, I couldn’t find weather info for that trip."
    except Exception as e:
        return f"Database error: {e}"

def get_travel_tips(title):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT e.title, ed.travel_tips FROM excursions e JOIN excursion_details ed ON ed.excursion_id = e.id")
        data = cursor.fetchall()
        cursor.close()
        conn.close()
        for r in data:
            if title.lower() in r["title"].lower():
                return f" <b>Travel Tips for {r['title']}:</b> {r['travel_tips']}"
        return "No travel prep tips found."
    except Exception as e:
        return f"Database error: {e}"

def get_culture_info(title):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT e.title, ed.culture_info FROM excursions e JOIN excursion_details ed ON ed.excursion_id = e.id")
        data = cursor.fetchall()
        cursor.close()
        conn.close()
        for r in data:
            if title.lower() in r["title"].lower():
                return f" <b>Culture & Language in {r['title']}:</b> {r['culture_info']}"
        return "No cultural info available."
    except Exception as e:
        return f"Database error: {e}"



def get_best_trips_for_season(season):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT e.title, ed.price FROM excursions e
            JOIN excursion_details ed ON ed.excursion_id = e.id
            JOIN seasons d ON e.season_id = d.id
            WHERE LOWER(d.title) LIKE %s
            ORDER BY ed.price ASC LIMIT 4
        """, (f"%{season.lower()}%",))
        rows = cursor.fetchall()
        cursor.close()
        conn.close()
        if rows:
            msg = f" <b>Top Trips for {season.capitalize()}</b>:<br>"
            for r in rows:
                msg += f" <b>{r['title']}</b><br> {r['price']} EUR<br><br>"
            msg += "Would you like more details about one of them?"
            return msg
        return "No trips found for that season."
    except Exception as e:
        return f"Database error: {e}"



def get_hotel_info(title):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT e.title, ed.hotel_name, ed.hotel_rating, ed.hotel_description, ed.hotel_link
            FROM excursions e
            JOIN excursion_details ed ON ed.excursion_id = e.id
        """)
        data = cursor.fetchall()
        cursor.close()
        conn.close()

        for r in data:
            if title.lower() in r["title"].lower():
                hotel_link = r.get('hotel_link')
                link_text = f"<a href='{hotel_link}' target='_blank'> View Hotel Website</a>" if hotel_link else " No website available"
                return (
                    f" <b>Hotel for {r['title']}:</b><br>"
                    f"<b>{r['hotel_name']}</b> ({r['hotel_rating']}⭐)<br>"
                    f"{r['hotel_description']}<br>"
                    f"{link_text}"
                )
        return "Sorry, I couldn’t find hotel info for that trip."
    except Exception as e:
        return f"Database error: {e}"




def get_meals_info(title):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT e.title, ed.meals_included
            FROM excursions e
            JOIN excursion_details ed ON ed.excursion_id = e.id
        """)
        rows = cursor.fetchall()
        cursor.close()
        conn.close()

        for r in rows:
            if title.lower() in r["title"].lower():
                return f" <b>Meals for {r['title']}:</b> {r['meals_included']}"
        return "No meal information available."
    except Exception as e:
        return f"Database error: {e}"



def get_group_size_info(title):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT e.title, ed.group_size
            FROM excursions e
            JOIN excursion_details ed ON ed.excursion_id = e.id
        """)
        rows = cursor.fetchall()
        cursor.close()
        conn.close()

        for r in rows:
            if title.lower() in r["title"].lower():
                return f" <b>Group Size for {r['title']}:</b> {r['group_size']} travelers max"
        return "Group size info not found."
    except Exception as e:
        return f"Database error: {e}"


def add_to_memory(user_msg, bot_response):
    history = session.get("history", [])
    history.append({"user": user_msg, "bot": bot_response})
    session["history"] = history[-10:]





@app.route('/chat', methods=['GET'])
def chat():
    user_message = request.args.get('message', '').strip()
    session.permanent = True
    lower = user_message.lower()

    # detect intent and place from messages
    intent = detect_intent(lower)
    place = extract_place(user_message)
    matched_place = match_excursion_name(place)

    # load session memory
    last_place = session.get("last_place", "")
    last_intent = session.get("last_intent", "")

    #  store matched_place if it was found
    if matched_place:
        session["last_place"] = matched_place
        print(" Stored matched_place in session:", matched_place)

    # store intent only if known
    if intent != "unknown":
        session["last_intent"] = intent
        print(" Stored intent in session:", intent)

    # fallback - if intent unknown but session had info
    if intent == "unknown" and not matched_place and last_place and last_intent in [
        "price", "details", "activities", "itinerary", "hotel", "meals",
        "group_size", "weather", "travel_tips", "culture"]:
        intent = last_intent
        matched_place = last_place
        print(" Restored intent + place from session")

    # fallback - if intent is known but no matched_place
    if intent in [
        "price", "details", "activities", "itinerary", "hotel", "meals",
        "group_size", "weather", "travel_tips", "culture"
    ] and not matched_place and last_place:
        matched_place = last_place
        print(" Reused last_place as matched_place:", matched_place)

    #  logging for debugging the way the intent/place is changed and recognised
    print(" USER:", user_message)
    print(" INTENT:", intent)
    print(" matched_place:", matched_place)
    print(" last_place (in session):", session.get("last_place"))
    import sys; sys.stdout.flush()

    
    follow_up = any(x in lower for x in [
        "yes", "tell me more", "ok", "alright", "i want all the info", "everything about it", 
        "show me everything", "give me the full description", "show me full trip", "more informations", "the details","all the details","know more","what about the"
    ])


    if intent == "unknown" and follow_up:
    # Prioritize newly mentioned destination
        if matched_place:
            session["last_place"] = matched_place
            session["last_intent"] = "details"
            response = get_trip_full_details(matched_place)
            return jsonify(response=response, memory_place=matched_place, memory_intent="details")

        # Fallback to remembered place
        if last_place:
            response = get_trip_full_details(last_place)
            return jsonify(response=response, memory_place=last_place, memory_intent="details")

        return jsonify(response="Which destination would you like details about?", memory_place="", memory_intent="")


    multi_field_triggers = {
        "price": ["price", "cost", "how much", "rate", "fee"],
        "transportation": ["transport", "transfer", "how to get", "how do i travel", "bus", "train", "metro"],
        "activities": ["what to do", "activities", "things to do", "can i do", "do there", "see", "visit", "special"],
        "itinerary": ["itinerary", "plan", "schedule", "timeline", "what’s the plan"],
        "hotel": ["hotel", "where do i stay", "what hotel", "accommodation", "which hotel", "stay at","the hotel"],
        "meals": ["meals", "food", "included meals", "do we eat", "breakfast", "lunch", "dinner"],
        "group_size": ["group size", "how many people", "maximum travelers", "travelers in a group"],
        "weather": ["weather", "climate", "temperature", "cold", "hot", "raining"],
        "travel_tips": ["what to pack", "bring", "prepare", "sunscreen", "jacket", "take with me"],
        "culture": ["do they speak english", "language", "speak", "speaking", "locals", "etiquette", "friendly", "safe", "culture", "customs"]
    }


    if matched_place:
        combined_response_parts = []
        for key, keywords_list in multi_field_triggers.items():
            if any(re.search(r'\b' + re.escape(k) + r'\b', lower) for k in keywords_list):
                if key == "price":
                    combined_response_parts.append(get_price_only(matched_place))
                elif key == "transportation":
                    combined_response_parts.append(get_transportation(matched_place))
                elif key == "activities":
                    combined_response_parts.append(get_activities_only(matched_place))
                elif key == "itinerary":
                    combined_response_parts.append(get_itinerary(matched_place))
                elif key == "hotel":
                    combined_response_parts.append(get_hotel_info(matched_place))
                elif key == "meals":
                    combined_response_parts.append(get_meals_info(matched_place))
                elif key == "group_size":
                    combined_response_parts.append(get_group_size_info(matched_place))
                elif key == "weather":
                    combined_response_parts.append(get_weather_info(matched_place))
                elif key == "travel_tips":
                    combined_response_parts.append(get_travel_tips(matched_place))
                elif key == "culture":
                    combined_response_parts.append(get_culture_info(matched_place))

            # If more than one type of info was matched, return the combined answer
        if len(combined_response_parts) > 1:
            response = "<br><br>".join(combined_response_parts)
            return jsonify(response=response, memory_place=matched_place, memory_intent="multi_field")

    if intent == "itinerary":
        if matched_place:
            session["last_place"] = matched_place
            session["last_intent"] = "itinerary"
            response = get_itinerary(matched_place)
            return jsonify(response=response, memory_place=matched_place, memory_intent="itinerary")
        elif last_place:
            response = get_itinerary(last_place)
            return jsonify(response=response, memory_place=last_place, memory_intent="itinerary")
        else:
            return jsonify(response="Tell me which trip you're asking about for itinerary.")


    if intent == "price":
        if matched_place:
            session["last_place"] = matched_place
            session["last_intent"] = "price"
            response = get_price_only(matched_place)
            return jsonify(response=response, memory_place=matched_place, memory_intent="price")
        elif last_place:
            response = get_price_only(last_place)
            return jsonify(response=response, memory_place=last_place, memory_intent="price")
        else:
            return jsonify(response="Which trip do you want the price for?")

    if intent == "activities":
        if matched_place:
            session["last_place"] = matched_place
            session["last_intent"] = "activities"
            response = get_activities_only(matched_place)
            return jsonify(response=response, memory_place=matched_place, memory_intent="activities")
        elif last_place:
            response = get_activities_only(last_place)
            return jsonify(response=response, memory_place=last_place, memory_intent="activities")
        else:
            return jsonify(response="Tell me which destination you're asking about for activities.")


    if intent == "season_trip":
        season = None
        for s in ["summer", "winter", "spring", "fall", "autumn"]:
            if s in lower:
                season = s.capitalize()
                break

        place = extract_place(user_message)
        matched_place = match_excursion_name(place)

        if matched_place:
            response = get_trip_full_details(matched_place)
            session["last_place"] = matched_place
            session["last_intent"] = intent
            return jsonify(response=response, memory_place=matched_place, memory_intent="details")

        if season:
            response = get_best_trips_for_season(season)
            
            return jsonify(response=response, memory_place="", memory_intent="season_trip")

    if intent == "hotel":
        if matched_place:
            session["last_place"] = matched_place
            session["last_intent"] = "hotel"
            response = get_hotel_info(matched_place)
            return jsonify(response=response, memory_place=matched_place, memory_intent="hotel")
        elif last_place:
            response = get_hotel_info(last_place)
            return jsonify(response=response, memory_place=last_place, memory_intent="hotel")
        else:
            return jsonify(response="Tell me which trip you're asking about for hotel info.")

    if intent == "meals":
        if matched_place:
            session["last_place"] = matched_place
            session["last_intent"] = "meals"
            response = get_meals_info(matched_place)
            return jsonify(response=response, memory_place=matched_place, memory_intent="meals")
        elif last_place:
            response = get_meals_info(last_place)
            return jsonify(response=response, memory_place=last_place, memory_intent="meals")
        else:
            return jsonify(response="Tell me which trip you're asking about for meals.")

    if intent == "group_size":
        if matched_place:
            session["last_place"] = matched_place
            session["last_intent"] = "group_size"
            response = get_group_size_info(matched_place)
            return jsonify(response=response, memory_place=matched_place, memory_intent="group_size")
        elif last_place:
            response = get_group_size_info(last_place)
            return jsonify(response=response, memory_place=last_place, memory_intent="group_size")
        else:
            return jsonify(response="Let me know which trip you're asking about for group size.")

    if intent == "weather":
        if matched_place:
            session["last_place"] = matched_place
            session["last_intent"] = "weather"
            response = get_weather_info(matched_place)
            return jsonify(response=response, memory_place=matched_place, memory_intent="weather")
        elif last_place:
            response = get_weather_info(last_place)
            return jsonify(response=response, memory_place=last_place, memory_intent="weather")
        else:
            return jsonify(response="Tell me which trip you're asking about for weather.")

    if intent == "travel_tips":
        if matched_place:
            session["last_place"] = matched_place
            session["last_intent"] = "travel_tips"
            response = get_travel_tips(matched_place)
            return jsonify(response=response, memory_place=matched_place, memory_intent="travel_tips")
        elif last_place:
            response = get_travel_tips(last_place)
            return jsonify(response=response, memory_place=last_place, memory_intent="travel_tips")
        else:
            return jsonify(response="Tell me which trip you're asking about for travel tips.")

    if intent == "culture":
        if matched_place:
            session["last_place"] = matched_place
            session["last_intent"] = "culture"
            response = get_culture_info(matched_place)
            return jsonify(response=response, memory_place=matched_place, memory_intent="culture")
        elif last_place:
            response = get_culture_info(last_place)
            return jsonify(response=response, memory_place=last_place, memory_intent="culture")
        else:
            return jsonify(response="Tell me which destination you’re asking about for culture or language.")

    if intent == "details":
        if matched_place:
            session["last_place"] = matched_place
            session["last_intent"] = "details"
            response = get_trip_full_details(matched_place)
            return jsonify(response=response, memory_place=matched_place, memory_intent="details")
        elif last_place:
            response = get_trip_full_details(last_place)
            return jsonify(response=response, memory_place=last_place, memory_intent="details")
        else:
            return jsonify(response="Which trip do you want the full info for?", memory_place="", memory_intent="unknown")
        
    if intent == "thankyou":
        return jsonify(response=random.choice(responses["thankyou"]),
                    memory_place=session.get("last_place", ""),
                    memory_intent="greeting")

    if intent == "no_followup":
        return jsonify(response=random.choice(responses["goodbye"]),
                    memory_place=session.get("last_place", ""),
                    memory_intent="goodbye")

    if intent == "goodbye":
        return jsonify(response=random.choice(responses["goodbye"]),
                    memory_place=session.get("last_place", ""),
                    memory_intent="goodbye")


    if matched_place:
        session["last_place"] = matched_place
        session["last_intent"] = intent
        response = f"{matched_place} sounds great! Want to know details like the price, activities, itinerary or the entire description ?"
        return jsonify(response=response, memory_place=matched_place, memory_intent=intent)

    response = random.choice(responses.get(intent, responses["unknown"]))
    return jsonify(response=response, memory_place="", memory_intent=intent)



if __name__ == '__main__':
    app.run(debug=True)


