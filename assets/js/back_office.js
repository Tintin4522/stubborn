function previewImage(event) {
    const imagePreview = document.getElementById('image-preview');
    imagePreview.innerHTML = ""; // Efface tout contenu existant

    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement("img");
            img.src = e.target.result;
            imagePreview.appendChild(img); // Ajoute l'image à l'aperçu
        };
        reader.readAsDataURL(file); // Convertit le fichier en base64 pour l'affichage
    }
}