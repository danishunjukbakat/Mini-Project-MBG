
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('report-form');
    const schoolSelect = document.getElementById('school_id');
    const distributionSelect = document.getElementById('distribution_id');
    const status = document.getElementById('distribution-status');

    if (!form || !schoolSelect || !distributionSelect) {
        return;
    }

    const urlTemplate = form.dataset.distributionsUrl;
    const oldDistribution = form.dataset.oldDistribution;

    let activeController = null;

    async function loadDistributions(selectedId = '') {
        // Batalkan request sebelumnya jika ada.
        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();

        const schoolId = schoolSelect.value;

        distributionSelect.disabled = true;
        distributionSelect.replaceChildren(
            new Option('-- Pilih Distribusi --', '')
        );

        if (!schoolId) {
            status.textContent = 'Pilih sekolah terlebih dahulu.';
            return;
        }

        status.textContent = 'Memuat distribusi...';

        const url = urlTemplate.replace(
            'SCHOOL_ID_PLACEHOLDER',
            encodeURIComponent(schoolId)
        );

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                signal: activeController.signal
            });

            if (!response.ok) {
                throw new Error('Gagal mengambil data distribusi.');
            }

            const distributions = await response.json();

            if (distributions.length === 0) {
                status.textContent =
                    'Belum ada distribusi untuk sekolah ini.';
                return;
            }

            distributions.forEach(distribution => {
                const option = new Option(
                    `${distribution.code} - ${distribution.distribution_date}`,
                    distribution.id
                );

                if (String(distribution.id) === String(selectedId)) {
                    option.selected = true;
                }

                distributionSelect.add(option);
            });

            distributionSelect.disabled = false;

            status.textContent =
                `${distributions.length} distribusi ditemukan.`;

        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            status.textContent =
                'Gagal memuat distribusi. Silakan pilih sekolah kembali.';

            console.error(error);
        }
    }

    // Ambil distribusi setiap kali sekolah berubah.
    schoolSelect.addEventListener('change', () => {
        loadDistributions();
    });

    // Pulihkan pilihan setelah gagal validasi server.
    if (schoolSelect.value) {
        loadDistributions(oldDistribution);
    }
});
