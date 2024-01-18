function initAdresse() {
    affichageMapAdrese();
}

window.initAdresse = initAdresse;
const accesToken = "pk.2716a119e7e25728fabf60cdc7af03e7";
function affichageMapAdrese() {

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
    clickToAdresse(longitude, latitude);
    envoiCoordonnee(longitude, latitude);
    let balise = document.createElement("a");
    balise.setAttribute("id", 'boutonCreaMap');
    let createAText = document.createTextNode('Lieu sélectionné');
    balise.appendChild(createAText);
    let popup = L.popup();
    popup

        .setLatLng(e.latlng)
        .setContent(balise)
        .openOn(mapAdresse)
}

function clickToAdresse(longitude, latitude) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=xml&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1`)
        .then(res => res.text())
        .then(xmlData => {
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(xmlData, "text/xml");
            const adresse = xmlDoc.querySelector("result").textContent;
            console.log(adresse);
            document.getElementById('adresseUser').innerText = adresse ;
            document.getElementById('adresseUser').value = adresse ;
        });
}

function showAdresse() {
    var adresse = document.getElementById("adresseUser").value;
    let conteneur = document.getElementById('conteneurAdresse');
    conteneur.innerHTML = "";
    let adresseModif = encodeURIComponent(adresse); // Encodage de l'adresse pour l'URL
    fetch(`https://api.locationiq.com/v1/autocomplete?key=${accesToken}&q=${adresseModif}&limit=7&dedupe=1&`)
        .then(res => res.json())
        .then(json => {
            json.forEach((result, index) => {
                let nom = result.display_name; // Accéder au nom de chaque résultat
                let option = document.createElement("option");
                option.text = nom;
                option.value = index; // Vous pouvez utiliser index comme valeur ou une autre valeur unique

                conteneur.appendChild(option);
            });
            conteneur.addEventListener('click', function(event) {
                let selectedIndex = event.target.value;
                let selectedText = conteneur.options[selectedIndex].text;
                flyToAdresse(selectedText);
            });
        });
}
window.showAdresse = showAdresse;

function flyToAdresse(texte) {
    console.log(texte);
    let adresse = encodeURIComponent(texte);
    console.log(adresse);
    fetch(`https://us1.locationiq.com/v1/search?key=${accesToken}&q=${adresse}&format=json&`)
        .then(res => res.json())
        .then(json => {

            let longitude = json[0].lon;
            let latitude = json[0].lat;
            console.log(latitude);
            console.log(longitude);
            mapAdresse.flyTo([latitude, longitude], 17);
            document.getElementById('latitude_form').value = latitude;
            document.getElementById('longitude_form').value = longitude;
        });

    document.getElementById('adresseUser').value = "" ;
    document.getElementById('adresseUser').value = texte ;
}
window.flyToAdresse = flyToAdresse;

function flyToAdresseCoordonnee(latitude, longitude) {
    mapAdresse.flyTo([latitude, longitude], 17);

}
window.flyToAdresseCoordonnee = flyToAdresseCoordonnee;
function envoiCoordonnee(longitude, latitude){
    let latitudeElement = document.getElementById('latitude_form');
    let longitudeElement = document.getElementById('longitude_form');

        latitudeElement.value = latitude;
        longitudeElement.value = longitude;


}