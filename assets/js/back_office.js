function previewImage(event) {
    const imagePreview = document.getElementById('image-preview');
    imagePreview.innerHTML = ""; // Efface tout contenu existant

    const file = event.target.files[0];
    if (file) {
        if (file.type.startsWith('image/')) { // Vérifie si le fichier est une image
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement("img");
                img.src = e.target.result;
                img.style.maxWidth = '100%'; // Ajuste la taille de l'image
                img.style.maxHeight = '100%'; // Ajuste la taille de l'image
                imagePreview.appendChild(img); // Ajoute l'image à l'aperçu
            };
            reader.readAsDataURL(file); // Convertit le fichier en base64 pour l'affichage
        } else {
            alert('Veuillez sélectionner une image valide.'); // Message d'erreur pour fichier non valide
        }
    }
}