const menuButton = document.querySelector('[data-menu-button]');
const sidebar = document.querySelector('[data-sidebar]');

if (menuButton && sidebar) {
    menuButton.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });
}

const analysisForm = document.querySelector('[data-analysis-form]');
const analysisResult = document.querySelector('[data-analysis-result]');
const fileInput = document.querySelector('[data-file-input]');
const fileLabel = document.querySelector('[data-file-label]');
const uploadCard = document.querySelector('[data-upload-card]');

if (fileInput && fileLabel) {
    fileInput.addEventListener('change', () => {
        const file = fileInput.files && fileInput.files[0];
        fileLabel.textContent = file ? file.name : 'Povuci ili odaberi PDF nalaz ovdje';
    });
}

if (uploadCard) {
    ['dragenter', 'dragover'].forEach((eventName) => {
        uploadCard.addEventListener(eventName, (event) => {
            event.preventDefault();
            uploadCard.classList.add('dragging');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        uploadCard.addEventListener(eventName, (event) => {
            event.preventDefault();
            uploadCard.classList.remove('dragging');
        });
    });

    uploadCard.addEventListener('drop', (event) => {
        if (!fileInput || !event.dataTransfer || event.dataTransfer.files.length === 0) {
            return;
        }

        fileInput.files = event.dataTransfer.files;
        fileInput.dispatchEvent(new Event('change'));
    });
}

if (analysisForm && analysisResult) {
    analysisForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const data = new FormData(analysisForm);
        const marker = String(data.get('marker') || '').trim();
        const value = Number(data.get('value'));
        const unit = String(data.get('unit') || '').trim();
        const min = Number(data.get('min'));
        const max = Number(data.get('max'));

        let status = 'Normalno';
        let badgeClass = 'success';
        let explanation = `${marker} je unutar očekivanih referentnih vrijednosti.`;
        let recommendation = 'Nastavite pratiti nalaze i održavati postojeće zdrave navike.';

        if (value < min) {
            status = 'Nizak';
            badgeClass = 'warning';
            explanation = `${marker} je ispod donje referentne vrijednosti. To može biti povezano s umorom, slabijom energijom ili drugim simptomima, ovisno o parametru.`;
            recommendation = marker.toLowerCase().includes('vitamin d')
                ? 'Razmotrite više boravka na suncu, prehranu bogatu vitaminom D i dodatke prehrani uz savjet liječnika.'
                : 'Razmotrite prehranu, stil života i savjetovanje s liječnikom ako odstupanje traje.';
        }

        if (value > max) {
            status = 'Visok';
            badgeClass = 'warning';
            explanation = `${marker} je iznad gornje referentne vrijednosti. Važno je pratiti trend i kontekst ostalih nalaza.`;
            recommendation = 'Provjerite nalaz s liječnikom, osobito ako postoje simptomi ili ponovljena odstupanja.';
        }

        analysisResult.innerHTML = `
            <div class="section-heading">
                <div>
                    <p class="eyebrow">AI analiza</p>
                    <h2>${escapeHtml(marker)}</h2>
                </div>
                <span class="badge ${badgeClass}">${status}</span>
            </div>
            <p><strong>Rezultat:</strong> ${value} ${escapeHtml(unit)} | Referentno: ${min} - ${max} ${escapeHtml(unit)}</p>
            <p class="plain-text">${escapeHtml(explanation)}</p>
            <p class="plain-text"><strong>Preporuka:</strong> ${escapeHtml(recommendation)}</p>
        `;
    });
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
