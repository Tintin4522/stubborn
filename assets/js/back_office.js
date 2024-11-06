function previewImage(event) {
    const imagePreview = document.getElementById('image-preview');
    imagePreview.innerHTML = ""; 

    const file = event.target.files[0];
    if (file) {
        if (file.type.startsWith('image/')) { 
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement("img");
                img.src = e.target.result;
                img.style.maxWidth = '100%'; 
                img.style.maxHeight = '100%'; 
                imagePreview.appendChild(img); 
            };
            reader.readAsDataURL(file); 
        } else {
            alert('Veuillez sélectionner une image valide.'); 
        }
    }
}

