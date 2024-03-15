function initMessage (){
    console.log('hello');
    const boutonsLire = document.querySelectorAll('.lireMessage');
    boutonsLire.forEach(function (bouton) {
        bouton.addEventListener('click', function (event) {
            const messageId = event.target.dataset.messageId;

            showMessage(messageId);
        });
    });
    const boutonsVu = document.querySelectorAll('.messageVu');
    boutonsVu.forEach(function (bouton) {
        bouton.addEventListener('click', function (event) {
            const messageId = event.target.dataset.messageId;

            messageVu(messageId);
        });
    });
}
window.initMessage = initMessage;

function showMessage(id){
    let messageALire = document.getElementById('messageNum_' + id);
    if(messageALire){
        if(messageALire.classList.contains('hidden')){
            messageALire.classList.remove('hidden');
        } else {
            messageALire.classList.add('hidden')
        }
    }
}

function messageVu(id){
    console.log('vu');
    let image = document.getElementById('imageMessage'+id);
    image.src ="images/valide.png";
    let url = 'https://127.0.0.1:8000/messageVerifier';
    let options = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(id)
    };
    fetch(url, options)
        .then(response => response.json())
        .then(data => {
            console.log('Réponse du serveur : ', data); // voir stock.js
        })
        .catch(error => {
            console.error('Erreur lors de la requête : ', error);
        });
}