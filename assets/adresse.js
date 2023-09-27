function initAdresse() {
    affichageMapAdrese();
    console.log('je suis dans initAdresse')
}
window.initAdresse = initAdresse;

function affichageMapAdrese(){

    mapAdresse = L.map('mapAdresse').setView([47.2264, -1.62076], 15);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright"></a>'
    }).addTo(mapAdresse);
    mapAdresse.on('click', onMapClick);
}

function onMapClick(e) {
    let latitude = e.latlng.lat;
    let longitude = e.latlng.lng;
    let str = "latitude : " + latitude + "longitude : " + longitude ;

    let balise  = document.createElement("a");
    balise.setAttribute("id", 'boutonCreaMap');
    let createAText = document.createTextNode(str);
    balise.appendChild(createAText);
    let popup = L.popup();

    popup

        .setLatLng(e.latlng)
        .setContent(balise)
        .openOn(mapAdresse)

    console.log('je suis dans le map on click');
    let longitudeText = document.getElementById('longitude');
    longitudeText.value = longitude;
    let latitudeText =document.getElementById('latitude')
    latitudeText.value = latitude;
}

function flyToAdresse() {
    var adresse = document.getElementById("adresseUser").value;
    let adresseFinal = adresse.replaceAll(" ", "+");
    fetch(`https://nominatim.openstreetmap.org/search?q=${adresseFinal}&format=geojson`)
        .then(res => res.json())
        .then(json => {
            let coordonnees = json['features'][0]['geometry']['coordinates'];
            let rue = json['features'][0]['properties']['name'];
            let longitude = coordonnees[0];
            console.log(longitude);
            let latitude = coordonnees[1];
            console.log(latitude);
            mapAdresse.flyTo([latitude, longitude], 16);
            let adresseForm = longitude + '+' + latitude;

            // Affectez la valeur à l'input du formulaire
            document.getElementById('registration_form_adresse').value = adresseForm;
            console.log('je suis après le value');
        });
}
window.flyToAdresse = flyToAdresse;
