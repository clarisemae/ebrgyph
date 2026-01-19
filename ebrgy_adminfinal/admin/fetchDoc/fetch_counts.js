async function fetchCounts() {
    try {
        const urls = [
            'fetchDoc/fetch_brgycert.php',
            'fetchDoc/fetch_indigency.php',
            'fetchDoc/registration.php',
            'fetchDoc/non_residency.php',
            'fetchDoc/national_id.php'
        ];

        for (const url of urls) {
            const response = await fetch(url);
            const data = await response.json();
            console.log("Fetched Data:", data);

            if (data.barangay_certificate !== undefined) {
                document.getElementById('barangay-certificate-count').textContent = data.barangay_certificate;
            }
            if (data.certificate_of_indigency !== undefined) {
                document.getElementById('certificate-of-indigency-count').textContent = data.certificate_of_indigency;
            }
            if (data.certificate_of_comelec_registration !== undefined) {
                document.getElementById('comelec-registration-count').textContent = data.certificate_of_comelec_registration;
            }
            if (data.certificate_of_non_residency !== undefined) {
                document.getElementById('certificate-of-non-residency').textContent = data.certificate_of_non_residency;
            }
            if (data.certificate_of_national_id !== undefined) {
                document.getElementById('certificate-of-national-id-count').textContent = data.certificate_of_national_id;
            }
        }
    } catch (error) {
        console.error('Error fetching counts:', error);
    }
}

document.addEventListener('DOMContentLoaded', fetchCounts);
