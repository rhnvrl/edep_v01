document.addEventListener('DOMContentLoaded', function() {

    // ========== ÖN YÜZ MOBİL MENÜ ==========
    const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
    const mobileNav = document.querySelector('.mobile-nav');
    const mobileNavClose = document.querySelector('.mobile-nav-close');

    if (mobileNavToggle && mobileNav) {
        mobileNavToggle.addEventListener('click', () => {
            mobileNav.classList.add('open');
        });
    }
    if (mobileNavClose && mobileNav) {
        mobileNavClose.addEventListener('click', () => {
            mobileNav.classList.remove('open');
        });
    }

    // ========== PANEL MOBİL MENÜ ==========
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const panelBody = document.querySelector('body.panel-body');

    if (mobileMenuToggle && panelBody) {
        mobileMenuToggle.addEventListener('click', () => {
            panelBody.classList.toggle('sidebar-open');
        });
    }

    // ========== GENEL OLAY DİNLEYİCİLERİ (Event Listeners) ==========
    document.body.addEventListener('click', function(event) {
        // --- SİLME ONAYI ---
        if (event.target.classList.contains('btn-sil')) {
            // confirm() fonksiyonu bu ortamda çalışmadığı için onay mekanizması kaldırıldı.
        }
    });

    // ========== SEKMELER (TABS) ==========
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            button.classList.add('active');
            const targetId = button.getAttribute('data-target');
            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
    
    // ========== ANKET FORMU - SEÇENEK EKLE/SİL ==========
    const addOptionBtn = document.getElementById('add-option-btn');
    const optionsContainer = document.getElementById('anket-secenekleri-container');

    if (addOptionBtn && optionsContainer) {
        addOptionBtn.addEventListener('click', () => {
            const optionCount = optionsContainer.querySelectorAll('.option-group').length;
            if (optionCount >= 10) return;
            
            const newOptionGroup = document.createElement('div');
            newOptionGroup.className = 'option-group';
            newOptionGroup.style.display = 'flex';
            newOptionGroup.style.gap = '10px';

            const newInput = document.createElement('input');
            newInput.type = 'text';
            newInput.name = 'secenekler[]';
            newInput.placeholder = `Seçenek ${optionCount + 1}`;
            newInput.required = true;
            newInput.className = 'form-control';
            newInput.style.flexGrow = '1';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-danger btn-sm remove-option-btn';
            removeBtn.textContent = 'Sil';
            
            newOptionGroup.appendChild(newInput);
            newOptionGroup.appendChild(removeBtn);
            optionsContainer.appendChild(newOptionGroup);
        });

        optionsContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-option-btn')) {
                if (optionsContainer.querySelectorAll('.option-group').length > 2) {
                    e.target.parentElement.remove();
                }
            }
        });
    }

    // ==================================================================
    // === YENİDEN YAZILAN, TEKİL ATAMA YÖNETİM FONKSİYONU BAŞLANGICI ===
    // ==================================================================
    /**
     * "Ekle" ve "Kaldır" butonları olan atama formlarını yönetmek için genel fonksiyon.
     * @param {string} addButtonId - "Ekle" butonunun ID'si.
     * @param {string} selectId - Seçim yapılan <select> elementinin ID'si.
     * @param {string} listId - Eklenen öğelerin listelendiği <ul> veya <div>'in ID'si.
     * @param {string} hiddenInputName - Sunucuya gönderilecek <input type="hidden"> adeti. Örn: "ogrenciler[]".
     * @param {string} noItemText - Liste boşken gösterilecek metin.
     * @param {string} noItemId - Liste boşken gösterilecek metnin ID'si.
     */
    function initializeAtamaForm(addButtonId, selectId, listId, hiddenInputName, noItemText, noItemId) {
        const addButton = document.getElementById(addButtonId);
        const selectElement = document.getElementById(selectId);
        const assignedList = document.getElementById(listId);

        if (!addButton || !selectElement || !assignedList) {
            return; // Gerekli elementler yoksa fonksiyonu çalıştırma.
        }

        // "EKLE" BUTONU İŞLEVİ
        addButton.addEventListener('click', function() {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            if (!selectedOption || !selectedOption.value) {
                return; // Geçerli bir seçim yoksa hiçbir şey yapma.
            }

            const itemId = selectedOption.value;
            const itemName = selectedOption.text;
            
            // Liste boşsa "henüz atanmamış" yazısını kaldır.
            const emptyText = document.getElementById(noItemId);
            if (emptyText) {
                emptyText.remove();
            }

            // Yeni liste öğesini oluştur.
            const listItem = document.createElement('div');
            listItem.className = 'atanan-item';
            listItem.innerHTML = `
                <span>${itemName}</span>
                <input type="hidden" name="${hiddenInputName}" value="${itemId}">
                <button type="button" class="btn-kaldir-item">Kaldır</button>
            `;
            assignedList.appendChild(listItem);

            // Eklenen öğeyi seçim listesinden kaldır.
            selectedOption.remove();
            selectElement.selectedIndex = 0;
        });

        // "KALDIR" BUTONU İŞLEVİ
        assignedList.addEventListener('click', function(event) {
            if (event.target.classList.contains('btn-kaldir-item')) {
                const listItem = event.target.parentElement;
                const itemName = listItem.querySelector('span').textContent;
                const itemId = listItem.querySelector('input').value;

                // Öğeyi listeden sil.
                listItem.remove();

                // Silinen öğeyi seçim listesine geri ekle.
                const newOption = document.createElement('option');
                newOption.value = itemId;
                newOption.textContent = itemName;
                selectElement.appendChild(newOption);

                // Eğer liste tamamen boşaldıysa "henüz atanmamış" yazısını geri ekle.
                if (assignedList.children.length === 0) {
                    const emptyText = document.createElement('p');
                    emptyText.id = noItemId;
                    emptyText.style.color = '#888';
                    emptyText.textContent = noItemText;
                    assignedList.appendChild(emptyText);
                }
            }
        });
    }

    // Fonksiyonları ilgili formlar için çağır
    initializeAtamaForm(
        'ogrenci-ekle-btn', 
        'ogrenci-sec', 
        'atanan-ogrenciler-listesi', 
        'ogrenciler[]', 
        'Bu veliye henüz öğrenci atanmamış.', 
        'veli-henuz-yok'
    );
    
    initializeAtamaForm(
        'ogrenci-sinif-ekle-btn', 
        'ogrenci-sinif-sec', 
        'atanan-siniflar-listesi', 
        'siniflar[]', 
        'Bu öğrenciye henüz sınıf atanmamış.', 
        'sinif-henuz-yok'
    );
    // ================================================================
    // === YENİDEN YAZILAN, TEKİL ATAMA YÖNETİM FONKSİYONU BİTİŞİ ===
    // ================================================================


    // ========== LIGHTBOX (RESİM GÖRÜNTÜLEYİCİ) ==========
    const lightbox = document.getElementById('lightbox');
    if (lightbox) {
        const lightboxImage = document.getElementById('lightbox-image');
        const lightboxClose = lightbox.querySelector('.lightbox-close');
        const prevBtn = lightbox.querySelector('.prev');
        const nextBtn = lightbox.querySelector('.next');
        const zoomInBtn = document.getElementById('zoom-in');
        const zoomOutBtn = document.getElementById('zoom-out');
        
        let currentImageIndex = 0;
        let images = [];
        let currentZoom = 1;

        document.body.addEventListener('click', function(event) {
            if (event.target.classList.contains('open-lightbox-btn')) {
                const button = event.target;
                const galleryContainer = button.closest('.file-gallery-container');
                const galleryLinks = galleryContainer.querySelectorAll('.d-none a');
                
                images = Array.from(galleryLinks).map(a => a.href);
                if (images.length > 0) {
                    currentImageIndex = 0;
                    updateLightboxImage();
                    lightbox.classList.add('show');
                }
            }
        });

        function updateLightboxImage() {
            if (images.length > 0) {
                lightboxImage.src = images[currentImageIndex];
                currentZoom = 1;
                lightboxImage.style.transform = `scale(${currentZoom})`;
            }
        }
        
        function closeLightbox() { lightbox.classList.remove('show'); }
        function showPrevImage() {
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
            updateLightboxImage();
        }
        function showNextImage() {
            currentImageIndex = (currentImageIndex + 1) % images.length;
            updateLightboxImage();
        }
        function zoomIn() {
            currentZoom += 0.2;
            lightboxImage.style.transform = `scale(${currentZoom})`;
        }
        function zoomOut() {
            if (currentZoom > 0.4) {
                 currentZoom -= 0.2;
                 lightboxImage.style.transform = `scale(${currentZoom})`;
            }
        }

        lightboxClose.addEventListener('click', closeLightbox);
        prevBtn.addEventListener('click', showPrevImage);
        nextBtn.addEventListener('click', showNextImage);
        zoomInBtn.addEventListener('click', zoomIn);
        zoomOutBtn.addEventListener('click', zoomOut);
        
        document.addEventListener('keydown', (e) => {
            if (lightbox.classList.contains('show')) {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') showPrevImage();
                if (e.key === 'ArrowRight') showNextImage();
            }
        });
    }

});

